<?php
namespace App\Controllers;

use App\Models\Player;
use App\Services\ResponseService;
use Exception;

class PlayerController {
    private $playerModel;

    public function __construct($db) {
        $this->playerModel = new Player($db);
    }

    public function getPlayer($player_id, $game_id = null) {
        try {
            $player = $this->playerModel->getPlayer($player_id, $game_id);
            if ($player) {
                ResponseService::jsonResponse($player);
            } else {
                ResponseService::jsonResponse(["message" => "Player not found"], 404);
            }
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }

    public function getAllPlayers($game_id = null) {
        try {
            $players = $this->playerModel->getAllPlayers($game_id);
            ResponseService::jsonResponse($players);
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }

    public function createPlayer($playerData) {
        try {
            $player_id = $this->playerModel->createPlayer($playerData);
            ResponseService::jsonResponse(["message" => "Player created", "player_id" => $player_id]);
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }

    public function updatePlayer($playerData, $player_id) {
        try {
            $this->playerModel->updatePlayer($playerData, $player_id);
            ResponseService::jsonResponse(["message" => "Player updated"]);
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }

    public function deletePlayer($player_id) {
        try {
            $this->playerModel->deletePlayer($player_id);
            ResponseService::jsonResponse(["message" => "Player deleted"]);
        } catch (Exception $e) {
            ResponseService::jsonResponse(["message" => $e->getMessage()], 500);
        }
    }
}
