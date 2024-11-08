-- #!sqlite
-- # { players
-- #  { initialize
CREATE TABLE IF NOT EXISTS players (
    uuid VARCHAR(36) PRIMARY KEY,
    username VARCHAR(16),
    balance INT DEFAULT 0,
    shards INT DEFAULT 0,
    kills INT DEFAULT 0,
    deaths INT DEFAULT 0,
    bounty INT DEFAULT 0,
    plevel INT DEFAULT 0,
    cooldowns TEXT,
    title TEXT,
    jackpotwins INT DEFAULT 0,
    jackpotearnings INT DEFAULT 0
    );

CREATE TABLE IF NOT EXISTS cooldowns (
    uuid VARCHAR(36),
    entry VARCHAR,
    timestamp INT,
    PRIMARY KEY (uuid, entry),
    FOREIGN KEY (uuid) REFERENCES players(uuid) ON DELETE CASCADE
    );
-- # }

-- #  { select
SELECT *
FROM players;
-- #  }

-- #  { create
-- #      :uuid string
-- #      :username string
-- #      :balance int
-- #      :shards int
-- #      :kills int
-- #      :deaths int
-- #      :bounty int
-- #      :plevel int
-- #      :cooldowns string
-- #      :title string
-- #      :jackpotwins int
-- #      :jackpotearnings int
INSERT OR REPLACE INTO players(uuid, username, balance, shards, kills, deaths, bounty, plevel, cooldowns, title, jackpotwins, jackpotearnings)
VALUES (:uuid, :username, :balance, :shards, :kills, :deaths, :bounty, :plevel, :cooldowns, :title, :jackpotwins, :jackpotearnings);
-- #  }

-- #  { update
-- #      :uuid string
-- #      :username string
-- #      :balance int
-- #      :shards int
-- #      :kills int
-- #      :deaths int
-- #      :bounty int
-- #      :plevel int
-- #      :cooldowns string
-- #      :title string
-- #      :jackpotwins int
-- #      :jackpotearnings int
UPDATE players
SET username=:username,
    balance=:balance,
    shards=:shards,
    kills=:kills,
    deaths=:deaths,
    bounty=:bounty,
    plevel=:plevel,
    cooldowns=:cooldowns,
    title=:title,
    jackpotwins=:jackpotwins,
    jackpotearnings=:jackpotearnings
WHERE uuid=:uuid;
-- #  }

-- #  { delete
-- #      :uuid string
DELETE FROM players
WHERE uuid=:uuid;
-- #  }

-- # { homes
-- #  { initialize
CREATE TABLE IF NOT EXISTS homes (
    uuid VARCHAR(36),
    home_name VARCHAR(32),
    world_name VARCHAR(32),
    x INT,
    y INT,
    z INT,
    max_homes INT DEFAULT 3,
    PRIMARY KEY (uuid, home_name)
    );
-- #  }
-- # { select
SELECT *
FROM homes;
-- # }

-- #  { create
-- #      :uuid string
-- #      :home_name string
-- #      :world_name string
-- #      :x int
-- #      :y int
-- #      :z int
-- #      :max_homes int
INSERT OR REPLACE INTO homes(uuid, home_name, world_name, x, y, z, max_homes)
VALUES (:uuid, :home_name, :world_name, :x, :y, :z, :max_homes);
-- #  }

-- #  { delete
-- #      :uuid string
-- #      :home_name string
DELETE FROM homes
WHERE uuid = :uuid AND home_name = :home_name;
-- #  }

-- #  { update
-- #      :uuid string
-- #      :max_homes int
UPDATE homes
SET max_homes = :max_homes
WHERE uuid = :uuid;
-- #   }
-- #  }
-- # }