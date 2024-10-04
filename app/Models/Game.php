<?php
namespace App\Models;

use PDO;
use Exception;
use App\Services\ResponseService;

class Game {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db;
    }

    public function getAllGames() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM games ORDER BY game_date DESC LIMIT 10");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching games: " . $e->getMessage());
        }
    }

    public function createGame($gameData) {
        try {
            // Handle the optional opponent image upload
            $opponentImagePath = null; // Set default value to null
            if (isset($_FILES['opponent_image']) && $_FILES['opponent_image']['error'] === 0) {
                $uploadsDir = 'uploads/';
                $fileName = basename($_FILES['opponent_image']['name']);
                $targetFilePath = $uploadsDir . $fileName;
    
                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($_FILES['opponent_image']['tmp_name'], $targetFilePath)) {
                    $opponentImagePath = $targetFilePath;
                }
            }
    
            // Insert game data into the database
            $stmt = $this->pdo->prepare("
                INSERT INTO games (game_date, opponent, location, team_score, opponent_score, result, season, opponent_image) 
                VALUES (:game_date, :opponent, :location, :team_score, :opponent_score, :result, :season, :opponent_image)
            ");
    
            // Bind the parameters correctly
            $stmt->execute([
                ':game_date' => $gameData['game_date'],
                ':opponent' => $gameData['opponent'],
                ':location' => $gameData['location'],
                ':team_score' => $gameData['team_score'],
                ':opponent_score' => $gameData['opponent_score'],
                ':result' => $gameData['result'],
                ':season' => $gameData['season'],
                ':opponent_image' => $opponentImagePath
            ]);
    
            // If the game was successfully created, return a success message
            ResponseService::jsonResponse(["message" => "Game created successfully"]);
        } catch (Exception $e) {
            // Handle any exception that occurs and return an error message
            ResponseService::jsonResponse(["message" => "Error creating game: " . $e->getMessage()], 500);
        }
    }

    public function updateGame($gameData, $game_id) {
        try {
            // Handle the optional opponent image upload
            $opponentImagePath = null; // Default value is null
    
            // Check if a new image is uploaded
            if (isset($_FILES['opponent_image']) && $_FILES['opponent_image']['error'] === 0) {
                $uploadsDir = 'uploads/';
                $fileName = basename($_FILES['opponent_image']['name']);
                $targetFilePath = $uploadsDir . $fileName;
    
                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($_FILES['opponent_image']['tmp_name'], $targetFilePath)) {
                    $opponentImagePath = $targetFilePath;
                } else {
                    // Handle file upload error
                    ResponseService::jsonResponse(["message" => "Error uploading opponent image"], 500);
                }
            } else {
                // If no new image is uploaded, keep the old image
                $stmt = $this->pdo->prepare("SELECT opponent_image FROM games WHERE game_id = :game_id");
                $stmt->execute([':game_id' => $game_id]);
                $opponentImagePath = $stmt->fetchColumn();
            }
    
            // Prepare the SQL query to update the game data
            $stmt = $this->pdo->prepare("
                UPDATE games 
                SET game_date = :game_date, opponent = :opponent, location = :location, 
                    team_score = :team_score, opponent_score = :opponent_score, 
                    result = :result, season = :season, opponent_image = :opponent_image 
                WHERE game_id = :game_id
            ");
    
            // Execute the update query with the new data
            $stmt->execute([
                ':game_date' => $_POST['game_date'],
                ':opponent' => $_POST['opponent'],
                ':location' => $_POST['location'],
                ':team_score' => $_POST['team_score'],
                ':opponent_score' => $_POST['opponent_score'],
                ':result' => $_POST['result'],
                ':season' => $_POST['season'],
                ':opponent_image' => $opponentImagePath,
                ':game_id' => $game_id
            ]);
    
            // If successful, return a success message
            ResponseService::jsonResponse(["message" => "Game updated successfully"]);
        } catch (Exception $e) {
            // Handle any exceptions and return an error message
            ResponseService::jsonResponse(["message" => "Error updating game: " . $e->getMessage()], 500);
        }
    }
    

    public function deleteGame($game_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM games WHERE game_id = :game_id");
            $stmt->execute([':game_id' => $game_id]);
        } catch (Exception $e) {
            throw new Exception("Error deleting game: " . $e->getMessage());
        }
    }
}
