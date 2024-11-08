<?php

namespace xtcy\odysseyrealm\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use xtcy\odysseyrealm\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\utils\Utils;

class HealCommand extends \CortexPE\Commando\BaseSubCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.heal");
        $this->setPermissionMessage(Loader::NO_PERMISSION);
        $this->registerArgument(0, new RawStringArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }
        $cooldown = Loader::getSessionManager()->getSession($sender)->getCooldown("heal");

        if ($cooldown === null || $cooldown === 0) {
            $sender->setHealth(20);
            $sender->sendMessage(TextFormat::colorize("&r&l&e/heal: &r&eYour health has been fully restored."));
            Loader::getSessionManager()->getSession($sender)->addCooldown("heal", 60);
        } else {
            $sender->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cYou cannot use /heal for another " . Utils::translateTime($cooldown) . "!"));
        }
    }
}