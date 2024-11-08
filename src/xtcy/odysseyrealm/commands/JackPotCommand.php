<?php

namespace xtcy\odysseyrealm\commands;

use xtcy\odysseyrealm\commands\jackpot\JackpotBuySubCommand;
use xtcy\odysseyrealm\commands\jackpot\JackpotStatsSubCommand;
use xtcy\odysseyrealm\commands\jackpot\JackpotTopSubCommand;
use xtcy\odysseyrealm\events\JackPotEvent;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class JackPotCommand extends \CortexPE\Commando\BaseCommand
{

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
        $this->registerSubCommand(new JackpotBuySubCommand($this->plugin,"buy", "Buy jackpot tickets"));
        $this->registerSubCommand(new JackpotStatsSubCommand($this->plugin,"stats", "View your or another players jackpot stats"));
        $this->registerSubCommand(new JackpotTopSubCommand($this->plugin, "top", "View the top jackpot winners"));

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        JackPotEvent::getInstance()->formatMessage($sender);
    }
}