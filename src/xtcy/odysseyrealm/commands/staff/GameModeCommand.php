<?php

namespace xtcy\odysseyrealm\commands\staff;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\utils\Utils;

class GameModeCommand extends BaseCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.gamemode");
        $this->registerArgument(0, new RawStringArgument("mode", true));
        $this->registerArgument(1, new RawStringArgument("name", true));
        $this->setPermissionMessage(Loader::NO_PERMISSION);

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (!isset($args["mode"])) {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cYou must provide a GameMode!"));
            $sender->sendMessage(TextFormat::colorize("&r&7List of valid gamemodes: survival, creative, adventure, spectator"));
            return;
        }

        $gameMode = GameMode::fromString($args["mode"]);
        if ($gameMode === null) {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cThe specified GameMode is invalid!"));
            $sender->sendMessage(TextFormat::colorize("&r&7List of valid gamemodes: survival, creative, adventure, spectator"));
            return;
        }

        $player = $sender; // Default to $sender if no name is provided
        if (isset($args["name"])) {
            if (!$player = Utils::getPlayerByPrefix($args["name"])) {
                $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cThe player '&f" . $args["name"] . "&c' is not online."));
                return;
            }
        }

        $sender->sendMessage(TextFormat::colorize("&r&l&a(!) &r&aThe player " . $player->getName() . "'s GameMode has been updated."));
        $player->setGamemode(GameMode::fromString($args["mode"]));

        $player->sendMessage(TextFormat::colorize("&r&8» &7(&l&eGAMEMODE CHANGE&f&7) &r&8«"));
        $player->sendMessage(TextFormat::colorize("&r&7Your GameMode has been set to: " . $gameMode->getEnglishName()));
    }
}