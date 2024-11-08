<?php

namespace xtcy\odysseyrealm\commands\jackpot;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class JackpotTopSubCommand extends \CortexPE\Commando\BaseSubCommand
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

        $page = isset($args["page"]) && is_numeric($args["page"]) && $args["page"] > 0 ? (int)$args["page"] : 1;
        $players = Loader::getInstance()->getServer()->getOnlinePlayers();
        $totalPages = ceil(count($players) / 5);
        $start = ($page - 1) * 5;
        $end = $start + 5;
        $place = 1; // Initialize $place to start at 1

        // Sort players by jackpot earnings in descending order
        usort($players, function($a, $b) {
            $earningsA = Loader::getSessionManager()->getSession($a)->getJackpotEarnings();
            $earningsB = Loader::getSessionManager()->getSession($b)->getJackpotEarnings();
            return $earningsB <=> $earningsA;
        });

        $text = "§r§6§lTop Jackpot Winners (§e$page" . "§e§l/§e$totalPages" . "§6§l)";

        for ($i = $start; $i < $end && $i < count($players); $i++) {
            $player = $players[$i];
            $session = Loader::getSessionManager()->getSession($player);
            $wins = $session->getJackpotWins();
            $earnings = number_format($session->getJackpotEarnings());
            $text .= "\n" . "§r§6§l$place. §r§f{$session->getUsername()} §r§6- §r§e§l$wins §r§eWins §l(§r§6$" . $earnings . "§e§l)";
            $place++;
        }

        $sender->sendMessage($text);
    }
}