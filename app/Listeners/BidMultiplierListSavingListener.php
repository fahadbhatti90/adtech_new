<?php

namespace App\Listeners;

use App\Events\BidMultiplierListSaving;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BidMultiplierListSavingListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BidMultiplierListSaving  $event
     * @return void
     */
    public function handle(BidMultiplierListSaving $event)
    {
        \Log::info("creating");
        // \Log::info(json_encode($event));
    }
}
