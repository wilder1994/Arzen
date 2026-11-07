<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Weapon;
use App\Services\WeaponPhotoStagingService;
use App\Support\RevistaWeaponPhotoSlots;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RevistaPhotoStagingController extends Controller
{
    public function __construct(
        private readonly WeaponPhotoStagingService $stagingService,
    ) {
        $this->middleware('revista.guest');
    }

    public function storeGuest(Request $request, Weapon $weapon)
    {
        /** @var User $fieldUser */
        $fieldUser = $request->attributes->get('revista_field_user');

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
            'description' => ['required', Rule::in(RevistaWeaponPhotoSlots::keys())],
        ]);

        $this->stagingService->storeForGuest(
            $fieldUser,
            $weapon,
            $data['photo'],
            $data['description'],
        );

        return response()->json(['ok' => true]);
    }
}
