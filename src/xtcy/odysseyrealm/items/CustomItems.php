<?php

declare(strict_types=1);

namespace xtcy\odysseyrealm\items;

use customiesdevs\customies\item\CustomiesItemFactory;
use xtcy\odysseyrealm\Loader;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\items\custom\BankNoteItem;

final class CustomItems
{

    public function __construct(public Loader $plugin)
    {
        CustomiesItemFactory::getInstance()->registerItem(BankNoteItem::class, "odyssey:bank_note", "Bank Note");
    }

    public static function giveMaxHome(int $amount = 1, int $increment = 1): ?Item {
        $item = VanillaBlocks::BED()->setColor(DyeColor::RED)->asItem()->setCount($amount);

        $item->setCustomName(C::colorize("&r&l&eMax Home Increase &r&7(Right Click)"));
        $item->setLore([
            C::colorize("&r&7Adds +" . $increment . " &e/home &7slots to your player.")
        ]);

        $item->getNamedTag()->setInt("custom_items", $increment);
        return $item;
    }

    public static function giveTitleVoucher(string $title, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($title)) {
            case "vlone":
                $item = StringToItemParser::getInstance()->parse("name_tag")->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&fTITLE '&r&cVlone&r&l&f'"));
                $item->setLore([
                    C::colorize("&r&7Right-Click to unlock this title.")
                ]);

                $item->getNamedTag()->setString("title", "vlone");
                break;
            case "sleepyjoe":
                $item = StringToItemParser::getInstance()->parse("name_tag")->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&fTITLE '&r&eSleepy&6Joe&r&l&f'"));
                $item->setLore([
                    C::colorize("&r&7Right-Click to unlock this title.")
                ]);

                $item->getNamedTag()->setString("title", "sleepyjoe");
                break;
            case "shogun":
                $item = StringToItemParser::getInstance()->parse("name_tag")->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&fTITLE '&r&4◆ &6SH&eOG&6UN &4◆&r&l&f'"));
                $item->setLore([
                    C::colorize("&r&7Right-Click to unlock this title.")
                ]);

                $item->getNamedTag()->setString("title", "shogun");
                break;
        }
        return $item;
    }

    public static function getMask(string $mask, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($mask)) {
            case "purge":
                $item = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::WITHER_SKELETON)->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&cPurge Mask"));
                $item->setLore([
                    C::colorize("&r&c+ 2.5% DMG"),
                    C::colorize("&r&7A great evil is contained within this"),
                    C::colorize("&r&7horrifying mask, Who knows what inner"),
                    C::colorize("&r&7demons it will unleash."),
                    C::colorize("&r&6&l * &r&6Map One"),
                    "",
                    C::colorize("&r&7&oAttach this mask to any helmet"),
                    C::colorize("&r&7&oto give it a visual override!"),
                    "",
                    C::colorize("&r&7To equip, place this mask on a helmet."),
                    C::colorize("&r&7To remove, use /removemask with the helmet in hand.")
                ]);

                $item->getNamedTag()->setString("masks", "purge");
                break;
            case "headless":
                $item = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::WITHER_SKELETON)->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&6Headless Mask"));
                $item->setLore([
                    C::colorize("&r&7A terrifying Mask from the"),
                    C::colorize("&r&7grave of the Headless Horseman."),
                    C::colorize("&r&6&l * &r&6Map One"),
                    "",
                    C::colorize("&r&7&oAttach this mask to any helmet"),
                    C::colorize("&r&7&oto give it a visual override!"),
                    "",
                    C::colorize("&r&7To equip, place this mask on a helmet."),
                    C::colorize("&r&7To remove, use /removemask with the helmet in hand.")
                ]);

                $item->getNamedTag()->setString("masks", "headless");
                break;
            case "pilgrim":
                $item = VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::WITHER_SKELETON)->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&ePilgrim Mask"));
                $item->setLore([
                    C::colorize("&r&c+ 25% XP/Drops"),
                    C::colorize("&r&7tHiS iS oUr lAnD nOw!"),
                    C::colorize("&r&l&6 * Thanksgiving"),
                    "",
                    C::colorize("&r&7&oAttach this mask to any helmet"),
                    C::colorize("&r&7&oto give it a visual override!"),
                    "",
                    C::colorize("&r&7To equip, place this mask on a helmet."),
                    C::colorize("&r&7To remove, use /removemask with the helmet in hand.")
                ]);

                $item->getNamedTag()->setString("masks", "pilgrim");
                break;
        }

        return $item;
    }

    public static function getItemMod(string $itemMod, int $amount = 1): ?Item
    {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($itemMod)) {
            case "flaming_halo":
                $item = StringToItemParser::getInstance()->parse("ender_eye")->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&fItem Mod (&eFlaming &6Halo&f)'));
                $item->setLore([
                    C::colorize("&r&c+4% Outgoing Damage"),
                    "",
                    C::colorize("&r&7Attach this mod to any &fHelmet"),
                    C::colorize("&r&7to give it a speciality modification."),
                    "",
                    C::colorize("&r&7Drag n' Drop onto item to attach."),
                    C::colorize("&r&7To remove, execute /removeitemmod.")
                ]);

                $item->getNamedTag()->setString("item_mod", "flaming_halo");
                break;
        }
        return $item;
    }

    public static function getBankNote(int $amount = 1): BankNoteItem
    {
        return CustomiesItemFactory::getInstance()->get("odyssey:bank_note")->setCount($amount);
    }
}