# Introduction

Enqueue is a Laravel package that provides declarative job enqueueing with schedule-aware dispatch.

Jobs often need to dispatch themselves on a schedule—syncing data every hour, processing uploads every few minutes, sending reports on weekdays. Normally this means scattering scheduling logic across `routes/console.php` while the job sits elsewhere, or writing awkward wrapper commands.

Enqueue lets the job own its entire lifecycle. Each job declares *how* to enqueue itself (maybe one instance per warehouse, or one per pending upload) and optionally *when* (hourly, weekdays, or custom logic). Run `jobs:enqueue` every minute and each job takes care of the rest.

## The Problem

In typical Laravel applications, you might have a scheduled command that queries for pending work and dispatches jobs:

```php
// routes/console.php
Schedule::command('videos:process')->everyTenMinutes();

// app/Console/Commands/ProcessVideos.php
class ProcessVideos extends Command
{
    public function handle()
    {
        Video::where('processed', false)->each(function ($video) {
            ProcessVideo::dispatch($video);
        });
    }
}
```

This works, but the enqueueing logic is separate from the job itself, making it harder to understand when and how jobs get dispatched.

## The Solution

With Enqueue, you define the enqueueing logic directly on the job class:

```php
use AaronFrancis\Enqueue\Enqueueable;

class ProcessVideo implements ShouldQueue, Enqueueable
{
    public static function enqueue(): void
    {
        Video::where('processed', false)->each(function ($video) {
            dispatch(new static($video));
        });
    }

    public static function shouldEnqueue(CallbackEvent $event): CallbackEvent
    {
        return $event->everyTenMinutes();
    }
}
```

Then schedule a single command to handle all enqueueable jobs:

```php
Schedule::command('jobs:enqueue')->everyMinute();
```

## Features

- **Declarative**: Define when and how jobs are enqueued directly on the job class
- **Schedule-aware**: Use Laravel's fluent scheduling API to control when jobs are enqueued
- **Automatic discovery**: Jobs implementing `Enqueueable` are automatically discovered
- **Pretend mode**: Preview what would be enqueued without actually dispatching
- **Flexible conditions**: Return a schedule or a simple boolean from `shouldEnqueue()`
