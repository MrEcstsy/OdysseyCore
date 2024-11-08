<?php

namespace xtcy\odysseyrealm;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\tag\Tag;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use muqsit\invmenu\InvMenuHandler;
use xtcy\odysseyrealm\addons\AddonManager;
use xtcy\odysseyrealm\addons\scorehud\TagResolveListener;
use xtcy\odysseyrealm\commands\balance\AddBalanceCommand;
use xtcy\odysseyrealm\commands\balance\BalanceCommand;
use xtcy\odysseyrealm\commands\balance\PayCommand;
use xtcy\odysseyrealm\commands\balance\RemoveBalanceCommand;
use xtcy\odysseyrealm\commands\balance\SetBalanceCommand;
use xtcy\odysseyrealm\commands\BalanceTopCommand;
use xtcy\odysseyrealm\commands\FeedCommand;
use xtcy\odysseyrealm\commands\HealCommand;
use xtcy\odysseyrealm\commands\JackPotCommand;
use xtcy\odysseyrealm\commands\RemoveMaskCommand;
use xtcy\odysseyrealm\commands\shards\AddShardCommand;
use xtcy\odysseyrealm\commands\shards\RemoveShardsCommand;
use xtcy\odysseyrealm\commands\shards\SetShardsCommand;
use xtcy\odysseyrealm\commands\shards\ShardCommand;
use xtcy\odysseyrealm\commands\staff\GameModeCommand;
use xtcy\odysseyrealm\commands\TitlesCommand;
use xtcy\odysseyrealm\commands\XPShopCommand;
use xtcy\odysseyrealm\items\CustomItems;
use xtcy\odysseyrealm\listeners\EventListener;
use xtcy\odysseyrealm\listeners\ItemListeners;
use xtcy\odysseyrealm\player\homes\HomeManager;
use xtcy\odysseyrealm\player\PlayerManager;
use xtcy\odysseyrealm\utils\Queries;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\addons\customArmor\listener\CustomArmorListener;
use wockkinmycup\utilitycore\commands\ExpCommand;

class Loader extends PluginBase {

    use SingletonTrait;

    public static DataConnector $connector;

    public static PlayerManager $manager;

    public static HomeManager $homeManager;

    /** @var AddonManager|null */
    private ?AddonManager $addonManager;


    public const NO_PERMISSION = TextFormat::RED . "You do not have permission to use this command.";

    public const MAX_PLAYER_LEVEL = 10;

    /** @var Enchantment */
    public static Enchantment $ench;

    public function onLoad(): void
    {
        self::setInstance($this);
    }

    public function onEnable(): void
    {
        $this->init();
    }

    public function onDisable(): void
    {
        if (isset($this->connector)) {
            $this->connector->close();
        }
    }

    public function registerCommands() {
        $this->getServer()->getCommandMap()->registerAll("odyssey_realm", [
            new XPShopCommand($this, "xpshop", "Open the xpshop menu", ["xps"]),
            new ExpCommand($this, "exp", "View your experience", ["xp"]),
            new BalanceCommand($this, "balance", "View your or another players balance", ["bal"]),
            new SetBalanceCommand($this, "setbalance", "Set your or another players balance", ["setbal"]),
            new SetShardsCommand($this, "setshards", "Set your or another players shards", ["setsh"]),
            new RemoveShardsCommand($this, "removeshards", "Remove shards from a player", ["removesh", "takesh", "subtractsh", "rmsh"]),
            new RemoveBalanceCommand($this, "removebalance", "Remove balance from a player", ["removebal", "takebal", "subtractbal", "rmbal"]),
            new AddBalanceCommand($this, "addbalance", "Add balance to a player", ["addbal"]),
            new ShardCommand($this, "shards", "View your or another players shards", ["shard"]),
            new AddShardCommand($this, "addshards", "Add shards to a player", ["addshards"]),
            new FeedCommand($this, "feed", "Replenish your hunger"),
            new HealCommand($this, "heal", "Replenish your health"),
            new PayCommand($this, "pay", "Pay money to another player"),
            new TitlesCommand($this, "title", "Manage unlocked titles", ["titles"]),
            new JackPotCommand($this, "jackpot", "View current jackpot", ["jp"]),
            new BalanceTopCommand($this, "balancetop", "View the top balances on this server", ["baltop"]),
            new GameModeCommand($this, "gamemode", "Change the player to a specific gamemode", ["gm"]),
            new RemoveMaskCommand($this, "removemask", 'Remove a mask off your helmet')
            //new CollectCommand($this, "collect", "Collect your items")
        ]);
    }

    public function init() {
        $settings = [
            "type" => "sqlite",
            "sqlite" => ["file" => "sqlite.sql"],
            "worker-limit" => 2
        ];

        self::$connector = libasynql::create($this, $settings, ["sqlite" => "sqlite.sql"]);
        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->executeGeneric(Queries::HOMES_INIT);

        self::$connector->waitAll();

        self::$manager = new PlayerManager($this);
        self::$homeManager = new HomeManager($this, 3);

        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("title"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("gamemode"));

        $this->saveDefaultConfig();
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
        $this->saveDefaultConfig();
        $this->registerCommands();
        $this->getServer()->getPluginManager()->registerEvents(new ItemListeners(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new CustomArmorListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new TagResolveListener($this), $this);
        self::$ench = new Enchantment("", Rarity::COMMON, ItemFlags::NONE, ItemFlags::NONE, 1);
        EnchantmentIdMap::getInstance()->register(999, self::$ench);

        RankSystem::getInstance()->getTagManager()->registerTag(new Tag("level", static function(Session $session) : string {
            $level = Loader::getSessionManager()->getSession($session->getPlayer());
            return (string) $level->getLevel();
        }));
        RankSystem::getInstance()->getTagManager()->registerTag(new Tag("title", static function(Session $session) : string {
            $title = Loader::getSessionManager()->getSession($session->getPlayer());
            return $title->getTitle();
        }));

        $this->addonManager = new AddonManager($this);
        new CustomItems($this);
    }

    public static function getDatabase() : DataConnector
    {
        return self::$connector;
    }

    public static function getSessionManager() : PlayerManager
    {
        return self::$manager;
    }

    public function getPlayerManager() : PlayerManager
    {
        return self::$manager;
    }

    public static function getHomeManager() : HomeManager {
        return self::$homeManager;
    }

    /**
     * @return AddonManager|null
     */
    public function getAddonManager(): ?AddonManager
    {
        return $this->addonManager;
    }

}