<?php

header("Access-Control-Allow-Origin: *"); // Allow all origins (you can specify specific domains instead of '*')
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allow these HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers

// If this is an OPTIONS request (preflight), return a 200 status code and exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require '../vendor/autoload.php';

use App\Config\Database;
use App\Services\JWTService;
use App\Services\ResponseService;
use App\Controllers\PlayerController;
use App\Controllers\GameController;

// Initialize Database and JWTService
$database = new Database();
$db = $database->getConnection();
$jwtService = new JWTService();

// Get the endpoint from the query string
$endpoint = $_GET['endpoint'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

// Handle JWT token generation
if ($endpoint === 'token' && $method === 'POST') {
    $payload = [
        "iss" => "localhost",
        "iat" => time(),
        "exp" => time() + 3600,  // Token expires in 1 hour
        "sub" => "49ers_service"
    ];

    // Generate the JWT token using JWTService
    $token = $jwtService->generateToken($payload);
    
    // Respond with the token
    ResponseService::jsonResponse(["token" => $token]);
    exit();
}

// Retrieve the Authorization header and validate the JWT token for other endpoints
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';
$jwt = str_replace('Bearer ', '', $authHeader);

$decoded = $jwtService->validateToken($jwt);
if (!$decoded) {
    ResponseService::jsonResponse(["message" => "Invalid JWT Token"], 401);
}

// Initialize the appropriate controllers
$playerController = new PlayerController($db);
$gameController = new GameController($db);

// Players Endpoint
if ($endpoint === 'players') {
    if ($method === 'GET') {
        // Retrieve all players or a specific player if player_id is provided
        $player_id = $_GET['player_id'] ?? null;
        $game_id = $_GET['game_id'] ?? null;
        
        if ($player_id) {
            // Retrieve a specific player
            $playerController->getPlayer($player_id, $game_id);
        } else {
            // Retrieve all players
            $playerController->getAllPlayers($game_id);
        }
    } elseif ($method === 'POST') {
        // Create a new player
        $playerData = $_POST;
        $playerController->createPlayer($playerData);
    } elseif ($method === 'PUT') {
        // Update an existing player
        parse_str(file_get_contents("php://input"), $_PUT);
        $player_id = $_PUT['player_id'] ?? null;
        $playerController->updatePlayer($_PUT, $player_id);
    } elseif ($method === 'DELETE') {
        // Delete a player
        parse_str(file_get_contents("php://input"), $_DELETE);
        $player_id = $_DELETE['player_id'] ?? null;
        $playerController->deletePlayer($player_id);
    } else {
        ResponseService::jsonResponse(["message" => "Method not allowed"], 405);
    }
}

// Games Endpoint
elseif ($endpoint === 'games') {
    if ($method === 'GET') {
        // Retrieve games
        $gameController->getAllGames();
    } elseif ($method === 'POST') {
        // Create a new game
        $gameData = $_POST;
        $gameController->createGame($gameData);
    } elseif ($method === 'PUT') {
        // Update an existing game
        parse_str(file_get_contents("php://input"), $_PUT);
        $game_id = $_PUT['game_id'] ?? null;
        $gameController->updateGame($_PUT, $game_id);
    } elseif ($method === 'DELETE') {
        // Delete a game
        parse_str(file_get_contents("php://input"), $_DELETE);
        $game_id = $_DELETE['game_id'] ?? null;
        $gameController->deleteGame($game_id);
    } else {
        ResponseService::jsonResponse(["message" => "Method not allowed"], 405);
    }
} else {
    ResponseService::jsonResponse(["message" => "Endpoint not found"], 404);
}
