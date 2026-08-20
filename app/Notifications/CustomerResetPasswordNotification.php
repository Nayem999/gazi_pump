<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The only mail-channel (not database) notification in the app — a password
 * reset link is inherently something the customer needs outside the portal
 * itself, unlike every other Notification here which feeds the in-app bell.
 * Kept separate from Laravel's default Illuminate\Auth\Notifications\ResetPassword
 * so the link points at the portal's own 'portal.password.reset' route
 * rather than the framework's default 'password.reset' name.
 *
 * Queued (Module 23 hardening) since it's also the only notification in the
 * app that makes a real outbound network call (SMTP) rather than a local DB
 * insert — unqueued, it blocked the password-reset HTTP response on that
 * round-trip. Unlike RecalculateAchievementsJob (deliberately kept
 * synchronous so an admin sees its result immediately), there's no UX reason
 * for this one to be synchronous: the visitor is shown the same generic
 * "check your email" message either way.
 */
class CustomerResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('portal.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
