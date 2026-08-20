# Production Deployment Checklist

Written during Module 19 (Hardening) — the audit for that module found these
requirements existed only implicitly (config expecting infrastructure that
wasn't documented anywhere), so this file makes them explicit.

## Queue worker

`QUEUE_CONNECTION=database` (see `.env`) means queued jobs sit in the `jobs`
table until a worker processes them — nothing runs them automatically. Run:

```bash
php artisan queue:work --tries=3
```

under a process supervisor (systemd, Supervisor, etc.) so it restarts if it
dies or the app is redeployed. Failed jobs land in `failed_jobs` (bundled into
the default `create_jobs_table` migration) and can be inspected with
`php artisan queue:failed` / retried with `php artisan queue:retry`.

Currently only one queued job exists (`App\Jobs\RecalculateAchievementsJob`),
and it's deliberately invoked with `::dispatchSync()` from the admin Target
screen (see the doc-comment on `TargetService::recalculate()`) so an admin
sees the recalculated achievement immediately without needing a worker
running — `::dispatchSync()` runs inline regardless of `QUEUE_CONNECTION`.
The same job class is genuinely queueable (`::dispatch()`) for a future
batch/scheduled recalculation of many targets at once, which *would* need a
worker running.

## Scheduler

Laravel's scheduler (`routes/console.php` — the 5 notification-check
commands) needs the single cron entry Laravel always requires, running every
minute:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Without this cron entry, none of the 5 scheduled notification checks (late
attendance, no checkout, low performance, target reminders, birthdays) ever
run, silently.

## `.env` values that must differ from local dev

| Key | Local (current `.env`) | Production |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` — leaving this `true` in production leaks stack traces/config to any error page (the JSON error envelope in `bootstrap/app.php` already guards against this for API responses via `config('app.debug')`, but web error pages are not similarly guarded by Laravel itself) |
| `SESSION_SECURE_COOKIE` | unset (defaults to `false`) | `true` — otherwise the session cookie is sent over plain HTTP; only safe once the production domain is served over HTTPS |
| `SESSION_ENCRYPT` | `false` | `true` recommended — encrypts the session payload at rest in the `sessions` table |
