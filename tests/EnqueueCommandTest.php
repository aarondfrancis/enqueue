<?php

use AaronFrancis\Enqueue\EnqueueCommand;
use AaronFrancis\Enqueue\Tests\Fixtures\JobWithBooleanShouldEnqueue;
use AaronFrancis\Enqueue\Tests\Fixtures\JobWithHourlySchedule;
use AaronFrancis\Enqueue\Tests\Fixtures\JobWithoutSchedule;
use AaronFrancis\Enqueue\Tests\Fixtures\NonEnqueueableJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

beforeEach(function () {
    JobWithoutSchedule::reset();
    JobWithHourlySchedule::reset();
    JobWithBooleanShouldEnqueue::reset();
    NonEnqueueableJob::reset();
});

afterEach(function () {
    Carbon::setTestNow();
});

it('enqueues jobs without schedule', function () {
    registerTestCommand([JobWithoutSchedule::class]);

    $this->artisan('jobs:enqueue:test')
        ->assertSuccessful();

    expect(JobWithoutSchedule::$enqueued)->toBeTrue();
});

it('enqueues jobs when schedule is due', function () {
    Carbon::setTestNow('2024-01-15 14:00:00');

    registerTestCommand([JobWithHourlySchedule::class]);

    $this->artisan('jobs:enqueue:test')
        ->assertSuccessful();

    expect(JobWithHourlySchedule::$enqueued)->toBeTrue();
});

it('skips jobs when schedule is not due', function () {
    Carbon::setTestNow('2024-01-15 14:30:00');

    registerTestCommand([JobWithHourlySchedule::class]);

    $this->artisan('jobs:enqueue:test')
        ->assertSuccessful();

    expect(JobWithHourlySchedule::$enqueued)->toBeFalse();
});

it('does not enqueue in pretend mode', function () {
    registerTestCommand([JobWithoutSchedule::class]);

    $this->artisan('jobs:enqueue:test', ['--pretend' => true])
        ->assertSuccessful();

    expect(JobWithoutSchedule::$enqueued)->toBeFalse();
});

it('handles no enqueueable jobs gracefully', function () {
    registerTestCommand([]);

    $this->artisan('jobs:enqueue:test')
        ->assertSuccessful();
});

it('ignores classes that do not implement Enqueueable', function () {
    registerTestCommand([JobWithoutSchedule::class, NonEnqueueableJob::class]);

    $this->artisan('jobs:enqueue:test')
        ->assertSuccessful();

    expect(JobWithoutSchedule::$enqueued)->toBeTrue();
    expect(NonEnqueueableJob::$enqueued)->toBeFalse();
});

it('enqueues multiple jobs when all are due', function () {
    Carbon::setTestNow('2024-01-15 14:00:00');

    registerTestCommand([
        JobWithoutSchedule::class,
        JobWithHourlySchedule::class,
    ]);

    $this->artisan('jobs:enqueue:test')
        ->assertSuccessful();

    expect(JobWithoutSchedule::$enqueued)->toBeTrue();
    expect(JobWithHourlySchedule::$enqueued)->toBeTrue();
});

it('enqueues jobs when shouldEnqueue returns true', function () {
    JobWithBooleanShouldEnqueue::$shouldReturn = true;

    registerTestCommand([JobWithBooleanShouldEnqueue::class]);

    $this->artisan('jobs:enqueue:test')
        ->assertSuccessful();

    expect(JobWithBooleanShouldEnqueue::$enqueued)->toBeTrue();
});

it('skips jobs when shouldEnqueue returns false', function () {
    JobWithBooleanShouldEnqueue::$shouldReturn = false;

    registerTestCommand([JobWithBooleanShouldEnqueue::class]);

    $this->artisan('jobs:enqueue:test')
        ->assertSuccessful();

    expect(JobWithBooleanShouldEnqueue::$enqueued)->toBeFalse();
});

function registerTestCommand(array $jobClasses): void
{
    $command = new TestEnqueueCommand($jobClasses);

    app(\Illuminate\Contracts\Console\Kernel::class)->registerCommand($command);
}

class TestEnqueueCommand extends EnqueueCommand
{
    protected $signature = 'jobs:enqueue:test
        {--pretend : Display which jobs would be enqueued without actually enqueueing them}';

    public function __construct(protected array $testJobs = [])
    {
        parent::__construct();
    }

    protected function discoverEnqueueableJobs(): Collection
    {
        return (new Collection($this->testJobs))
            ->filter(fn ($class) => $this->isEnqueueable($class))
            ->values();
    }
}
