<?php

namespace xtcy\odysseyrealm\commands;

use CortexPE\Commando\BaseCommand;
use xtcy\odysseyrealm\utils\Menus\XPShopMenu;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class XPShopCommand extends BaseCommand {

    public function prepare(): void
    {
        $this->setPermission("command.odyssey.xpshop");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cYou must run this command in-game!"));
            return;
        }

        XPShopMenu::getMenu($sender)->send($sender);
    }
}