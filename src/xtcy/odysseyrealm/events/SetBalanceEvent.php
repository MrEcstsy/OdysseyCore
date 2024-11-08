<?php

namespace xtcy\odysseyrealm\events;

use xtcy\odysseyrealm\Loader;

class SetBalanceEvent extends EconomyEvent
{

    public static $handlerList;

    public function __construct(Loader $plugin, private $username, private $money, $issuer)
    {
        parent::__construct($plugin, $issuer);
        $this->username = $username;
        $this->money = $money;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getMoney()
    {
        return $this->money;
    }

}