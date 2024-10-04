<?php
namespace App\Controllers;

use App\Models\Game;
use App\Services\ResponseService;
use Exception;

class GameController {
    private $gameModel;

    public function __construct($db) {
        $this->gameModel = new Game($db);
    }

    public function getAllGames() {
        try {
            $games = $this->gameModel->getAllGames();
            ResponseService::jsonResponse($games);
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }

    public function createGame($gameData) {
        try {
            $this->gameModel->createGame($gameData);
            ResponseService::jsonResponse(["message" => "Game created successfully"]);
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }

    public function updateGame($gameData, $game_id) {
        try {
            $this->gameModel->updateGame($gameData, $game_id);
            ResponseService::jsonResponse(["message" => "Game updated successfully"]);
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }

    public function deleteGame($game_id) {
        try {
            $this->gameModel->deleteGame($game_id);
            ResponseService::jsonResponse(["message" => "Game deleted successfully"]);
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }
}
