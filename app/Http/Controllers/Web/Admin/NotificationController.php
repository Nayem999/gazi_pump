<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $this->notifications->paginate($request->user(), $request->only(['status']), 15),
            'filters' => $request->only(['status']),
        ]);
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $this->notifications->markAsRead($request->user(), $id);

        return redirect()->back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $count = $this->notifications->markAllAsRead($request->user());

        return redirect()->back()->with('success', "{$count} notification(s) marked as read.");
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $this->notifications->delete($request->user(), $id);

        return redirect()->back()->with('success', 'Notification deleted.');
    }
}
