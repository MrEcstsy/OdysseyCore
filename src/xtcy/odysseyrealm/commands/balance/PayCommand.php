<?php

namespace xtcy\odysseyrealm\commands\balance;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\utils\Utils;

class PayCommand extends \CortexPE\Commando\BaseSubCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
        $this->registerArgument(0, new RawStringArgument("player", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $session = Loader::getSessionManager()->getSession($sender);
        $balance = $session->getBalance();

        if ($balance < $args["amount"]) {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cYou don't have sufficient funds!"));
            return;
        }

        if ($args['amount'] > 100000000000) {
            $sender->sendMessage(TextFormat::colorize("&r&cAmount must be less than or equal to 100,000,000,000."));
            return;
        }

        if (!$player = Utils::getPlayerByPrefix($args["player"])) {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cNo online player by the name of '" . $args["player"] . "' could be found!"));
            return;
        }

        $pSession = Loader::getSessionManager()->getSession($player);

        $session->subtractBalance($args["amount"]);
        $pSession->addBalance($args["amount"]);

        $sender->sendMessage(TextFormat::colorize("&r&aYou paid $" . number_format($args["amount"]) . " to " . $player->getName() . "!"));
        $player->sendMessage(TextFormat::colorize("&r&aYou received $" . number_format($args["amount"]) . " from " . $sender->getName() . "!"));
    }
}