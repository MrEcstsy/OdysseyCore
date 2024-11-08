<?php

namespace xtcy\odysseyrealm\utils\Menus;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use xtcy\odysseyrealm\Loader;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TitlesMenu
{

    public static function send(Player $player): ?InvMenu
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $inv = $menu->getInventory();
        $menu->setName(TextFormat::colorize("&r&7Titles"));

        $titles = [
            ["Vlone", "titles.vlone", "§cVlone"],
            ["SleepyJoe", "titles.sleepyjoe", "§eSleepy§6Joe"],
            ["Voter", "titles.voter",  "§aVoter"],
            ["L", "titles.l", "§cL"],
            ["Shogun", "titles.shogun", "§4◆ §6SH§eOG§6UN §4◆"]
        ];

        foreach ($titles as [$titleName, $permission, $displayName]) {
            $item = VanillaItems::EMERALD()->setCustomName(TextFormat::colorize("&r&l{$displayName}"));
            if ($player->hasPermission($permission)) {
                $item->setLore([TextFormat::colorize("&r&7Click to equip this title."), TextFormat::colorize("&r&aYou own the {$titleName} title")]);
            } else {
                $item->setLore([TextFormat::colorize("&r&7Unlock this title through your in-game travels!"), TextFormat::colorize("&r&cYou do not own the {$titleName} title")]);
            }
            $inv->addItem($item);

            $inv->setItem(49, VanillaItems::REDSTONE_DUST()->setCustomName(TextFormat::colorize("&r&cRemove Title")));
        }

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($player, $titles): void {
            $itemClicked = $transaction->getItemClicked();
            $session = Loader::getSessionManager()->getSession($player);
            $slot = $transaction->getAction()->getSlot();

            if ($slot === 49) {
                $player->sendMessage(TextFormat::colorize("&r&c&l(!) &r&cRemoved title"));
                $session->setTitle("");
                $player->removeCurrentWindow();
            }

            foreach ($titles as [$titleName, $permission, $displayName]) {
                if ($itemClicked->getCustomName() === TextFormat::colorize("&r&l{$displayName}")) {
                    if ($player->hasPermission($permission)) {
                        $player->sendMessage(TextFormat::colorize("&r&l&a(!) &r&aYou have selected &l{$displayName}&r&a!"));
                        $session->setTitle("§r{$displayName}");
                    } else {
                        $player->sendMessage(TextFormat::colorize("&r&l&c(!) &r&cYou do not have permission to use the {$titleName} title!"));
                    }
                    $player->removeCurrentWindow();
                    return;
                }
            }
        }));

        return $menu;
    }

}