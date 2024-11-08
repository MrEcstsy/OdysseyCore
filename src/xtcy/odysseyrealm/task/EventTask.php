<?php

namespace xtcy\odysseyrealm\task;

use xtcy\odysseyrealm\events\JackPotEvent;
use pocketmine\scheduler\Task;

class EventTask extends Task
{

    public function onRun(): void
    {
        $e = JackPotEvent::getInstance();
        $e->tick();
    }
}