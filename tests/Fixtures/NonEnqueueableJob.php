<?php

namespace AaronFrancis\Enqueue\Tests\Fixtures;

class NonEnqueueableJob
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
