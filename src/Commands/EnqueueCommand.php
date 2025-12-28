<?php

namespace AaronFrancis\Enqueue\Commands;

use AaronFrancis\Enqueue\Contracts\Enqueueable;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'jobs:enqueue')]
class EnqueueCommand extends Command
{
    protected $signature = 'jobs:enqueue
        {--list : List all discovered enqueueable jobs}
        {--pretend : Display which jobs would be enqueued without actually enqueueing them}';

    protected $description = 'Enqueue jobs that implement the Enqueueable interface.';

    public function handle(): int
    {
        $jobs = $this->discoverEnqueueableJobs();

        if ($jobs->isEmpty()) {
            $this->components->info('No enqueueable jobs found.');

            return self::SUCCESS;
        }

        if ($this->option('list')) {
            $this->components->info('Discovered enqueueable jobs:');
            $this->newLine();

            foreach ($jobs as $jobClass) {
                $hasSchedule = method_exists($jobClass, 'shouldEnqueue');
                $this->components->twoColumnDetail(
                    $jobClass,
                    $hasSchedule ? '<fg=cyan>Scheduled</>' : '<fg=gray>Always</>'
                );
            }

            $this->newLine();
            $this->components->info("Found {$jobs->count()} enqueueable job(s).");

            return self::SUCCESS;
        }

        $enqueued = 0;
        $skipped = 0;

        foreach ($jobs as $jobClass) {
            if ($this->isDue($jobClass)) {
                if ($this->option('pretend')) {
                    $this->components->info("Would enqueue: {$jobClass}");
                } else {
                    $jobClass::enqueue();
                    $this->components->info("Enqueued: {$jobClass}");
                }
                $enqueued++;
            } else {
                $this->components->twoColumnDetail($jobClass, '<fg=yellow>Not due</>');
                $skipped++;
            }
        }

        $this->newLine();
        $this->components->info("Enqueued {$enqueued} job(s), skipped {$skipped} job(s).");

        return self::SUCCESS;
    }

    /**
     * Discover all classes implementing Enqueueable in the app directory.
     */
    protected function discoverEnqueueableJobs(): Collection
    {
        $appPath = app_path();

        if (! is_dir($appPath)) {
            return new Collection;
        }

        return (new Collection(
            Finder::create()->in($appPath)->files()->name('*.php')
        ))
            ->map(fn ($file) => $this->classFromFile($file))
            ->filter(fn ($class) => $this->isEnqueueable($class))
            ->values();
    }

    /**
     * Extract the fully qualified class name from a file.
     */
    protected function classFromFile(\SplFileInfo $file): ?string
    {
        $namespace = $this->laravel->getNamespace();
        $relativePath = Str::after(
            $file->getRealPath(),
            realpath(app_path()).DIRECTORY_SEPARATOR
        );

        return $namespace.str_replace(
            ['/', '.php'],
            ['\\', ''],
            $relativePath
        );
    }

    /**
     * Determine if a class implements Enqueueable.
     */
    protected function isEnqueueable(?string $class): bool
    {
        if (! $class || ! class_exists($class)) {
            return false;
        }

        $reflection = new ReflectionClass($class);

        return ! $reflection->isAbstract()
            && $reflection->implementsInterface(Enqueueable::class);
    }

    /**
     * Determine if the job should be enqueued based on its schedule.
     */
    protected function isDue(string $jobClass): bool
    {
        if (! method_exists($jobClass, 'shouldEnqueue')) {
            return true;
        }

        $schedule = $this->laravel->make(Schedule::class);
        $event = $schedule->call(fn () => null);

        $result = $jobClass::shouldEnqueue($event);

        if (is_bool($result)) {
            return $result;
        }

        return $result->isDue($this->laravel);
    }
}
