<?php

namespace App\Services;

use App\Models\User;
use App\Models\Weapon;
use Illuminate\Database\Eloquent\Builder;

class RevistaArmasScopeService
{
    public function weaponsQueryForStaff(User $user, ?array $with = null): Builder
    {
        $with ??= [
            'activeClientAssignment.client',
            'activeClientAssignment.responsible',
            'activePendingTransfer.fromClient',
            'activePendingTransfer.fromUser',
            'activePostAssignment.post',
            'activeWorkerAssignment.worker',
        ];

        $query = Weapon::query()
            ->with($with)
            ->orderBy('serial_number');

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isResponsibleLevelOne()) {
            return $query->whereHas('clientAssignments', function (Builder $assignmentQuery) use ($user) {
                $assignmentQuery
                    ->where('is_active', true)
                    ->where('responsible_user_id', $user->id);
            });
        }

        abort(403);
    }

    public function canStaffManageWeapon(User $user, Weapon $weapon): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isResponsibleLevelOne()) {
            return false;
        }

        return $weapon->activeClientAssignment?->responsible_user_id === $user->id;
    }

    public function fieldUsersQueryForStaff(User $user): Builder
    {
        $query = User::query()
            ->where('account_kind', User::KIND_FIELD)
            ->where('is_active', true)
            ->with('fieldOwner:id,name')
            ->orderBy('name');

        if ($user->isAdmin()) {
            return $query;
        }

        if (! $user->isResponsibleLevelOne()) {
            abort(403);
        }

        return $query->where(function (Builder $outer) use ($user) {
            $outer
                ->where('field_owner_id', $user->id)
                ->orWhere(function (Builder $inner) use ($user) {
                    $inner
                        ->where('field_shared', true)
                        ->whereHas('fieldSharedResponsibles', function (Builder $responsibleQuery) use ($user) {
                            $responsibleQuery->where('users.id', $user->id);
                        });
                });
        });
    }
}
