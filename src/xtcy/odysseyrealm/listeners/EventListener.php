<?php

namespace xtcy\odysseyrealm\listeners;

use wockkinmycup\utilitycore\items\Vouchers;
use xtcy\odysseyrealm\Loader;
use xtcy\odysseyrealm\player\PlayerManager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use src\Ifera\ScoreHud\event\PlayerTagsUpdateEvent;
use src\Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use src\Ifera\ScoreHud\scoreboard\ScoreTag;
use wockkinmycup\utilitycore\utils\Utils;

class EventListener implements Listener {

    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        if (PlayerManager::getInstance()->getSession($player) === null) {
            PlayerManager::getInstance()->createSession($player);
        }
    }

    /**
     * @throws \JsonException
     */
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $session = Loader::getSessionManager()->getSession($player);
        $message = "§r§7╭━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━╮\n" .
            "§r§f                     Welcome to §6ETHEREAL§fHUB\n" .
            "§r§f        Embark on a Cosmic Journey Through the Stars!\n" .
            "§r§f           §7§o            (Odyssey Realm)\n" .
            "\n" .
            "§r§6       Server Information:\n" .
            "§r§7       • §7Account: §f" . $player->getNameTag() . "\n" .
            "§r§7       • §7Connected Players: §a" . count($player->getServer()->getOnlinePlayers()) . "\n" .
            "§r§7       • §7Webstore: §fstore.etherealhub.tk\n" .
            "§r§7       • §7Discord: §fdiscord.gg/UpJBVg2QMZ\n" .
            "\n" .
            "§r§7For additional support, join our community discord!\n" .
            "§r§7╰━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━╯";

        $player->sendMessage($message);
        PlayerManager::getInstance()->getSession($player)->setConnected(true);
        if(is_null($player) || !$player->isOnline()){
            return;
        }

        Utils::sendUpdate($player);

        $player->getInventory()->addItem(Vouchers::createMoneyNote($player, 100000));

        /*if (!empty($session->getCollection($player))) {
            $player->sendMessage(TextFormat::colorize("&r&l&6/collect &r&6You have item(s) pending!"));
        }*/
    }

    public function onPlayerDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof Player) {
                $damagerSession = Loader::getSessionManager()->getSession($damager);
                $damagerSession->addKills();
            }
        }
        $victimSession = Loader::getSessionManager()->getSession($player);
        $victimSession->addDeaths();
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();

        PlayerManager::getInstance()->getSession($player)->setConnected(false);
    }
}