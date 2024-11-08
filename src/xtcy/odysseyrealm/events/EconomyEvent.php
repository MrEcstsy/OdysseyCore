<?php

namespace xtcy\odysseyrealm\events;

use xtcy\odysseyrealm\Loader;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;

class EconomyEvent extends PluginEvent implements Cancellable
{

    public $issuer;

    public function __construct(Loader $plugin, $issuer){
        parent::__construct($plugin);
        $this->issuer = $issuer;
    }

    public function getIssuer(){
        return $this->issuer;
    }

    public function isCancelled(): bool
    {
        return true;
    }
}