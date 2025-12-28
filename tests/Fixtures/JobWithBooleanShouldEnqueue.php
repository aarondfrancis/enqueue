<?php

namespace AaronFrancis\Enqueue\Tests\Fixtures;

use AaronFrancis\Enqueue\Contracts\Enqueueable;
use Illuminate\Console\Scheduling\CallbackEvent;

class JobWithBooleanShouldEnqueue implements Enqueueable
{
    public static bool $enqueued = false;

    public static bool $shouldReturn = true;

    public static function enqueue(): void
    {
        static::$enqueued = true;
    }

    public static function shouldEnqueue(CallbackEvent $event): bool
    {
        return static::$shouldReturn;
    }

    public static function reset(): void
    {
        static::$enqueued = false;
        static::$shouldReturn = true;
    }
}
