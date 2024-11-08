<?php

namespace xtcy\odysseyrealm\commands;

use xtcy\odysseyrealm\utils\Menus\TitlesMenu;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class TitlesCommand extends \CortexPE\Commando\BaseCommand
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

        TitlesMenu::send($sender)->send($sender);
    }
}
