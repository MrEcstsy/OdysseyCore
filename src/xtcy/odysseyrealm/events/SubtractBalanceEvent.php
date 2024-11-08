<?php

namespace xtcy\odysseyrealm\events;

use xtcy\odysseyrealm\Loader;

class SubtractBalanceEvent extends EconomyEvent
{

    public static $handlerList;

    public function __construct(Loader $plugin, private $username, private $amount, $issuer)
    {
        parent::__construct($plugin, $issuer);
        $this->username = $username;
        $this->amount = $amount;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}