<?php

namespace xtcy\odysseyrealm\commands\jackpot;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\events\JackPotEvent;
use xtcy\odysseyrealm\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use xtcy\bn\utils\Utils;

class JackpotStatsSubCommand extends \CortexPE\Commando\BaseSubCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
        $this->registerArgument(0, new RawStringArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (!isset($args["name"])) {
            JackPotEvent::getInstance()->formatStats($sender);
            return;
        }
        if (isset($args["name"])) {
            if (!$player = Utils::getPlayerByPrefix($args["name"])) {
                $sender->sendMessage("§r§c§l(!) §r§c" . $args["name"] . " is not locally online.");
                return;
            }
            $session = Loader::getSessionManager()->getSession($player);
            $mytickets = JackPotEvent::getInstance()->getTickets($player->getName());
            $wins = $session->getJackPotWins();
            $sender->sendMessage("§r§d§lOdyssey Jackpot Stats §r§7({$player->getName()})");
            $sender->sendMessage("§r§bTotal Winnings: §r§d§l$" . "§r§d". number_format($session->getJackPotEarnings(),2));
            $sender->sendMessage("§r§b§lTotal Tickets Purchased: §r§d$mytickets");
            $sender->sendMessage("§r§b§lTotal Jackpot Wins: §r§d$wins");
        }
    }
}