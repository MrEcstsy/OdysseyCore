<?php

namespace xtcy\odysseyrealm\commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\Loader;
use xtcy\odysseyrealm\player\OdysseyPlayer;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class BalanceTopCommand extends BaseCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
        $this->registerArgument(0, new IntegerArgument("page", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $session = Loader::getInstance()->getSessionManager()->getSession($sender);
        $balance = number_format($session->getBalance());
        $page = isset($args["page"]) && is_numeric($args["page"]) && $args["page"] > 0 ? (int)$args["page"] : 1;
        $players = Loader::getInstance()->getServer()->getOnlinePlayers();

        // Sort players by balance in descending order
        usort($players, function($a, $b) {
            $balanceA = Loader::getSessionManager()->getSession($a)->getBalance();
            $balanceB = Loader::getSessionManager()->getSession($b)->getBalance();
            return $balanceB <=> $balanceA;
        });

        $totalPages = ceil(count($players) / 5);
        $start = ($page - 1) * 5;
        $end = $start + 5;
        $displayedPlayers = [];

        $text = "§r§e§lTop Balances (§6$page" . "§e§l/§6$totalPages" . "§e§l)";

        for ($i = $start; $i < $end && $i < count($players); $i++) {
            $player = $players[$i];
            $odysseyPlayer = Loader::getSessionManager()->getSession($player);

            if (!$odysseyPlayer instanceof OdysseyPlayer || in_array($player->getName(), $displayedPlayers)) {
                continue;
            }

            $balance = number_format($odysseyPlayer->getBalance());
            $text .= "\n" . "§r§6§l" . ($i + 1) . ". §r§e{$odysseyPlayer->getUsername()}: §r§6§l$$balance";
            $displayedPlayers[] = $player->getName();
        }

        $sender->sendMessage($text);
    }

}