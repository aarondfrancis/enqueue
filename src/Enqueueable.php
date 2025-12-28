<?php

namespace AaronFrancis\Enqueue;

interface Enqueueable
{
    /**
     * Enqueue the job(s).
     *
     * This method is called when the schedule is due (or immediately
     * if no enqueueableAt() method is defined on the class).
     */
    public static function enqueue(): void;
}
