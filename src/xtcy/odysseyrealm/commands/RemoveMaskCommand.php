<?php

namespace xtcy\odysseyrealm\commands;

use CortexPE\Commando\BaseCommand;
use xtcy\odysseyrealm\items\CustomItems;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class RemoveMaskCommand extends BaseCommand
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
            return; // Command can only be used by players
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage("You don't have permission to use this command.");
            return;
        }

        $item = $sender->getInventory()->getItemInHand();
        $ids = [VanillaItems::LEATHER_CAP()->getTypeId(), VanillaItems::IRON_HELMET()->getTypeId(), VanillaItems::CHAINMAIL_HELMET()->getTypeId(), VanillaItems::GOLDEN_HELMET()->getTypeId(), VanillaItems::DIAMOND_HELMET()->getTypeId()];
        if (!in_array($item->getTypeId(), $ids, true) || $item->getNamedTag()->getString("masks", "") === "") {
            $sender->sendMessage(TextFormat::RED . "Please hold a helmet with a mask applied to it!");
            return;
        }

        $mask = $item->getNamedTag()->getString("masks");
        if ($mask === null) {
            $sender->sendMessage(TextFormat::RED . "Undefined Mask!");
            return;
        }
        $lore = $item->getLore();
        if ($mask === "headless") {
            if (isset($lore[array_search("§r§7§lMASK ATTACHED: §6Headless Mask", $lore)])) {
                unset($lore[array_search("§r§7§lMASK ATTACHED: §6Headless Mask", $lore)]);
                $sender->getInventory()->addItem(CustomItems::getMask("headless"));
            }
        } elseif ($mask === "purge") {
            if (isset($lore[array_search("§r§7§lMASK ATTACHED: §cPurge Mask", $lore)])) {
                unset($lore[array_search("§r§7§lMASK ATTACHED: §cPurge Mask", $lore)]);
                $sender->getInventory()->addItem(CustomItems::getMask("purge"));
            }
        } elseif ($mask === "pilgrim") {
            if (isset($lore[array_search("§r§7§lMASK ATTACHED: §ePilgrim Mask", $lore)])) {
                unset($lore[array_search("§r§7§lMASK ATTACHED: §ePilgrim Mask", $lore)]);
                $sender->getInventory()->addItem(CustomItems::getMask("pilgrim"));
            }
        }

        $maskColors = [
            "purge" => "§c",
            "headless" => "§6",
            "pilgrim" => "§e"
        ];
        $color = $maskColors[$mask] ?? "§7";

        $item->getNamedTag()->removeTag("masks");
        $item->setLore($lore);
        $sender->getInventory()->setItemInHand($item);
        $sender->sendMessage(TextFormat::colorize("&r&a&l(!) &r&aRemoved &l" . $color . ucfirst($mask) . " Mask&r&a from " . TextFormat::colorize($item->getName()) . "&r&a!"));
    }
}