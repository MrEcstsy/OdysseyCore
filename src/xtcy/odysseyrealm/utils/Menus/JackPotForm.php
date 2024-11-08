<?php

namespace xtcy\odysseyrealm\utils\Menus;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use xtcy\odysseyrealm\events\JackPotEvent;
use pocketmine\player\Player;

class JackPotForm extends MenuForm
{

    /** @var int */
    public int $amount;

    /** @var int */
    public int $tickets;

    /**
     * @param int $amount
     * @param int $tickets
     */
    public function __construct(int $amount, int $tickets)
    {
        $options = [
            new MenuOption("§r§2Confirm Purchase"),
            new MenuOption("§r§4Cancel Purchase"),
        ];
        $this->tickets = $tickets;
        $this->amount = $amount;
        $price = number_format($amount);
        $m = "§r§6§lOdyssey's Ticket Merchant \n §r§fYou are about to purchase §r§2$tickets §r§fticket(s) §r§ffor §r§2$" . number_format($amount) . "!";

        $onSubmitClosure = function (Player $player, int $selectedOption): void {
            switch ($selectedOption) {
                case 0:
                    JackPotEvent::getInstance()->addTickets($player,$this->tickets, $this->amount);
                    break;
                case 1:
                    // Handle cancel purchase logic
                    break;
            }
        };


        parent::__construct("§r§8Confirm Ticket Purchase", $m, $options, $onSubmitClosure);
    }
}