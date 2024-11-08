<?php

namespace xtcy\odysseyrealm\utils\Menus;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\Form;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\items\Vouchers;
use wockkinmycup\utilitycore\utils\Utils;

class XPShopMenu {

    public static function getMenu(Player $player) : InvMenu {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName(C::colorize("&r&8XP Shop"));
        $inventory = $menu->getInventory();
        $playerExp = $player->getXpManager()->getCurrentTotalXp();
        $playerXpItem = VanillaItems::EXPERIENCE_BOTTLE()->setCustomName(C::colorize("&r&l&6Ethereal &fHub"))->setLore([
            C::colorize("&r&7Discover the mysteries of the server"),
            C::colorize("&r&7Visit the EXP shop, where wonders await"),
            "",
            C::colorize("&r&aYour Current EXP: &f" . number_format($playerExp) . " EXP"),
        ]);

        $menu->getInventory()->setItem(49, $playerXpItem);
        $menu->getInventory()->setItem(47, VanillaBlocks::CARPET()->setColor(DyeColor::RED)->asItem()->setCustomName(C::colorize("&r&l&cGo Back"))->setLore([C::colorize("&r&7Go back a page")]));
        $menu->getInventory()->setItem(51, VanillaBlocks::CARPET()->setColor(DyeColor::GREEN)->asItem()->setCustomName(C::colorize("&r&l&aNext Page"))->setLore([C::colorize("&r&7Go forward a page")]));

        /*
                10, 11, 12, 13, 14, 15, 16,
                19, 20, 21, 22, 23, 24, 25,
                28, 29, 30, 31, 32, 33, 34,
                37, 38, 39, 40, 41, 42, 43
         */
        $page_1 = [ # 28 items per page
            10 => (VanillaItems::PAPER()->setCustomName(C::colorize('&r&l&eRANK "&r&fSeeker&e&l"'))->setLore([C::colorize('&r&7Right-click this item to'), C::colorize('&r&7receive Seeker rank on Odyssey Realm'), "", C::colorize("&r&l&ePrice: &r&f1,000 EXP")])),
            // Add more items
        ];

        foreach ($page_1 as $slot => $item) {
            $inventory->setItem($slot, $item);
        }

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) : void  {
            $player = $transaction->getPlayer();
            $itemClicked = $transaction->getItemClicked();
            $slot = $transaction->getAction()->getSlot();

            // Go Back
            if ($slot === 47) {

            }

            // Go Forward
            if ($slot === 51) {

            }

            // Items
            if ($slot === 10) {
                $player->removeCurrentWindow();
                $player->sendForm(self::openConfirmationForm(Vouchers::giveRankVoucher("Seeker"), 1000));

            }

        }));
        $excludedSlots = [47, 49, 51];

        Utils::fillBorders($inventory, "gray_stained_glass_pane", $excludedSlots);
        return $menu;
    }

    private static function openConfirmationForm($item, $price) : Form {
        $form = new CustomForm(function (Player $player, array $data) use ($item, $price) {
            $amountToBuy = (int) ($data["amount"] ?? 1);

            if ($amountToBuy <= 0) {
                $player->sendMessage(C::RED . "Invalid amount. Please enter a valid number.");
                return;
            }

            $totalCost = $amountToBuy * $price;

            if ($player->getXpManager()->getCurrentTotalXp() >= $totalCost) {
                $player->getXpManager()->subtractXp($totalCost);
                $player->getInventory()->addItem($item->setCount($amountToBuy)); // Set the item count
                $player->sendMessage(C::GREEN . "Purchase successful! Total cost: " . number_format($totalCost) . " EXP");
            } else {
                $player->sendMessage(C::RED . C::BOLD . "(!) " . C::RESET . C::RED . "You do not have enough EXP.");
                $player->sendMessage(C::colorize("&r&7Your XP: " . number_format($player->getXpManager()->getCurrentTotalXp())));
            }
        });

        $form->setTitle(C::colorize("&r&l&aConfirm Purchase"));
        $form->addLabel(C::colorize("&r&7Are you sure you want to purchase: &r&l&e" . $item->getCustomName()));
        $form->addInput("Enter Amount to Buy:", "1", "1", "amount");
        return $form;
    }
}