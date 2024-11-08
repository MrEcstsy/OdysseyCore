<?php

namespace xtcy\odysseyrealm\events;

use xtcy\odysseyrealm\Loader;

class BalanceChangeEvent extends EconomyEvent {

    public static $handlerList;

    public function __construct(Loader $plugin, private $username, private $newMoney, $issuer, private $oldMoney = null)
    {
        parent::__construct($plugin, $issuer);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getBalance(): int
    {
        return $this->newMoney;
    }

    /**
     * @return float
     */
    public function getNewBalance()
    {
        return $this->newMoney;
    }


    /**
     * @return float|null
     */
    public function getOldBalance()
    {
        return $this->oldMoney;
    }

}