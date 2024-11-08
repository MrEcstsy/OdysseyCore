<?php

namespace xtcy\odysseyrealm\utils;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat as C;

class Lootboxes {

    public static function give(string $lootbox, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($lootbox)) {
            case "equipment":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&6Equipment Lootbox &r&7(Right Click)'));

                $item->setLore([
                    C::colorize('&r&7Right Click to receive a random'),
                    C::colorize('&r&7item from one of these armor sets!'),
                    '',
                    C::colorize('&r&l&6Available Armor Sets'),
                    C::colorize(' &r&l&6* &cPhantom Armor Set'),
                    C::colorize(' &r&l&6* &4Supreme Armor Set'),
                    C::colorize(' &r&l&6* &4Ghoul Armor Set'),
                    C::colorize(' &r&l&6* &9Titan Armor Set')
                ]);

                $item->getNamedTag()->setString("lootbox", "equipment");
                break;
            case "test":
                $item = VanillaBlocks::BEACON()->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&f&lLootbox: &aTest"));

                $item->setLore([
                    C::colorize('&r&7The test lootbox designed by'),
                    C::colorize("&r&7xtcy_ to test lootbox animations."),
                    "",
                    C::colorize('&r&l&fRandom Loot (&r&71 item&l&f)'),
                    C::colorize('&r&f&l * &r&f1x &bBank Note'),
                    C::colorize('&r&f&l * &r&f1x &aExperience Bottle &r&7(Throw)'),
                    C::colorize('&r&f&l * &r&f1x &l&cPhantom Scythe')
                ]);

                $item->getNamedTag()->setString("lootbox", "test");
                break;
        }
        return $item;
    }
}