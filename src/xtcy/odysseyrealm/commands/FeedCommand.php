<?php

namespace xtcy\odysseyrealm\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\utils\Utils;

class FeedCommand extends \CortexPE\Commando\BaseSubCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.feed");
        $this->setPermissionMessage(Loader::NO_PERMISSION);
        $this->registerArgument(0, new RawStringArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }
        $cooldown = Loader::getSessionManager()->getSession($sender)->getCooldown("feed");

        if (empty($args["name"])) {
            $player = $sender;
        } else {
            $player = Utils::getPlayerByPrefix($args["name"]);
        }

        if ($player !== null) {
            if ($cooldown === null || $cooldown === 0) {
                $playerName = $player === $sender ? "Your" : $player->getName() . "'s";
                $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                $sender->sendMessage(TextFormat::colorize("&r&l&e/feed: &r&e{$playerName} hunger has been replenished."));
                if ($player !== $sender) {
                    $player->sendMessage(TextFormat::colorize("&r&l&e/feed: &r&eYour hunger has been replenished by {$sender->getName()}."));
                }
                Loader::getSessionManager()->getSession($sender)->addCooldown("feed", 60);
            } else {
                $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cYou cannot use /feed for another " . Utils::translateTime($cooldown) . "!"));
            }
        } else {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cNo online player by the name '" . $args["name"] . "' could be found!"));
        }
    }
}