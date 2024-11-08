<?php

namespace xtcy\odysseyrealm\utils\Menus;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use xtcy\odysseyrealm\player\PlayerManager;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CollectionMenu
{

    public static function send(Player $player): InvMenu
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $menu->setName(TextFormat::colorize("&r&8Collection"));
        $inventory = $menu->getInventory();
        $session = PlayerManager::getInstance()->getSession($player);

        $collectionData = $session->getCollection();

// Check if $collectionData is not empty and is an array
        if (!empty($collectionData)) {
            // Loop through each item in the collection
            foreach ($collectionData as $item) {
                // Check if $item is an instance of Item
                if ($item instanceof Item) {
                    // Add the item to the inventory if possible
                    if ($inventory->canAddItem($item)) {
                        $inventory->addItem($item);
                    } else {
                        $player->sendMessage(TextFormat::RED . "Your inventory is full. Please make space and try again.");
                    }
                } else {
                    // Handle case where $item is not an instance of Item (e.g., log an error)
                }
            }
        } else {
            // If $collectionData is empty, there are no items in the collection
            $player->sendMessage(TextFormat::RED . "Your collection is empty.");
        }

        // Set the menu listener to readonly
        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction): void {}));

        return $menu;
    }

}
