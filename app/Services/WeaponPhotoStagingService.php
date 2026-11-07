<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponPhoto;
use App\Models\WeaponPhotoStaging;
use App\Support\RevistaWeaponPhotoSlots;
use App\Services\WeaponDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class WeaponPhotoStagingService
{
    public function __construct(
        private readonly WeaponDocumentService $documentService,
        private readonly WeaponHistoryService $weaponHistory,
    ) {}

    public function stagingCount(User $fieldUser, Weapon $weapon): int
    {
        return WeaponPhotoStaging::query()
            ->where('user_id', $fieldUser->id)
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->count();
    }

    /**
     * Weapon IDs with all required staging slots for the temporary user (single query).
     *
     * @param  list<int>  $weaponIds
     * @return list<int>
     */
    public function completedWeaponIdsForTemporaryUser(int $temporaryPhotoUserId, array $weaponIds): array
    {
        if ($weaponIds === []) {
            return [];
        }

        $required = RevistaWeaponPhotoSlots::requiredCount();
        $keys = RevistaWeaponPhotoSlots::keys();

        return WeaponPhotoStaging::query()
            ->select('weapon_id')
            ->where('user_id', $temporaryPhotoUserId)
            ->whereIn('weapon_id', $weaponIds)
            ->whereIn('description', $keys)
            ->groupBy('weapon_id')
            ->havingRaw('COUNT(DISTINCT description) >= ?', [$required])
            ->pluck('weapon_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Weapon IDs that already have at least one staging photo for the temporary user.
     *
     * @return list<int>
     */
    public function weaponIdsWithAnyStagingForTemporaryUser(int $temporaryPhotoUserId): array
    {
        return WeaponPhotoStaging::query()
            ->where('user_id', $temporaryPhotoUserId)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->distinct()
            ->pluck('weapon_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Staging held by other temporary users on the given weapons.
     *
     * @param  list<int>  $weaponIds
     * @return list<array{weapon_id: int, serial_number: string|null, user_id: int, temporary_user_name: string}>
     */
    public function foreignStagingConflicts(array $weaponIds, int $excludingTemporaryUserId): array
    {
        if ($weaponIds === []) {
            return [];
        }

        return WeaponPhotoStaging::query()
            ->with([
                'fieldUser:id,name',
                'weapon:id,serial_number',
            ])
            ->whereIn('weapon_id', $weaponIds)
            ->where('user_id', '!=', $excludingTemporaryUserId)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->get()
            ->unique(fn (WeaponPhotoStaging $row) => $row->weapon_id.'-'.$row->user_id)
            ->map(fn (WeaponPhotoStaging $row) => [
                'weapon_id' => (int) $row->weapon_id,
                'serial_number' => $row->weapon?->serial_number,
                'user_id' => (int) $row->user_id,
                'temporary_user_name' => $row->fieldUser?->name ?? '—',
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $weaponIds
     * @return array<int, int> weapon_id => photo count
     */
    public function stagingCountsByWeaponForTemporaryUser(int $temporaryPhotoUserId, array $weaponIds = []): array
    {
        $query = WeaponPhotoStaging::query()
            ->selectRaw('weapon_id, COUNT(*) as staging_count')
            ->where('user_id', $temporaryPhotoUserId)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->groupBy('weapon_id');

        if ($weaponIds !== []) {
            $query->whereIn('weapon_id', $weaponIds);
        }

        return $query
            ->pluck('staging_count', 'weapon_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    public function isStagingComplete(User $fieldUser, Weapon $weapon): bool
    {
        return $this->stagingCount($fieldUser, $weapon) >= RevistaWeaponPhotoSlots::requiredCount();
    }

    /**
     * @return array<string, WeaponPhotoStaging>
     */
    public function stagingByDescription(User $fieldUser, Weapon $weapon): array
    {
        return WeaponPhotoStaging::query()
            ->with('file')
            ->where('user_id', $fieldUser->id)
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->get()
            ->keyBy('description')
            ->all();
    }

    public function storeForGuest(
        User $fieldUser,
        Weapon $weapon,
        UploadedFile $file,
        string $description,
    ): WeaponPhotoStaging {
        if (! $fieldUser->isField() || ! $fieldUser->is_active || ! $fieldUser->fieldAccessOpen()) {
            throw ValidationException::withMessages([
                'photo' => __('El acceso de campo no está vigente.'),
            ]);
        }

        if (! in_array($description, RevistaWeaponPhotoSlots::keys(), true)) {
            throw ValidationException::withMessages([
                'description' => __('Descripción de foto no válida.'),
            ]);
        }

        if (! $fieldUser->fieldWeapons()->where('weapons.id', $weapon->id)->exists()) {
            abort(403);
        }

        $path = $file->store(
            'weapons/'.$weapon->id.'/staging/'.$fieldUser->id,
            'public'
        );

        try {
            return DB::transaction(function () use ($fieldUser, $weapon, $file, $path, $description) {
                $existing = WeaponPhotoStaging::query()
                    ->where('user_id', $fieldUser->id)
                    ->where('weapon_id', $weapon->id)
                    ->where('description', $description)
                    ->first();

                if ($existing?->file) {
                    Storage::disk($existing->file->disk)->delete($existing->file->path);
                    $existing->file->delete();
                    $existing->delete();
                }

                $storedFile = File::create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => null,
                ]);

                return WeaponPhotoStaging::create([
                    'user_id' => $fieldUser->id,
                    'weapon_id' => $weapon->id,
                    'description' => $description,
                    'file_id' => $storedFile->id,
                ]);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    public function approve(User $reviewer, User $fieldUser, Weapon $weapon): void
    {
        $stagingRows = WeaponPhotoStaging::query()
            ->with('file')
            ->where('user_id', $fieldUser->id)
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->get();

        if ($stagingRows->count() < RevistaWeaponPhotoSlots::requiredCount()) {
            throw ValidationException::withMessages([
                'photos' => __('Faltan fotos en revisión para actualizar las imágenes oficiales.'),
            ]);
        }

        DB::transaction(function () use ($reviewer, $weapon, $stagingRows, $fieldUser) {
            foreach ($stagingRows as $staging) {
                $this->promoteStagingRowToOfficial($reviewer, $weapon, $staging);
            }

            WeaponPhotoStaging::query()
                ->where('user_id', $fieldUser->id)
                ->where('weapon_id', $weapon->id)
                ->whereIn('description', RevistaWeaponPhotoSlots::keys())
                ->delete();

            AuditLog::create([
                'user_id' => $reviewer->id,
                'action' => 'approve_revista_staging_photos',
                'auditable_type' => Weapon::class,
                'auditable_id' => $weapon->id,
                'before' => null,
                'after' => [
                    'user_id' => $fieldUser->id,
                    'count' => $stagingRows->count(),
                ],
            ]);

            $this->weaponHistory->recordRevistaPhotosApproved(
                $weapon,
                $reviewer,
                $fieldUser,
                $stagingRows->count(),
            );
        });

        $this->documentService->syncRenewalDocument($weapon);
    }

    public function reject(User $reviewer, User $fieldUser, Weapon $weapon): void
    {
        $stagingRows = WeaponPhotoStaging::query()
            ->with('file')
            ->where('user_id', $fieldUser->id)
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->get();

        DB::transaction(function () use ($reviewer, $weapon, $stagingRows, $fieldUser) {
            foreach ($stagingRows as $staging) {
                if ($staging->file) {
                    Storage::disk($staging->file->disk)->delete($staging->file->path);
                    $staging->file->delete();
                }
                $staging->delete();
            }

            AuditLog::create([
                'user_id' => $reviewer->id,
                'action' => 'reject_revista_staging_photos',
                'auditable_type' => Weapon::class,
                'auditable_id' => $weapon->id,
                'before' => null,
                'after' => [
                    'user_id' => $fieldUser->id,
                ],
            ]);
        });
    }

    private function promoteStagingRowToOfficial(User $reviewer, Weapon $weapon, WeaponPhotoStaging $staging): void
    {
        $stagingFile = $staging->file;
        if (! $stagingFile) {
            return;
        }

        $existingPhoto = $weapon->photos()
            ->with('file')
            ->where('description', $staging->description)
            ->first();

        if ($existingPhoto) {
            if ($existingPhoto->file) {
                Storage::disk($existingPhoto->file->disk)->delete($existingPhoto->file->path);
                $existingPhoto->file->delete();
            }
            $existingPhoto->delete();
        }

        $extension = pathinfo($stagingFile->path, PATHINFO_EXTENSION) ?: 'jpg';
        $officialPath = 'weapons/'.$weapon->id.'/photos/revista_'.uniqid('', true).'.'.$extension;

        Storage::disk('public')->copy($stagingFile->path, $officialPath);

        $officialFile = File::create([
            'disk' => 'public',
            'path' => $officialPath,
            'original_name' => $stagingFile->original_name,
            'mime_type' => $stagingFile->mime_type,
            'size' => $stagingFile->size,
            'checksum' => $stagingFile->checksum,
            'uploaded_by' => $reviewer->id,
        ]);

        $photo = $weapon->photos()->create([
            'file_id' => $officialFile->id,
            'description' => $staging->description,
        ]);

        Storage::disk($stagingFile->disk)->delete($stagingFile->path);
        $stagingFile->delete();

        AuditLog::create([
            'user_id' => $reviewer->id,
            'action' => 'upload_photo',
            'auditable_type' => Weapon::class,
            'auditable_id' => $weapon->id,
            'before' => null,
            'after' => [
                'photo_id' => $photo->id,
                'file_id' => $officialFile->id,
                'description' => $photo->description,
                'source' => 'revista_staging',
            ],
        ]);
    }
}
