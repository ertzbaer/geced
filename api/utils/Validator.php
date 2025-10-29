<?php
/**
 * Input Validation Utility
 * Validates and sanitizes user input
 */

namespace App\Utils;

class Validator
{
    private $errors = [];

    /**
     * Validate email format
     *
     * @param string $email
     * @param string $fieldName
     * @return bool
     */
    public function email(string $email, string $fieldName = 'email'): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$fieldName] = "Invalid email format";
            return false;
        }
        return true;
    }

    /**
     * Validate required field
     *
     * @param mixed $value
     * @param string $fieldName
     * @return bool
     */
    public function required($value, string $fieldName): bool
    {
        if (empty($value) && $value !== '0') {
            $this->errors[$fieldName] = "$fieldName is required";
            return false;
        }
        return true;
    }

    /**
     * Validate minimum length
     *
     * @param string $value
     * @param int $length
     * @param string $fieldName
     * @return bool
     */
    public function minLength(string $value, int $length, string $fieldName): bool
    {
        if (strlen($value) < $length) {
            $this->errors[$fieldName] = "$fieldName must be at least $length characters";
            return false;
        }
        return true;
    }

    /**
     * Validate maximum length
     *
     * @param string $value
     * @param int $length
     * @param string $fieldName
     * @return bool
     */
    public function maxLength(string $value, int $length, string $fieldName): bool
    {
        if (strlen($value) > $length) {
            $this->errors[$fieldName] = "$fieldName must not exceed $length characters";
            return false;
        }
        return true;
    }

    /**
     * Validate phone number format
     *
     * @param string $phone
     * @param string $fieldName
     * @return bool
     */
    public function phone(string $phone, string $fieldName = 'phone'): bool
    {
        // Basic phone validation (allows international format)
        if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $phone)) {
            $this->errors[$fieldName] = "Invalid phone format";
            return false;
        }
        return true;
    }

    /**
     * Validate integer
     *
     * @param mixed $value
     * @param string $fieldName
     * @return bool
     */
    public function integer($value, string $fieldName): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_INT) && $value !== 0) {
            $this->errors[$fieldName] = "$fieldName must be an integer";
            return false;
        }
        return true;
    }

    /**
     * Validate value in array
     *
     * @param mixed $value
     * @param array $allowed
     * @param string $fieldName
     * @return bool
     */
    public function inArray($value, array $allowed, string $fieldName): bool
    {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$fieldName] = "$fieldName must be one of: " . implode(', ', $allowed);
            return false;
        }
        return true;
    }

    /**
     * Validate range (min-max)
     *
     * @param mixed $value
     * @param int $min
     * @param int $max
     * @param string $fieldName
     * @return bool
     */
    public function range($value, int $min, int $max, string $fieldName): bool
    {
        if ($value < $min || $value > $max) {
            $this->errors[$fieldName] = "$fieldName must be between $min and $max";
            return false;
        }
        return true;
    }

    /**
     * Validate date format
     *
     * @param string $date
     * @param string $format
     * @param string $fieldName
     * @return bool
     */
    public function date(string $date, string $format = 'Y-m-d', string $fieldName = 'date'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            $this->errors[$fieldName] = "Invalid date format for $fieldName";
            return false;
        }
        return true;
    }

    /**
     * Validate URL format
     *
     * @param string $url
     * @param string $fieldName
     * @return bool
     */
    public function url(string $url, string $fieldName = 'url'): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->errors[$fieldName] = "Invalid URL format";
            return false;
        }
        return true;
    }

    /**
     * Get all validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if validation has errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Clear all errors
     *
     * @return void
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Sanitize string
     *
     * @param string $value
     * @return string
     */
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     *
     * @param string $email
     * @return string
     */
    public static function sanitizeEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Validate password strength
     *
     * @param string $password
     * @param string $fieldName
     * @return bool
     */
    public function password(string $password, string $fieldName = 'password'): bool
    {
        $config = require __DIR__ . '/../../config/app.php';
        $requirements = $config['password'];

        if (strlen($password) < $requirements['min_length']) {
            $this->errors[$fieldName] = "Password must be at least {$requirements['min_length']} characters";
            return false;
        }

        return true;
    }
}
