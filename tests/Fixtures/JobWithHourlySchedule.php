<?php

namespace AaronFrancis\Enqueue\Tests\Fixtures;

use AaronFrancis\Enqueue\Contracts\Enqueueable;
use Illuminate\Console\Scheduling\CallbackEvent;

class JobWithHourlySchedule implements Enqueueable
{
    public static bool $enqueued = false;

    public static function enqueue(): void
    {
        static::$enqueued = true;
    }

    public static function shouldEnqueue(CallbackEvent $event): CallbackEvent
    {
        return $event->hourly();
    }

    public static function reset(): void
    {
        static::$enqueued = false;
    }
}
