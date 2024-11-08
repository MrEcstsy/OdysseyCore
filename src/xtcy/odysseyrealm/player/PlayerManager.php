<?php

declare(strict_types=1);

namespace xtcy\odysseyrealm\player;

use xtcy\odysseyrealm\Loader;
use xtcy\odysseyrealm\utils\Queries;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PlayerManager
{

    use SingletonTrait;

    /** @var OdysseyPlayer[] */
    private array $sessions; // array to fetch player data

    public function __construct(
        public Loader $plugin
    ){
        self::setInstance($this);

        $this->loadSessions();
    }

    /**
     * Store all player data in $sessions property
     *
     * @return void
     */
    private function loadSessions(): void
    {
        Loader::getDatabase()->executeSelect(Queries::PLAYERS_SELECT, [], function (array $rows): void {
            foreach ($rows as $row) {
                $this->sessions[$row["uuid"]] = new OdysseyPlayer(
                    Uuid::fromString($row["uuid"]),
                    $row["username"],
                    $row["balance"],
                    $row["shards"],
                    $row["kills"],
                    $row["deaths"],
                    $row["bounty"],
                    $row["plevel"],
                    $row["cooldowns"],
                    $row["title"],
                    $row["jackpotwins"],
                    $row["jackpotearnings"]
                   // $collection // Assign the deserialized collection data
                );
            }
        });
    }

    /**
     * Create a session
     *
     * @param Player $player
     * @return OdysseyPlayer
     * @throws \JsonException
     */
    public function createSession(Player $player): OdysseyPlayer
    {
        $args = [
            "uuid" => $player->getUniqueId()->toString(),
            "username" => $player->getName(),
            "balance" => 1000,
            "shards" => 0,
            "kills" => 0,
            "deaths" => 0,
            "bounty" => 0,
            "plevel" => 0,
            "cooldowns" => "{}",
            "title" => "",
            "jackpotwins" => 0,
            "jackpotearnings" => 0
            //"collection" => "" // Initialize collection as empty string
        ];

        Loader::getDatabase()->executeInsert(Queries::PLAYERS_CREATE, $args);

        $this->sessions[$player->getUniqueId()->toString()] = new OdysseyPlayer(
            $player->getUniqueId(),
            $args["username"],
            $args["balance"],
            $args["shards"],
            $args["kills"],
            $args["deaths"],
            $args["bounty"],
            $args["plevel"],
            $args["cooldowns"],
            $args["title"],
            $args["jackpotwins"],
            $args["jackpotearnings"]
           // [] // Assign the empty collection array
        );
        return $this->sessions[$player->getUniqueId()->toString()];
    }

    /**
     * Get session by player object
     *
     * @param Player $player
     * @return OdysseyPlayer|null
     */
    public function getSession(Player $player) : ?OdysseyPlayer
    {
        return $this->getSessionByUuid($player->getUniqueId());
    }

    /**
     * Get session by player name
     *
     * @param string $name
     * @return OdysseyPlayer|null
     */
    public function getSessionByName(string $name) : ?OdysseyPlayer
    {
        foreach ($this->sessions as $session) {
            if (strtolower($session->getUsername()) === strtolower($name)) {
                return $session;
            }
        }
        return null;
    }

    /**
     * Get session by UuidInterface
     *
     * @param UuidInterface $uuid
     * @return OdysseyPlayer|null
     */
    public function getSessionByUuid(UuidInterface $uuid) : ?OdysseyPlayer
    {
        return $this->sessions[$uuid->toString()] ?? null;
    }

    public function destroySession(OdysseyPlayer $session) : void
    {
        Loader::getDatabase()->executeChange(Queries::PLAYERS_DELETE, ["uuid", $session->getUuid()->toString()]);

        # Remove session from the array
        unset($this->sessions[$session->getUuid()->toString()]);
    }

    public function getSessions() : array
    {
        return $this->sessions;
    }

}