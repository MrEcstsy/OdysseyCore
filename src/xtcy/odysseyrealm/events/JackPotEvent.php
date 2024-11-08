<?php

namespace xtcy\odysseyrealm\events;

use xtcy\odysseyrealm\Loader;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\XpLevelUpSound;
use wockkinmycup\utilitycore\utils\Utils;

class JackPotEvent extends EventBase
{
    /** @var int  */
    public int $prizepool = 0;

    /** @var int */
    public int $time = 30;

    /** @var JackPotEvent */
    public static JackPotEvent $instance;

    /** @var array $players */
    public array $players = [];

    /** @var int */
    public int $default = 0;

    public EventManager $eventManager;

    /**
     * @param EventManager $eventManager
     * @param $eventName
     */
    public function __construct(EventManager $eventManager, public $eventName)
    {
        self::$instance = $this;
        $this->eventManager = $eventManager;
        $this->setPrizePool(0);
        parent::__construct($eventManager, "JackPot");
    }

    /**
     * @return JackPotEvent|null
     */
    public static function getInstance(): ?JackPotEvent
    {
        return self::$instance;
    }

    public function tick(): void
    {
        --$this->time;
        if ($this->time <= 0) {
            if (count($this->players) <= 0) {
                Server::getInstance()->broadcastMessage("§r§c§l(!) §r§cFailed to draw a winner, could not find any ticket(s).");
                $this->time = 3600;
            }
            if (count($this->players) >= 1 && $this->time <= 0) {
                $this->drawWinner();
                $this->time = 3600;
            }
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function formatStats(Player $player): void
    {
        $session = Loader::getInstance()->getSessionManager()->getSession($player);
        $mytickets = $this->getTickets($player->getName());
        $wins = $session->getJackPotWins();
        $player->sendMessage(TextFormat::colorize("&r&l&6Odyssey Jackpot Stats &r&7({$player->getName()})"));
        $player->sendMessage(TextFormat::colorize("   &r&l&eTotal Winnings: &r&6$" . number_format($session->getJackpotEarnings())));
        $player->sendMessage(TextFormat::colorize("   &r&l&eTotal Tickets Purchased: &r&6" . number_format($mytickets)));
        $player->sendMessage(TextFormat::colorize("   &r&l&eTotal Wins: &r&6" . number_format($wins)));
    }

    /**
     * @param Player $player
     * @return void
     */
    public function formatMessage(Player $player): void
    {
        $percentage = 0;
        $value = number_format($this->getPrizePool());
        $mytickets = $this->getTickets($player->getName());
        $tickets = [];
        foreach ($this->players as $players => $amount) {
            for ($i = 0; $i < $amount; $i++) {
                $tickets[] = $player;
            }
        }
        $ticketz = number_format(count($tickets));
        if ($mytickets >= 1) $percentage = ($mytickets / count($this->players)) * 100;

        $player->sendMessage(TextFormat::colorize("&r&l&6Odyssey Jackpot"));
        $player->sendMessage(TextFormat::colorize("   &r&l&eJackpot Value: &r&6$" . $value . " &r&7(-10% tax)"));
        $player->sendMessage(TextFormat::colorize("   &r&l&eTickets Sold: &r&6" . $ticketz));
        $player->sendMessage(TextFormat::colorize("   &r&l&eYour Tickets: &r&a" . number_format($mytickets) . " &r&7(" . $percentage . "%)"));
        $player->sendMessage(" ");
        $player->sendMessage(TextFormat::colorize("&r&l&e(!) &r&eNext winner in " . Utils::translateTime($this->time)));
    }

    public function drawWinner(): void
    {
        $tickets = [];
        foreach ($this->players as $player => $amount) {
            for ($i = 0; $i < $amount; $i++) {
                $tickets[] = $player;
            }
        }
        shuffle($tickets);
        $player = $tickets[array_rand($tickets)];
        $winning = Server::getInstance()->getPlayerExact($player);

        if (!$winning instanceof Player) {
            $jackpotAmount = $this->getPrizePool();
            $tax = ceil($jackpotAmount * 0.1);
            $prize = $jackpotAmount - $tax;
            Loader::getSessionManager()->getSession($player)->addBalance($prize);

            $pool = number_format($prize);
            $mytickets = $this->getTickets($player);
            $ticketz = number_format(count($tickets));
            Server::getInstance()->broadcastMessage("§r§a§l(!) §r§a$player has won the /jackpot and received \n §r§2$" . $pool . "§r§a! \n §r§aThey purchased $mytickets ticket(s) \n §r§aout of the $ticketz ticket(s) sold!");

            Loader::getSessionManager()->getSession($player)->addJackpotWins();
            Loader::getSessionManager()->getSession($player)->addJackPotEarnings($prize);

            $this->setPrizePool(0);
            $this->players = array();
            return;
        }

        if ($winning instanceof Player && $winning->isOnline()) {
            $jackpotAmount = $this->getPrizePool();
            $tax = ceil($jackpotAmount * 0.1);
            $prize = $jackpotAmount - $tax;

            $session = Loader::getSessionManager()->getSession($winning);
            $session->addBalance($prize);
            $session->addJackpotEarnings($prize);
            $session->addJackpotWins();

            $pool = number_format($prize);
            $mytickets = number_format($this->getTickets($player));
            $ticketz = number_format(count($tickets));
            Server::getInstance()->broadcastMessage("§r§a§l(!) §r§a$player has won the /jackpot and received \n §r§2$" . $pool . "§r§a! \n §r§aThey purchased $mytickets ticket(s) \n §r§aout of the $ticketz ticket(s) sold!");

            $winning->sendMessage("§r§a§l+ $" . number_format($prize));
            $winning->getWorld()->addSound($winning->getLocation(), new XpLevelUpSound(1000));

            $this->players = array();
            $this->setPrizePool(0);
        }
    }

    /**
     * @param $player
     * @return int
     */
    public function getTickets($player): int{
        if(!isset($this->players[$player])) return 0;
        return (int) $this->players[$player];
    }

    /**
     * @param Player $player
     * @param int $amount
     * @param int $price
     */
    public function addTickets(Player $player, int $amount = 1, int $price = 1): void{
        $session = Loader::getSessionManager()->getSession($player);
        $currentTickets = $this->players[$player->getName()] ?? 0;

        if($session->getBalance() < $price){
            $player->sendMessage("§r§c§l(!) §r§cYou don't have the required balance to purchase tickets.");
            return;
        }

        if ($currentTickets >= 1000) {
            $player->sendMessage("§r§c§l(!) §r§cYou already have 1000 tickets. You cannot buy more.");
            return;
        }

        if ($currentTickets + $amount > 1000) {
            $amount = 1000 - $currentTickets; // Adjust the purchase amount to reach the 1000 limit
        }

        $tickets = $this->players[$player->getName()] ?? 0;
        $session->subtractBalance($price);
        $player->sendMessage("§r§a§l(!) §r§aYou successfully purchased x$amount Ticket(s).");
        $player->getWorld()->addSound($player->getLocation(),new XpLevelUpSound(1000));
        $this->setPrizePool($this->getPrizePool() + $price);
        $this->players[$player->getName()] = $tickets + $amount;
    }

    /**
     * @param int $prize
     */
    public function setPrizePool(int $prize): void{
        $this->prizepool = $prize;
    }

    /**
     * @return int
     */
    public function getPrizePool(): int{
        return $this->prizepool;
    }
}