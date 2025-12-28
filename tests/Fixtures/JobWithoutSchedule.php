<?php

namespace AaronFrancis\Enqueue\Tests\Fixtures;

use AaronFrancis\Enqueue\Contracts\Enqueueable;

class JobWithoutSchedule implements Enqueueable
{
    public static bool $enqueued = false;

    public static function enqueue(): void
    {
        static::$enqueued = true;
    }

    public static function reset(): void
    {
        static::$enqueued = false;
    }
}
