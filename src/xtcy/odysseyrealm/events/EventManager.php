<?php

namespace xtcy\odysseyrealm\events;

use xtcy\odysseyrealm\Loader;
use xtcy\odysseyrealm\task\EventTask;

class EventManager
{

    /** @var EventBase|null */
    public ?EventBase $eventBase = null;

    /** @var int $time */
    public int $time = 60;

    /** @var int */
    public int $wait = 90;

    /**
     * @param Loader $plugin
     * @param JackPotEvent|null $jackPotEvent
     */
    public function __construct(
        public Loader $plugin,
        private ?JackPotEvent $jackPotEvent = null,
    )
    {
        $this->plugin->getScheduler()->scheduleRepeatingTask(new EventTask(), 20);
        $this->init();
    }

    public function init(): void
    {
        $this->jackPotEvent = new JackPotEvent($this, "JACKPOT");
    }

    /**
     * @return JackPotEvent|null
     */
    public function getJackPotEvent(): ?JackPotEvent{
        return $this->jackPotEvent;
    }

    public function getLoader(): Loader {
        return $this->plugin;
    }

    /**
     * SWITCHES THRU EVENTS
     */
    public function shiftEvents(): void
    {
        --$this->time;
        --$this->wait;
        if($this->time <= 0 && $this->wait <= 0){
            $this->time = mt_rand(500,1000);
            $this->wait = mt_rand(600,1700);
        }
    }
}