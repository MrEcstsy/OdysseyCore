<?php

namespace xtcy\odysseyrealm\commands;

use xtcy\odysseyrealm\utils\Menus\CollectionMenu;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class CollectCommand extends \CortexPE\Commando\BaseCommand
{

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        CollectionMenu::send($sender)->send($sender);
    }
}