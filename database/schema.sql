-- Drop player_statistics table first because it has foreign key dependencies on players and games
DROP TABLE IF EXISTS player_statistics;

-- Drop games and players
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS players;

-- Create players table
CREATE TABLE players (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    position VARCHAR(10) NOT NULL,
    height VARCHAR(10) NOT NULL,
    weight INT NOT NULL,
    age INT NOT NULL,
    experience INT,
    college VARCHAR(100),
    image VARCHAR(200)
);

-- Create games table
CREATE TABLE games (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    game_date DATE NOT NULL,
    opponent VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    team_score INT NOT NULL,
    opponent_score INT NOT NULL,
    result CHAR(1) NOT NULL,
    season VARCHAR(9) NOT NULL DEFAULT '2023-2024', -- Default value for season
    opponent_image VARCHAR(200)
);

-- Create player_statistics table
CREATE TABLE player_statistics (
    stats_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    game_id INT,
    season VARCHAR(9) NOT NULL DEFAULT '2023-2024', -- Default value for season
    season_rank INT,
    game_rank INT,
    FOREIGN KEY (player_id) REFERENCES players(player_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(game_id) ON DELETE SET NULL ON UPDATE CASCADE
);
