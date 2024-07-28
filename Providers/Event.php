<?php

namespace Modules\Flowiseai\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Flowiseai\Listeners\RespondOnMessage;

class Event extends ServiceProvider
{
    protected $listen = [];

    protected $subscribe = [
        RespondOnMessage::class,
    ];
}