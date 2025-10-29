<?php
/**
 * JWT Utility Class
 * Handles JWT token generation and validation
 */

namespace App\Utils;

class JWT
{
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/jwt.php';
    }

    /**
     * Generate JWT access token
     *
     * @param array $payload User data
     * @return string JWT token
     */
    public function generateAccessToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->config['access_token_lifetime'];

        $data = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => $this->config['issuer'],
            'aud' => $this->config['audience'],
            'data' => $payload
        ];

        return $this->encode($data);
    }

    /**
     * Generate JWT refresh token
     *
     * @param array $payload User data
     * @return string JWT token
     */
    public function generateRefreshToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->config['refresh_token_lifetime'];

        $data = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => $this->config['issuer'],
            'aud' => $this->config['audience'],
            'type' => 'refresh',
            'data' => $payload
        ];

        return $this->encode($data);
    }

    /**
     * Validate and decode JWT token
     *
     * @param string $token JWT token
     * @return array|false Decoded payload or false if invalid
     */
    public function validateToken(string $token)
    {
        try {
            $decoded = $this->decode($token);

            // Check expiration
            if (isset($decoded['exp']) && $decoded['exp'] < time()) {
                return false;
            }

            // Check issuer
            if (isset($decoded['iss']) && $decoded['iss'] !== $this->config['issuer']) {
                return false;
            }

            // Check audience
            if (isset($decoded['aud']) && $decoded['aud'] !== $this->config['audience']) {
                return false;
            }

            return $decoded;
        } catch (\Exception $e) {
            error_log("JWT Validation Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Encode JWT token
     *
     * @param array $payload Data to encode
     * @return string JWT token
     */
    private function encode(array $payload): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->config['algorithm']
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->config['secret_key'],
            true
        );

        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Decode JWT token
     *
     * @param string $token JWT token
     * @return array Decoded payload
     */
    private function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \Exception("Invalid token format");
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        // Verify signature
        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->config['secret_key'],
            true
        );

        $signatureCheck = $this->base64UrlEncode($signature);

        if ($signatureEncoded !== $signatureCheck) {
            throw new \Exception("Invalid signature");
        }

        // Decode payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

        if (!$payload) {
            throw new \Exception("Invalid payload");
        }

        return $payload;
    }

    /**
     * Base64 URL encode
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     *
     * @param string $data
     * @return string
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Get token from Authorization header
     *
     * @return string|null
     */
    public static function getBearerToken(): ?string
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );

            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
