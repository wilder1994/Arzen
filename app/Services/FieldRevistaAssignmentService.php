<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FieldRevistaAssignmentService
{
    public function __construct(
        private readonly RevistaArmasScopeService $scopeService,
    ) {
    }

    /**
     * @param  list<int>  $weaponIds
     * @return array{notice: bool, field_name: string, owner_name: string}
     */
    public function assign(User $actor, User $fieldUser, array $weaponIds): array
    {
        if (! $fieldUser->canAssignFieldUser($actor)) {
            abort(403);
        }

        $weaponIds = collect($weaponIds)->map(fn ($id) => (int) $id)->unique()->values();
        if ($weaponIds->isEmpty()) {
            throw ValidationException::withMessages([
                'weapon_ids' => __('Seleccione al menos un arma.'),
            ]);
        }

        $allowed = $this->scopeService
            ->weaponsQueryForStaff($actor, [])
            ->whereIn('weapons.id', $weaponIds)
            ->pluck('weapons.id');

        if ($allowed->count() !== $weaponIds->count()) {
            abort(403);
        }

        $alreadyAssigned = $fieldUser->fieldWeapons()->exists();

        DB::transaction(function () use ($fieldUser, $allowed, $actor) {
            $fieldUser->fieldWeapons()->syncWithoutDetaching(
                $allowed->mapWithKeys(fn ($id) => [(int) $id => ['assigned_by_user_id' => $actor->id]])->all()
            );
        });

        $fieldUser->loadMissing('fieldOwner:id,name');

        return [
            'notice' => $fieldUser->field_shared
                && $alreadyAssigned
                && (int) $fieldUser->field_owner_id !== (int) $actor->id,
            'field_name' => $fieldUser->name,
            'owner_name' => $fieldUser->fieldOwner?->name ?? $actor->name,
        ];
    }
}
