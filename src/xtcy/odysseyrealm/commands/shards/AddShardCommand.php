<?php

namespace xtcy\odysseyrealm\commands\shards;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\utils\Utils;

class AddShardCommand extends \CortexPE\Commando\BaseCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.add_shards");
        $this->setPermissionMessage(Loader::NO_PERMISSION);
        $this->registerArgument(0, new RawStringArgument("player", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $playerName = $args["player"];
        $amount = $args["amount"];

        if (!is_numeric($amount) || $amount < 0) {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cEnter a valid number."));
            return;
        }

        if (empty($playerName)) {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cSpecify a player name."));
        }

        $player = Utils::getPlayerByPrefix($playerName);

        if ($player === null) {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cThe player '&f" . $playerName . "&c' is not online."));
            return;
        }

        $session = Loader::getSessionManager()->getSession($player);

        $session->addShards($amount);
        $sender->sendMessage(TextFormat::colorize("&r&l&a(!) &r&aAdded " . number_format($amount) . "⛁ to player '&f" . $player->getName() . "&a'."));

    }
}