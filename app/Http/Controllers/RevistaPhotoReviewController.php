<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Weapon;
use App\Services\RevistaArmasScopeService;
use App\Services\WeaponPhotoStagingService;
use Illuminate\Http\Request;

class RevistaPhotoReviewController extends Controller
{
    public function __construct(
        private readonly WeaponPhotoStagingService $stagingService,
        private readonly RevistaArmasScopeService $scopeService,
    ) {
        $this->middleware(['auth', 'revista.staff']);
    }

    public function approve(Request $request, Weapon $weapon, User $fieldUser)
    {
        $this->assertReview($request->user(), $weapon, $fieldUser);
        $this->stagingService->approve($request->user(), $fieldUser, $weapon);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('Imágenes oficiales actualizadas.')]);
        }

        return back()->with('status', __('Imágenes oficiales actualizadas.'));
    }

    public function reject(Request $request, Weapon $weapon, User $fieldUser)
    {
        $this->assertReview($request->user(), $weapon, $fieldUser);
        $this->stagingService->reject($request->user(), $fieldUser, $weapon);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('Fotos en revisión eliminadas.')]);
        }

        return back()->with('status', __('Fotos en revisión eliminadas.'));
    }

    private function assertReview(User $actor, Weapon $weapon, User $fieldUser): void
    {
        abort_unless($fieldUser->isField(), 404);

        if (! $this->scopeService->canStaffManageWeapon($actor, $weapon)) {
            abort(403);
        }

        abort_unless($fieldUser->fieldWeapons()->where('weapons.id', $weapon->id)->exists(), 404);
    }
}
