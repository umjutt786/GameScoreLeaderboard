<?php
namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTService {
    private $secretKey = "uWi1pJQNoaG+5hkVTU4bFEMCTtnjXJtgLCWaX+3IKkM=";

    public function generateToken($payload) {
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken($jwt) {
        try {
            return JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
        } catch (Exception $e) {
            return false;
        }
    }
}
