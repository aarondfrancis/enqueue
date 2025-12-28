<?php

namespace AaronFrancis\Enqueue\Contracts;

interface Enqueueable
{
    /**
     * Enqueue the job(s).
     *
     * This method is called when the schedule is due (or immediately
     * if no shouldEnqueue() method is defined on the class).
     */
    public static function enqueue(): void;
}
