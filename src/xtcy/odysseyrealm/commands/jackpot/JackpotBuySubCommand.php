<?php

namespace xtcy\odysseyrealm\commands\jackpot;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\utils\Menus\JackPotForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class JackpotBuySubCommand extends BaseSubCommand
{

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission("command.default");
        $this->registerArgument(0, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }
        if(!isset($args["amount"])){
            $form = new JackpotForm(1000, 1);
            $sender->sendForm($form);
            return;
        }
        if($args["amount"] <= 0){
            $sender->sendMessage("§r§c§l(!) §r§cThe amount must be greater then 0.");
            return;
        }
        $argument = $args["amount"] !== null ? $args["amount"] : 1;
        $form = new JackpotForm($args["amount"] * 1000, $args["amount"]);
        $sender->sendForm($form);
    }

}