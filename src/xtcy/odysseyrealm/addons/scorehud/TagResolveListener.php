<?php

namespace xtcy\odysseyrealm\addons\scorehud;

use Ifera\ScoreHud\event\TagsResolveEvent;
use xtcy\odysseyrealm\Loader;
use pocketmine\event\Listener;

class TagResolveListener implements Listener
{

    private Loader $plugin;

    public function __construct(Loader $plugin)
    {
            $this->plugin = $plugin;
    }

    public function onTagResolve(TagsResolveEvent $event)
    {
        $player = $event->getPlayer();
        $tag = $event->getTag();
        $tags = explode(".", $tag->getName(), 2);
        $value = "";

        if ($tags[0] !== "odyssey" || count($tags) < 2) {
            return;
        }

        switch ($tags[1]) {
            case "balance":
                $value = $this->plugin->getSessionManager()->getSession($player)->getBalance();
                break;
            case "kills":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getKills();
                break;
            case "deaths":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getDeaths();
                break;
            case "shards":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getShards();
                break;
            case "level":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getLevel();
                break;
            case "xp":
                $value = $player->getXpManager()->getCurrentTotalXp();
                break;
        }

        $tag->setValue((string) $value);
    }
}