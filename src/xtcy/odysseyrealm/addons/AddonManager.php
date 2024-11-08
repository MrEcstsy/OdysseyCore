<?php

namespace xtcy\odysseyrealm\addons;

use xtcy\odysseyrealm\events\EventManager;
use xtcy\odysseyrealm\Loader;

class AddonManager{

    /**
     * @param Loader $plugin
     * @param EventManager|null $eventManager
     */
    public function __construct(
        private Loader  $plugin,
        private ?EventManager $eventManager = null,
    ){
        $this->init();
    }

    public function init(): void{
        $this->eventManager = new EventManager(Loader::getInstance());
    }

    /**
     * @return EventManager|null
     */
    public function getEventManager(): ?EventManager{
        return $this->eventManager;
    }

    /**
     * @return Loader|null
     */
    public function getLoader(): ?Loader{
        return $this->plugin;
    }

}