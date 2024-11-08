<?php

namespace xtcy\odysseyrealm\listeners;

use Exception;
use IvanCraft623\RankSystem\RankSystem;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use xtcy\odysseyrealm\Loader;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\BlockBreakSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;
use wockkinmycup\utilitycore\addons\customArmor\sets\PhantomSet;
use wockkinmycup\utilitycore\items\custom\BankNoteItem;
use wockkinmycup\utilitycore\items\Vouchers;
use wockkinmycup\utilitycore\Loader as UTLoader;
use wockkinmycup\utilitycore\tasks\EquipmentLootboxTask;
use wockkinmycup\utilitycore\tasks\TestLootboxTask;
use wockkinmycup\utilitycore\utils\ItemRenameManager;
use wockkinmycup\utilitycore\utils\Utils;

class ItemListeners implements Listener {

    /** @var array $loreMessages */
    public static array $loreMessages = [];

    /** @var array $renameMessages */
    public static array $renameMessages = [];

    /** @var array $itemRenamer */
    public static array $itemRenamer = [];

    /** @var array $lorerenamer */
    public static array $lorerenamer = [];

    public function onPlace(BlockPlaceEvent $e) {
        $i = $e->getItem();
        $t = $i->getNamedTag();
        if ($t->getTag("lootbox")) {
            $e->cancel();
        }

        if ($t->getTag('rank_voucher')) {
            $e->cancel();
        }

        if ($t->getTag('drop_package')) {
            $e->cancel();
        }

        if ($t->getTag("custom_items")) {
            $e->cancel();
        }

        if ($t->getTag("masks")) {
            $e->cancel();
        }
    }

    public function onUse(PlayerItemUseEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $tag = $item->getNamedTag();

        if ($tag->getTag("drop_package")) {
            $drop_packageTag = $tag->getString("drop_package");
            if ($drop_packageTag === "simple") {
                $item->pop();
                $player->getInventory()->setItemInHand($item);

                $simpleDP = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
                $simpleInv = $simpleDP->getInventory();
                $simpleDP->setName(TextFormat::colorize("&r&8Simple " . Utils::getConfiguration(UTLoader::getInstance(), "config.yml")->get("server-name") . " Chest"));
                $chestNumber = 1;
                $clickedChests = [];

                for ($slot = 0; $slot < 54; $slot++) {
                    $row = (int)($slot / 9);
                    $column = $slot % 9;

                    if ($row === 0 || $row === 5 || $column === 0 || $column === 8) {
                        $simpleInv->setItem($slot, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK)->asItem());
                    } else {
                        $mysteryItem = VanillaBlocks::STAINED_GLASS()->setColor(DyeColor::WHITE)->asItem();
                        $mysteryItem->setCustomName(TextFormat::colorize("&r&f??? &l#{$chestNumber}"));
                        $mysteryItem->setLore([
                            TextFormat::colorize("&r&7Choose &f5 mystery items &7and,"),
                            TextFormat::colorize("&r&7your &f&lSimple &r&7loot will be revealed.")
                        ]);

                        $mysteryItem->getNamedTag()->setInt("mystery_chest", $chestNumber);

                        $simpleInv->setItem($slot, $mysteryItem);

                        $chestNumber++;
                    }
                }

                $clickCount = 0;
                $playerData = new \stdClass();
                $playerData->selectedItems = [];

                $simpleDP->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use (&$clickCount, $simpleInv, &$clickedChests, $player, &$playerData) {
                    $itemClicked = $transaction->getItemClicked();
                    $slot = $transaction->getAction()->getSlot();

                    $namedTag = $itemClicked->getNamedTag();

                    if ($namedTag !== null && $namedTag->getTag("mystery_chest") && $slot >= 9 && $slot < 45) {
                        Utils::playSound($player, "random.click");

                        $isSelected = in_array($slot, $clickedChests);

                        if ($isSelected) {
                            $clickCount--;
                            $clickedChests = array_diff($clickedChests, [$slot]);

                            $simpleInv->setItem($slot, VanillaBlocks::CHEST()->asItem());

                            if ($clickCount === 5) {
                                $clickCount--;
                            }
                        } elseif ($clickCount < 5) {
                            $clickCount++;
                            $clickedChests[] = $slot;

                            $chestItem = VanillaBlocks::CHEST()->asItem();
                            $chestItem->setCustomName(TextFormat::colorize("&r&l&fMystery Item #{$namedTag->getInt("mystery_chest")}"));
                            $chestItem->setLore([TextFormat::colorize("&r&7You have selected this mystery item.")]);

                            $simpleInv->setItem($slot, $chestItem);

                            if ($clickCount === 5) {
                                $lootTable = [
                                    VanillaItems::DIAMOND(),
                                    VanillaItems::STEAK()->setCount(16),
                                    VanillaItems::GOLDEN_APPLE()->setCount(8),
                                    VanillaBlocks::SAND()->asItem()->setCount(32),
                                    VanillaBlocks::OBSIDIAN()->asItem()->setCount(16),
                                    Vouchers::createMoneyNote(null, 5000),
                                    Vouchers::createXPBottle(null, 1000),
                                    VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1)),
                                    VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 1)),
                                    VanillaBlocks::TORCH()->asItem()->setCount(16),
                                ];

                                foreach (range(1, 5) as $_) {
                                    array_push($playerData->selectedItems, $lootTable[array_rand($lootTable)]);
                                }

                                //$transaction->getPlayer()->removeCurrentWindow();
                            }
                        }
                    }
                }));

                $simpleDP->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory) use ($clickedChests, $playerData) {
                    foreach ($clickedChests as $clickedSlot) {
                        $item = $inventory->getItem($clickedSlot);
                        if ($item !== null && $item->equals(VanillaItems::AIR())) {
                            // Do something if needed
                        }
                    }

                    foreach ($playerData->selectedItems as $selectedItem) {
                        $player->getInventory()->addItem($selectedItem);
                        $player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(1000));

                    }
                });

                $simpleDP->send($player);
            }
        }

        if ($tag->getTag("custom_items")) {
            $custom_itemTag = $tag->getString("custom_items");
            Loader::getHomeManager()->addMaxHomes($player->getUniqueId(), $custom_itemTag);
            $player->sendMessage(TextFormat::colorize("&r&l&a(!) &r&a+" . $custom_itemTag . " homes have been added to your account."));
            $item->pop();
            $player->getInventory()->setItemInHand($item);
        }

        if ($tag->getTag("title")) {
            $title_tag = $tag->getString("title");
            if ($title_tag === "vlone") {
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                $rankSystem = RankSystem::getInstance()->getSessionManager()->get($player);
                $rankSystem->setPermission("titles.vlone");
                $player->sendMessage(TextFormat::colorize("&r&l&6(!) &r&6You've UNLOCKED the &e" . ucfirst($title_tag) . " &6title!"));
                $player->sendMessage(TextFormat::colorize("&r&7Equip your new title in the /title menu!"));
            }

            if ($title_tag === "sleepyjoe") {
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                $rankSystem = RankSystem::getInstance()->getSessionManager()->get($player);
                $rankSystem->setPermission("titles.sleepyjoe");
                $player->sendMessage(TextFormat::colorize("&r&l&6(!) &r&6You've UNLOCKED the &e" . ucfirst($title_tag) . " &6title!"));
                $player->sendMessage(TextFormat::colorize("&r&7Equip your new title in the /title menu!"));
            }

            if ($title_tag === "shogun") {
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                $rankSystem = RankSystem::getInstance()->getSessionManager()->get($player);
                $rankSystem->setPermission("titles.shogun");
                $player->sendMessage(TextFormat::colorize("&r&l&6(!) &r&6You've UNLOCKED the &e" . ucfirst($title_tag) . " &6title!"));
                $player->sendMessage(TextFormat::colorize("&r&7Equip your new title in the /title menu!"));
            }
        }

        if ($tag->getTag("lootbox")) {
            $lootboxTag = $tag->getString("lootbox");
            if ($lootboxTag === "equipment") {
                $item->pop();
                $player->getInventory()->setItemInHand($item);

                $equipmentLootbox = InvMenu::create(InvMenuTypeIds::TYPE_HOPPER);
                $equipmentLootbox->setName(TextFormat::colorize("&r&8Equipment Lootbox"));

                $equipmentLootbox->setListener(InvMenu::readonly());
                $equipmentLootbox->setInventoryCloseListener(function () use ($equipmentLootbox, $player): void {
                    $itemToGive = $equipmentLootbox->getInventory()->getItem(2);
                    $player->getInventory()->addItem($itemToGive);
                    $player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(1000));
                    Server::getInstance()->broadcastMessage(TextFormat::colorize('&r&l&e(!) &r&e' . $player->getName() . " has just opened the &l&6Equipment Lootbox&r &eand has\n&egotten\n&r&l&6* &e" . $itemToGive->getCount() . "x " . $itemToGive->getCustomName()));
                });
                $equipmentLootbox->send($player);

                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new EquipmentLootboxTask($equipmentLootbox, $player), 1);
            }

            if ($lootboxTag === "test") {
                $item->pop();
                $player->getInventory()->setItemInHand($item);

                $testLBInv = InvMenu::create(InvMenuTypeIds::TYPE_HOPPER);
                $testLBInv->setName(TextFormat::colorize("&r&8Lootbox: Test"));

                $testLBInv->setListener(InvMenu::readonly());
                $testLBInv->setInventoryCloseListener(function () use ($testLBInv, $player): void {
                    $itemToGive = $testLBInv->getInventory()->getItem(2);
                    $player->getInventory()->addItem($itemToGive);
                });
                $testLBInv->send($player);
                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new TestLootboxTask($testLBInv, $player), 1);
            }
        }

        if ($tag->getTag("rank_voucher")) {
            $rankTag = $tag->getString("rank_voucher");
            $config = Utils::getConfiguration(UTLoader::getInstance(), 'config.yml')->getAll();
            if (!isset($config["vouchers"][$rankTag])) {
                throw new Exception("Invalid rank voucher identifier: $rankTag");
            }

            $rankVoucherData = $config['vouchers'][$rankTag];

            $commands = $rankVoucherData['settings']['command'];
            $message = $rankVoucherData['settings']['message'];
            $requirePermission = $rankVoucherData['settings']['require-permission'];
            $permission = $rankVoucherData['settings']['permission'];
            $soundToggle = $rankVoucherData['settings']['sound-toggle'];
            $sound = $rankVoucherData['settings']['sound'];

            foreach ($commands as $command) {
                if ($requirePermission === true && $player->hasPermission($permission)) {
                    $command = str_replace("{player}", $player->getName(), $command);
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Loader::getInstance()->getServer(), Loader::getInstance()->getServer()->getLanguage()), $command);
                } elseif ($requirePermission === false) {
                    $command = str_replace("{player}", $player->getName(), $command);
                    Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Loader::getInstance()->getServer(), Loader::getInstance()->getServer()->getLanguage()), $command);
                }

                if ($message !== null && trim($message) !== "") {
                    $player->sendMessage(TextFormat::colorize($message));
                }

                if ($soundToggle === true) {
                    Utils::playSound($player, $sound);
                } elseif ($soundToggle === false) {
                    return;
                }
            }

            $item->pop();
            $player->getInventory()->setItemInHand($item);
        }

        if ($tag->getTag("voucher")) {
            $voucherTag = $tag->getString("voucher");
            if ($voucherTag === "lore_crystal") {
                if (isset(self::$lorerenamer[$player->getName()])) {
                    $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a lore rename tag type cancel to remove it!");
                    return;
                }
                if (isset(self::$itemRenamer[$player->getName()])) {
                    $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a item tag type cancel to remove it!");
                    return;
                }
                self::$lorerenamer[$player->getName()] = $player;
                $player->sendMessage("    §r§6§lLore Rename Usage");
                $player->sendMessage("§r§61. §r§7Hold the ITEM you'd like to edit.");
                $player->sendMessage("§r§62. §r§7Send the new name as a chat message §lwith & color codes§r§7.");
                $player->sendMessage("§r§63. §r§7Confirm the preview of the new name that is displayed.");
                Utils::playSound($player, "mob.enderdragon.flap", 2);
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                return;
            }

            if ($voucherTag === "rename_tag") {
                if (isset(self::$itemRenamer[$player->getName()])) {
                    $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a item tag type cancel to remove it!");
                    return;
                }
                if (isset(self::$lorerenamer[$player->getName()])) {
                    $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a lore rename tag type cancel to remove it!");
                    return;
                }
                self::$itemRenamer[$player->getName()] = $player;
                $player->sendMessage("    §r§6§lRename-Tag Usage");
                $player->sendMessage("§r§61. §r§7Hold the ITEM you'd like to rename.");
                $player->sendMessage("§r§62. §r§7Send the new name as a chat message §lwith & color codes§r§7.");
                $player->sendMessage("§r§63. §r§7Confirm the preview of the new name that is displayed.");
                Utils::playSound($player, "mob.enderdragon.flap", 2);
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                return;
            }
        }

        if ($tag->getInt("banknote", 0) !== 0) {
            $event->cancel();
            $value = $tag->getInt("banknote");
            $formatted = number_format($value,2);
            $player->sendMessage("§r§a§l+ $formatted$");
            $player->getWorld()->addSound($player->getLocation(), new XpCollectSound());
            Loader::getSessionManager()->getSession($player)->addBalance($value);
            $item->pop();
            $player->getInventory()->setItemInHand($item);
        }

        if ($tag->getInt("xpbottle", 0) !== 0) {
            $event->cancel();
            $value = $tag->getInt("xpbottle");
            $formatted = number_format($value,1);
            $player->sendMessage("§r§a§l+ $formatted xp");
            $player->getWorld()->addSound($player->getLocation(), new BlockBreakSound(VanillaBlocks::GLASS()));
            $player->getXpManager()->addXp($value);
            $item->pop();
            $player->getInventory()->setItemInHand($item);
        }
    }

    /**
     * @priority HIGHEST
     */
    public function onItemRename(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $hand = $player->getInventory()->getItemInHand();

        if (!isset(self::$itemRenamer[$player->getName()])) {
            return;
        }

        $message = $event->getMessage();
        $event->cancel();

        if ($message === "cancel") {
            ItemRenameManager::handleCancel($player);
            return;
        }

        if ($hand->getTypeId() === VanillaItems::AIR()->getTypeId()) {
            ItemRenameManager::sendMessageFormats($player, "items.itemnametag.messages.air");
            return;
        }

        if (!Utils::isToolOrArmor($hand)) {
            ItemRenameManager::sendMessageFormats($player, "items.itemnametag.messages.not-tool-armor");
            return;
        }

        if ($message === "confirm" && isset(self::$renameMessages[$player->getName()])) {
            ItemRenameManager::handleConfirmation($player);
            return;
        }

        if (strlen($message) > 26) {
            $player->sendMessage("§r§cYour custom name exceeds the 36 character limit.");
            return;
        }

        ItemRenameManager::handlePreview($player, $message);
    }

    public function onLoreRename(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        if (!isset(self::$lorerenamer[$player->getName()])) {
            return;
        }
        $message = $event->getMessage();
        $hand = $player->getInventory()->getItemInHand();
        $event->cancel();
        if ($message === "cancel") {
            $player->sendMessage("§r§c§l** §r§cYou have unqueued your Lore-Renamer for this usage.");
            Utils::playSound($player,"mob.enderdragon.flap",2);
            unset(self::$lorerenamer[$player->getName()]);
            if (isset(self::$loreMessages[$player->getName()])) unset(self::$loreMessages[$player->getName()]);
            $player->getInventory()->addItem(Vouchers::give("lore_crystal", 1));
        }
        if ($event->getMessage() === "confirm" && isset(self::$loreMessages[$player->getName()])) {
            $player->sendMessage("§r§e§l(!) §r§eYour ITEM's lore has been set to: '" . self::$loreMessages[$player->getName()] . "§e'");
            $player->getLocation()->getWorld()->addSound($player->getLocation(), new XpLevelUpSound(100));
            $lore = $hand->getLore();
            $lore[] = self::$loreMessages[$player->getName()];
            $hand->setLore($lore);
            $player->getInventory()->setItemInHand($hand);
            unset(self::$lorerenamer[$player->getName()]);
            unset(self::$loreMessages[$player->getName()]);
        }
        if (strlen($event->getMessage()) > 18) {
            $player->sendMessage("§r§cYour custom lore exceeds the 18 character limit.");
            return;
        }
        if (!isset(self::$loreMessages[$player->getName()]) && $event->getMessage() !== "cancel" &&  $event->getMessage() !== "confirm") {
            $formatted = TextFormat::colorize($message);
            $player->sendMessage("§r§e§l(!) §r§eItem Name Preview: $formatted");
            $player->sendMessage("§r§7Type '§r§aconfirm§7' if this looks correct, otherwise type '§ccancel§7' to start over.");
            self::$loreMessages[$player->getName()] = $formatted;
        }
    }

    public function onPlayerDropMask(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());

        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $items = [ItemTypeIds::DIAMOND_HELMET, ItemTypeIds::NETHERITE_HELMET];

                if ($action instanceof SlotChangeAction
                    && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction
                    && ($itemClickedWith = $action->getTargetItem())->getTypeId() === VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::WITHER_SKELETON())->asItem()->getTypeId()
                    && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId()
                    && in_array($itemClicked->getTypeId(), $items)
                    && $itemClickedWith->getCount() === 1
                    && $itemClickedWith->getNamedTag()->getTag("masks")
                ) {
                    $maskType = $itemClickedWith->getNamedTag()->getString("masks");

                    if ($itemClicked->getNamedTag()->getTag("masks")) {
                        $event->getTransaction()->getSource()->sendMessage("§r§c§l(!) §r§cYou cannot do that!");
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                        return;
                    }

                    $event->cancel();
                    $maskColors = [
                        "purge" => "§c",
                        "headless" => "§6",
                        "pilgrim" => "§e"
                    ];
                    $color = $maskColors[$maskType] ?? "§7";

                    $lore = "§r§7§lMASK ATTACHED: " . $color . ucfirst($maskType) . " Mask";
                    $itemClicked->setLore([$lore]);
                    $transaction->getSource()->sendMessage(TextFormat::colorize("&r&a&l(!) &r&aApplied &l" . $color . ucfirst($maskType) . " Mask&r&a onto " . TextFormat::colorize($itemClicked->getName()) . "&r&a!"));
                    $transaction->getSource()->sendMessage(TextFormat::colorize("&r&7Use /removemask to unmask the helmet."));
                    $itemClicked->getNamedTag()->setString("masks", $maskType);
                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    return;
                }
            }
        }
    }

    public function onPlayerDropItemMod(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());

        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $items = [ItemTypeIds::DIAMOND_HELMET, ItemTypeIds::NETHERITE_HELMET];

                if ($action instanceof SlotChangeAction
                    && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction
                    && ($itemClickedWith = $action->getTargetItem())->getTypeId() === StringToItemParser::getInstance()->parse("ender_eye")->getTypeId()
                    && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId()
                    && in_array($itemClicked->getTypeId(), $items)
                    && $itemClickedWith->getCount() === 1
                    && $itemClickedWith->getNamedTag()->getTag("item_mod")
                ) {
                    $maskType = $itemClickedWith->getNamedTag()->getString("item_mod");

                    if ($itemClicked->getNamedTag()->getTag("item_mod")) {
                        $event->getTransaction()->getSource()->sendMessage("§r§c§l(!) §r§cYou cannot do that!");
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                        return;
                    }

                    $event->cancel();
                    $tagParts = explode('_', $maskType);
                    $tagName = '';
                    foreach ($tagParts as $part) {
                        $tagName .= TextFormat::colorize($this->getWordColor($part)) . ucfirst($part) . ' ';
                    }

                    // Separate the words with different colors in the lore
                    $lore = "§r§f§lITEM MOD: " . $tagName;
                    $itemClicked->setLore([$lore]);
                    $transaction->getSource()->sendMessage(TextFormat::colorize("&r&a&l(!) &r&aApplied &l&fITEM MOD: " . $tagName . " &r&a onto " . TextFormat::colorize($itemClicked->getName()) . "&r&a!"));
                    $transaction->getSource()->sendMessage(TextFormat::colorize("&r&7Use /removeitemmod to unmask the helmet."));
                    $itemClicked->getNamedTag()->setString("item_mod", $maskType);
                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    return;
                }
            }
        }
    }

    private function getWordColor(string $word): string
    {
        $wordColors = [
            "flaming" => TextFormat::YELLOW,
            "halo" => TextFormat::GOLD,
        ];
        return $wordColors[$word] ?? TextFormat::WHITE;
    }

    public function onTransaction(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();

        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                $inventory = $action->getInventory();
                if ($inventory instanceof ArmorInventory) {
                    $oldArmorPiece = $action->getSourceItem();
                    $newArmorPiece = $action->getTargetItem();

                }
            }
        }
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event)
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        if ($damager instanceof Player) {
            $damagerInv = $damager->getArmorInventory();
            $helmet = $damagerInv->getHelmet();
            $armor = $damagerInv->getContents();
            $itemInHand = $damager->getInventory()->getItemInHand();
            foreach ($armor as $piece) {
                if (Utils::hasTag($piece, "customarmor", "phantom")) {
                    $event->setBaseDamage($event->getBaseDamage() * 1.25);
                    if (Utils::hasTag($itemInHand, "customarmor", "phantom")) {
                        $event->setBaseDamage($event->getBaseDamage() * 1.10);
                    }
                }
            }

            if (Utils::hasTag($helmet, "masks", "purge")) {
                $event->setBaseDamage($event->getBaseDamage() + 2.5);
            }

            if (Utils::hasTag($helmet, "item_mod", "flaming_halo")) {
                $event->setBaseDamage($event->getBaseDamage() * 1.04);
            }
        }
    }

    public function onEntityDeath(EntityDeathEvent $event) {
        $entity = $event->getEntity();
        $cause = $entity->getLastDamageCause();

        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();

            if ($damager instanceof Player) {
                $damagerInv = $damager->getArmorInventory();
                $helmet = $damagerInv->getHelmet();

                if (Utils::hasTag($helmet, "masks", "pilgrim")) {
                    if ($entity instanceof Entity) {
                        $xpDrops = $event->getDrops();

                        foreach ($xpDrops as $drop) {
                            $drop->setCount(ceil($drop->getCount() * 1.25));
                        }

                        $xpDrops = $entity->getXpDropAmount();
                        $newXP = ceil($xpDrops * 1.25);
                        $event->setXpDropAmount($newXP);
                    }
                }
            }
        }
    }

    public function onJ(PlayerJoinEvent $e)
    {
        $set = ["helmet", "chestplate", "leggings", "boots", "weapon"];
        foreach ($set as $piece) {
            $e->getPlayer()->getInventory()->addItem(PhantomSet::give($piece));
        }
    }
}