# CLI Reference

## jobs:enqueue

Discovers and enqueues jobs that implement the `Enqueueable` interface.

### Usage

```bash
php artisan jobs:enqueue [--list] [--pretend]
```

### Options

| Option | Description |
|--------|-------------|
| `--list` | List all discovered enqueueable jobs without dispatching |
| `--pretend` | Display which jobs would be enqueued without actually dispatching them |

### Examples

**Run normally:**

```bash
php artisan jobs:enqueue
```

Output:
```
Enqueued: App\Jobs\SyncInventory
Enqueued: App\Jobs\ProcessVideos
App\Jobs\SendReports .......................... Not due

Enqueued 2 job(s), skipped 1 job(s).
```

**List discovered jobs:**

```bash
php artisan jobs:enqueue --list
```

Output:
```
Discovered enqueueable jobs:

App\Jobs\SyncInventory ........................ Always
App\Jobs\ProcessVideos ..................... Scheduled
App\Jobs\SendReports ....................... Scheduled

Found 3 enqueueable job(s).
```

**Preview without dispatching:**

```bash
php artisan jobs:enqueue --pretend
```

Output:
```
Would enqueue: App\Jobs\SyncInventory
Would enqueue: App\Jobs\ProcessVideos
App\Jobs\SendReports .......................... Not due

Enqueued 2 job(s), skipped 1 job(s).
```

### Scheduling

Add to your scheduler in `routes/console.php`:

```php
Schedule::command('jobs:enqueue')->everyMinute();
```

The command should run frequently (every minute) since individual jobs control their own schedule via `shouldEnqueue()`.
