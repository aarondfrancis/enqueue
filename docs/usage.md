# Usage

## Implementing Enqueueable

To make a job enqueueable, implement the `Enqueueable` interface:

```php
<?php

namespace App\Jobs;

use AaronFrancis\Enqueue\Contracts\Enqueueable;
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

## Controlling When Jobs Enqueue

By default, `enqueue()` is called every time `jobs:enqueue` runs. To control when jobs are enqueued, add a `shouldEnqueue()` method.

### Schedule-based Enqueueing

Use Laravel's fluent scheduling API:

```php
use Illuminate\Console\Scheduling\CallbackEvent;

public static function shouldEnqueue(CallbackEvent $event): CallbackEvent
{
    return $event->hourly();
}
```

Available scheduling methods include:

- `->everyMinute()`
- `->everyFiveMinutes()`
- `->everyTenMinutes()`
- `->everyFifteenMinutes()`
- `->everyThirtyMinutes()`
- `->hourly()`
- `->daily()`
- `->weekly()`
- `->monthly()`
- `->weekdays()`
- `->weekends()`
- `->between('8:00', '17:00')`
- And [many more](https://laravel.com/docs/scheduling#schedule-frequency-options)

### Boolean-based Enqueueing

For custom logic, return a boolean:

```php
public static function shouldEnqueue(CallbackEvent $event): bool
{
    return Cache::get('inventory_sync_enabled', true);
}
```

### Combining Approaches

You can combine schedule and custom logic:

```php
public static function shouldEnqueue(CallbackEvent $event): CallbackEvent|bool
{
    if (!config('services.inventory.enabled')) {
        return false;
    }

    return $event->hourly()->weekdays();
}
```

## Discovery

The `jobs:enqueue` command automatically discovers all classes in your `app/` directory that implement the `Enqueueable` interface. No manual registration is required.
