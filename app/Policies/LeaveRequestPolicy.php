<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leave-requests.view');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave-requests.view') && $this->isVisible($user, $leaveRequest);
    }

    public function create(User $user): bool
    {
        return $user->can('leave-requests.add');
    }

    /**
     * Editing is limited to a request nobody has decided yet. Once a
     * manager has approved or rejected it, changing the dates or the type
     * underneath their decision would make the record misrepresent what
     * was actually agreed - the request is cancelled and resubmitted
     * instead.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave-requests.edit')
            && $leaveRequest->isPending()
            && $this->isVisible($user, $leaveRequest);
    }

    /**
     * Deciding someone's leave is a manager's action, and deliberately not
     * one they can perform on their own request: `leave-requests.approve`
     * is never granted to Sales Executive, and self-approval is refused
     * here as well so a manager who does hold the permission still cannot
     * sign off their own time off.
     */
    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave-requests.approve')
            && $leaveRequest->user_id !== $user->id
            && $this->isVisible($user, $leaveRequest);
    }

    /**
     * Withdrawing a request belongs to whoever it is for - an executive
     * cancels their own without needing the approve permission. A manager
     * with approve rights can also withdraw one on someone's behalf.
     */
    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->user_id === $user->id) {
            return $user->can('leave-requests.view');
        }

        return $user->can('leave-requests.approve') && $this->isVisible($user, $leaveRequest);
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave-requests.delete') && $this->isVisible($user, $leaveRequest);
    }

    public function restore(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave-requests.restore') && $this->isVisible($user, $leaveRequest);
    }

    public function forceDelete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('leave-requests.export');
    }

    public function import(User $user): bool
    {
        return $user->can('leave-requests.import');
    }

    public function print(User $user): bool
    {
        return $user->can('leave-requests.print');
    }

    /**
     * Whether this request falls within the viewer's own visibility scope
     * - reuses LeaveRequest::scopeVisibleTo() so list and single-record
     * checks cannot drift apart. withTrashed() so a soft-deleted request
     * can still be authorized for restore.
     */
    private function isVisible(User $user, LeaveRequest $leaveRequest): bool
    {
        return LeaveRequest::withTrashed()->visibleTo($user)->whereKey($leaveRequest->id)->exists();
    }
}
