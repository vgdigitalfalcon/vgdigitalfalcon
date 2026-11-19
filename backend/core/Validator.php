<?php
/**
 * VG Digital Falcon - Input Sanitization & Validation Engine
 */

namespace VGDigitalFalcon\Core;

class Validator
{
    public static function sanitizeString(?string $input, int $maxLength = 1000): string
    {
        if ($input === null) {
            return '';
        }
        $trimmed = trim($input);
        $stripped = strip_tags($trimmed);
        return mb_substr($stripped, 0, $maxLength, 'UTF-8');
    }

    public static function validateEmail(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePhone(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }
        // Accepts 7-16 characters containing numbers, +, -, spaces, parentheses
        return preg_match('/^[0-9+\s\-()]{7,20}$/', trim($phone)) === 1;
    }

    public static function validateUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }
        $trimmed = trim($url);
        // Prepend https:// if protocol missing for user convenience
        if (!preg_match('~^(?:f|ht)tps?://~i', $trimmed)) {
            $trimmed = 'https://' . $trimmed;
        }
        return filter_var($trimmed, FILTER_VALIDATE_URL) !== false;
    }

    public static function isSpam(array $source, string $field = 'address'): bool
    {
        // If hidden honeypot field is filled by bot, flag as spam
        if (!empty($source[$field])) {
            return true;
        }
        return false;
    }

    public static function verifyCaptcha(?string $submittedAnswer): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($submittedAnswer) || !isset($_SESSION['captcha_answer'])) {
            return true; // Allow pass-through if captcha was not activated in session
        }

        $isValid = trim((string)$submittedAnswer) === trim((string)$_SESSION['captcha_answer']);
        // Clear after check to prevent replay
        unset($_SESSION['captcha_answer']);
        return $isValid;
    }
}
