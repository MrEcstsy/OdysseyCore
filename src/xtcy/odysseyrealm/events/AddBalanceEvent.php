<?php

namespace xtcy\odysseyrealm\events;

use xtcy\odysseyrealm\Loader;

class AddBalanceEvent extends EconomyEvent
{

    public static $handlerList;

    public function __construct(Loader $plugin, private $username, private $amount, $issuer)
    {
        parent::__construct($plugin, $issuer);
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