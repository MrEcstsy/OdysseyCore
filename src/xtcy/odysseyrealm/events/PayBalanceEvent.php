<?php

namespace xtcy\odysseyrealm\events;

use xtcy\odysseyrealm\Loader;

class PayBalanceEvent extends EconomyEvent
{

    public static $handlerList;

    public function __construct(Loader $plugin, private $payer, private $target, private $amount)
    {
        parent::__construct($plugin, "PayCommand");
    }

    public function getPayer()
    {
        return $this->payer;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getAmount()
    {
        return $this->amount;
    }

}