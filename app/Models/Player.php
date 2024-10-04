<?php
namespace App\Models;

use PDO;
use Exception;
use App\Services\ResponseService;

class Player {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db;
    }

    public function getPlayer($player_id, $game_id = null) {
        try {
            $query = "
                SELECT p.*, ps.game_id, ps.season_rank, ps.game_rank 
                FROM players p 
                LEFT JOIN player_statistics ps ON p.player_id = ps.player_id 
                WHERE p.player_id = :player_id";

            if ($game_id) {
                $query .= " AND ps.game_id = :game_id";
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':player_id', $player_id);
            if ($game_id) {
                $stmt->bindParam(':game_id', $game_id);
            }

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching player: " . $e->getMessage());
        }
    }

    public function getAllPlayers($game_id = null) {
        try {
            // Base query to get all players and their statistics
            $query = "
                SELECT p.*, ps.game_id, ps.season_rank, ps.game_rank 
                FROM players p 
                LEFT JOIN player_statistics ps ON p.player_id = ps.player_id";
            
            // If game_id is provided, add the filter
            if ($game_id) {
                $query .= " WHERE ps.game_id = :game_id";
            }

            $stmt = $this->pdo->prepare($query);

            // Bind the game_id parameter if provided
            if ($game_id) {
                $stmt->bindParam(':game_id', $game_id);
            }

            // Execute the query
            $stmt->execute();

            // Fetch all players with their statistics
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Handle any exceptions and rethrow them with context
            throw new Exception("Error fetching players: " . $e->getMessage());
        }
    }

    public function createPlayer($playerData) {
        try {
            // Handle image upload
            $imagePath = null; // Default null if no image is uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadsDir = 'uploads/';
                $fileName = basename($_FILES['image']['name']);
                $targetFilePath = $uploadsDir . $fileName;

                // Move the uploaded file to the public/uploads directory
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $imagePath = $targetFilePath;
                } else {
                    // Handle file upload error
                    ResponseService::jsonResponse(["message" => "Error uploading image"], 500);
                }
            }

            // Prepare the SQL query to insert a new player
            $stmt = $this->pdo->prepare("
                INSERT INTO players (first_name, last_name, position, height, weight, age, experience, college, image) 
                VALUES (:first_name, :last_name, :position, :height, :weight, :age, :experience, :college, :image)
            ");

            // Execute the query with the player data
            $stmt->execute([
                ':first_name' => $playerData['first_name'],
                ':last_name' => $playerData['last_name'],
                ':position' => $playerData['position'],
                ':height' => $playerData['height'],
                ':weight' => $playerData['weight'],
                ':age' => $playerData['age'],
                ':experience' => $playerData['experience'],
                ':college' => $playerData['college'],
                ':image' => $imagePath
            ]);

            // Get the last inserted player ID
            $player_id = $this->pdo->lastInsertId();

            // Now insert into player_statistics
            $stmt = $this->pdo->prepare("
                INSERT INTO player_statistics (player_id, game_id, season_rank, game_rank) 
                VALUES (:player_id, :game_id, :season_rank, :game_rank)
            ");

            // Bind and execute statistics parameters
            $stmt->execute([
                ':player_id' => $player_id,
                ':game_id' => $playerData['game_id'],
                ':season_rank' => $playerData['season_rank'],
                ':game_rank' => $playerData['game_rank']
            ]);

            // If successful, return a success message
            ResponseService::jsonResponse(["message" => "Player and statistics added successfully"]);
        } catch (Exception $e) {
            // Handle any exceptions and return an error message
            ResponseService::jsonResponse(["message" => "Error creating player: " . $e->getMessage()], 500);
        }
    }

    public function updatePlayer($playerData, $player_id) {
        try {
            // Access form data using $_POST
            $first_name = $_POST['first_name'] ?? null;
            $last_name = $_POST['last_name'] ?? null;
            $position = $_POST['position'] ?? null;
            $height = $_POST['height'] ?? null;
            $weight = $_POST['weight'] ?? null;
            $age = $_POST['age'] ?? null;
            $experience = $_POST['experience'] ?? null;
            $college = $_POST['college'] ?? null;
            $game_id = $_POST['game_id'] ?? null;
            $season_rank = $_POST['season_rank'] ?? null;
            $game_rank = $_POST['game_rank'] ?? null;
    
            // Handle image upload using $_FILES
            $imagePath = null;  // Default null if no image is uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadsDir = 'uploads/';
                $fileName = basename($_FILES['image']['name']);
                $targetFilePath = $uploadsDir . $fileName;
    
                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $imagePath = $targetFilePath;
                } else {
                    // Handle file upload error
                    ResponseService::jsonResponse(["message" => "Error uploading image"], 500);
                }
            } else {
                // Retain the old image if no new image is uploaded
                $stmt = $this->pdo->prepare("SELECT image FROM players WHERE player_id = :player_id");
                $stmt->execute([':player_id' => $player_id]);
                $imagePath = $stmt->fetchColumn();
            }
    
            // Prepare the SQL query to update the player data
            $stmt = $this->pdo->prepare("
                UPDATE players 
                SET first_name = :first_name, last_name = :last_name, 
                    position = :position, height = :height, weight = :weight, 
                    age = :age, experience = :experience, college = :college, 
                    image = :image 
                WHERE player_id = :player_id
            ");
    
            // Execute the update query
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':position' => $position,
                ':height' => $height,
                ':weight' => $weight,
                ':age' => $age,
                ':experience' => $experience,
                ':college' => $college,
                ':image' => $imagePath,
                ':player_id' => $player_id
            ]);
    
            // Now update player statistics
            $stmt = $this->pdo->prepare("
                UPDATE player_statistics 
                SET game_id = :game_id, season_rank = :season_rank, game_rank = :game_rank 
                WHERE player_id = :player_id
            ");
    
            // Bind and execute statistics parameters
            $stmt->execute([
                ':player_id' => $player_id,
                ':game_id' => $game_id,
                ':season_rank' => $season_rank,
                ':game_rank' => $game_rank
            ]);
    
            // If successful, return a success message
            ResponseService::jsonResponse(["message" => "Player and statistics updated successfully"]);
        } catch (Exception $e) {
            // Handle any exceptions and return an error message
            ResponseService::jsonResponse(["message" => "Error updating player: " . $e->getMessage()], 500);
        }
    }
    
    

    public function deletePlayer($player_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM player_statistics WHERE player_id = :player_id");
            $stmt->execute([':player_id' => $player_id]);

            $stmt = $this->pdo->prepare("DELETE FROM players WHERE player_id = :player_id");
            $stmt->execute([':player_id' => $player_id]);
        } catch (Exception $e) {
            throw new Exception("Error deleting player: " . $e->getMessage());
        }
    }
}
