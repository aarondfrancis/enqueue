# Enqueue

Declarative job enqueueing with schedule-aware dispatch for Laravel.

## Installation

```bash
composer require aaronfrancis/enqueue
```

## Usage

Implement the `Enqueueable` interface on any job:

```php
<?php

namespace App\Jobs;

use AaronFrancis\Enqueue\Enqueueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncInventory implements ShouldQueue, Enqueueable
{
    use Queueable;

    public function __construct(public int $warehouseId) {}

    public function handle(): void
    {
        // Sync inventory for this warehouse...
    }

    public static function enqueue(): void
    {
        Warehouse::all()->each(function ($warehouse) {
            dispatch(new static($warehouse->id));
        });
    }
}
```

Schedule the command to run every minute:

```php
// routes/console.php
Schedule::command('jobs:enqueue')->everyMinute();
```

### Controlling When Jobs Enqueue

Add a `shouldEnqueue()` method to control timing:

```php
use Illuminate\Console\Scheduling\CallbackEvent;

public static function shouldEnqueue(CallbackEvent $event): CallbackEvent
{
    return $event->hourly()->weekdays();
}
```

Or return a boolean for custom logic:

```php
public static function shouldEnqueue(CallbackEvent $event): bool
{
    return Cache::get('sync_enabled', true);
}
```

### Preview Mode

See what would be enqueued without dispatching:

```bash
php artisan jobs:enqueue --pretend
```

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12

## License

MIT
