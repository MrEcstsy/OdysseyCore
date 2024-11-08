<?php

namespace xtcy\odysseyrealm\commands\balance;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;

class BalanceCommand extends BaseSubCommand
{

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission("command.default");

        $this->registerArgument(0, new RawStringArgument("name", true));

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $session = Loader::getSessionManager()->getSession($sender);
        if ($session === null) {
            $sender->sendMessage(C::colorize("&r&l&cError: Unable to retrieve your session."));
            return;
        }

        $balance = $session->getBalance();

        if (isset($args["name"])) {
            $name = $args["name"];
            $player = Utils::getPlayerByPrefix($name);

            if ($player !== null) {
                $PSession = Loader::getSessionManager()->getSession($player);

                if ($PSession === null) {
                    $sender->sendMessage(C::colorize("&r&l&cError: Unable to retrieve session for $name."));
                    return;
                }

                $Pbalance = $PSession->getBalance();
                $sender->sendMessage(C::colorize("&r&l&6Ethereal &fHub &8» &r&f{$player->getName()}'s Balance: $" . number_format($Pbalance)));
                if ($Pbalance > $balance) {
                    $sender->sendMessage(C::colorize("&r&7&oThey are richer than you!"));
                } elseif ($Pbalance < $balance) {
                    $sender->sendMessage(C::colorize("&r&7&oYou are richer than them!"));
                } else {
                    $sender->sendMessage(C::colorize("&r&7&oYour balances are equal!"));
                }
            } else {
                $sender->sendMessage(C::colorize("&r&l&cError: Player not found."));
            }
        } else {
            $sender->sendMessage(C::colorize("&r&l&6Ethereal &fHub &8» &r&fYour Balance: $" . number_format($balance)));
        }
    }

}