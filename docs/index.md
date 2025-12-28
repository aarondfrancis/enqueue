# Introduction

Enqueue is a Laravel package that provides declarative job enqueueing with schedule-aware dispatch.

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
