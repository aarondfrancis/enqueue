# Enqueue

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aaronfrancis/enqueue.svg?style=flat-square)](https://packagist.org/packages/aaronfrancis/enqueue)
[![Tests](https://github.com/aarondfrancis/enqueue/actions/workflows/tests.yml/badge.svg)](https://github.com/aarondfrancis/enqueue/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/aaronfrancis/enqueue.svg?style=flat-square)](https://packagist.org/packages/aaronfrancis/enqueue)
[![PHP Version](https://img.shields.io/packagist/php-v/aaronfrancis/enqueue.svg?style=flat-square)](https://packagist.org/packages/aaronfrancis/enqueue)
[![License](https://img.shields.io/packagist/l/aaronfrancis/enqueue.svg?style=flat-square)](https://packagist.org/packages/aaronfrancis/enqueue)

Declarative job enqueueing with schedule-aware dispatch for Laravel.

Jobs often need to dispatch themselves on a schedule—syncing data every hour, processing uploads every few minutes, sending reports on weekdays. Normally this means scattering scheduling logic across `routes/console.php` while the job sits elsewhere, or writing awkward wrapper commands.

Enqueue lets the job own its entire lifecycle. Each job declares *how* to enqueue itself (maybe one instance per warehouse, or one per pending upload) and optionally *when* (hourly, weekdays, or custom logic). Run `jobs:enqueue` every minute and each job takes care of the rest.

## Installation

```bash
composer require aaronfrancis/enqueue
```

## Usage

Implement the `Enqueueable` interface on any job:

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
- Laravel 10, 11, or 12.24+

## License

MIT
