# Installation

## Requirements

- PHP 8.2 or higher
- Laravel 10, 11, or 12

## Install via Composer

```bash
composer require aaronfrancis/enqueue
```

The package will automatically register its service provider via Laravel's package discovery.

## Schedule the Command

Add the `jobs:enqueue` command to your scheduler. In `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('jobs:enqueue')->everyMinute();
```

The command runs every minute and checks each enqueueable job's `shouldEnqueue()` method to determine if it should actually dispatch.
