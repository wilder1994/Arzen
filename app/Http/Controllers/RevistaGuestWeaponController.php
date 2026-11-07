<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Weapon;
use App\Services\WeaponPhotoStagingService;
use App\Support\RevistaWeaponPhotoSlots;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RevistaGuestWeaponController extends Controller
{
    public function __construct(
        private readonly WeaponPhotoStagingService $stagingService,
    ) {
        $this->middleware('revista.guest');
    }

    public function index(Request $request): View
    {
        /** @var User $fieldUser */
        $fieldUser = $request->attributes->get('revista_field_user');

        $weapons = $fieldUser->fieldWeapons()
            ->with([
                'activeClientAssignment.client',
                'activePostAssignment.post',
                'activeWorkerAssignment.worker',
            ])
            ->orderBy('serial_number')
            ->get();

        $rows = $weapons->map(fn (Weapon $weapon) => [
            'weapon' => $weapon,
            'is_complete' => $this->stagingService->isStagingComplete($fieldUser, $weapon),
        ]);

        return view('revista-armas.guest.weapons', [
            'rows' => $rows,
            'fieldUser' => $fieldUser,
        ]);
    }

    public function stagingState(Request $request, Weapon $weapon)
    {
        /** @var User $fieldUser */
        $fieldUser = $request->attributes->get('revista_field_user');

        if (! $fieldUser->fieldWeapons()->where('weapons.id', $weapon->id)->exists()) {
            abort(403);
        }

        $staging = $this->stagingService->stagingByDescription($fieldUser, $weapon);

        return response()->json([
            'slots' => collect(RevistaWeaponPhotoSlots::descriptions())->map(function ($label, $description) use ($staging) {
                $row = $staging[$description] ?? null;

                return [
                    'description' => $description,
                    'label' => $label,
                    'url' => $row?->file
                        ? Storage::disk($row->file->disk)->url($row->file->path)
                        : null,
                ];
            })->values(),
            'is_complete' => $this->stagingService->isStagingComplete($fieldUser, $weapon),
        ]);
    }
}
