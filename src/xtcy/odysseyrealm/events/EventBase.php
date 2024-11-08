<?php

namespace xtcy\odysseyrealm\events;

use pocketmine\event\Listener;

class EventBase implements Listener
{
    /** @var bool */
    public bool $enabled = false;

    /**
     * @param EventManager $eventManager
     * @param $eventName
     */
    public function __construct(
        private EventManager $eventManager,
        private $eventName,
    )
    {
        $this->getEventManager()->getLoader()->getServer()->getPluginManager()->registerEvents($this, $this->eventManager->getLoader());
    }

    /**
     * @return string
     */
    public function getName(): string{
        return $this->eventName;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool{
        return $this->enabled;
    }

    /**
     * @return EventManager
     */
    public function getEventManager(): EventManager{
        return  $this->eventManager;
    }

    public function announce(): void
    {
        //TODO
    }

    public function disable()
    {
    }
}