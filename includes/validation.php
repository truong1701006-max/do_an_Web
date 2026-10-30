<?php
/**
 * Validation Functions for Minh Phone Store
 * Provides secure input validation for forms
 */

/**
 * Validate phone number format
 * Accepts 10-11 digit phone numbers
 * 
 * @param string $phone
 * @return bool
 */
function validate_phone($phone) {
    $phone = trim(preg_replace('/[^\d]/', '', $phone));
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}

/**
 * Validate email address
 * 
 * @param string $email
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate address field
 * Check length constraints
 * 
 * @param string $address
 * @param int $minLength
 * @param int $maxLength
 * @return bool
 */
function validate_address($address, $minLength = 10, $maxLength = 255) {
    $length = strlen(trim($address));
    return $length >= $minLength && $length <= $maxLength;
}

/**
 * Validate password strength
 * Minimum 6 characters
 * 
 * @param string $password
 * @return bool
 */
function validate_password($password) {
    return strlen($password) >= 6;
}

/**
 * Validate full name
 * Check length between 3-100 characters
 * 
 * @param string $name
 * @return bool
 */
function validate_full_name($name) {
    $length = strlen(trim($name));
    return $length >= 3 && $length <= 100;
}

/**
 * Validate username/account name
 * Alphanumeric, underscore, hyphen allowed
 * Length 3-32 characters
 * 
 * @param string $username
 * @return bool
 */
function validate_username($username) {
    $length = strlen(trim($username));
    $pattern = '/^[a-zA-Z0-9_-]{3,32}$/';
    return preg_match($pattern, $username) && $length >= 3 && $length <= 32;
}

/**
 * Sanitize text input
 * Remove HTML tags and trim whitespace
 * 
 * @param string $text
 * @return string
 */
function sanitize_text($text) {
    return trim(strip_tags($text));
}

/**
 * Sanitize email input
 * 
 * @param string $email
 * @return string
 */
function sanitize_email($email) {
    return trim(filter_var($email, FILTER_SANITIZE_EMAIL));
}

/**
 * Batch validate multiple fields
 * 
 * @param array $data Field name => value pairs
 * @param array $rules Field name => validation rule pairs
 * @return array Errors array
 * 
 * Example:
 * $errors = validate_batch([
 *     'email' => $email,
 *     'phone' => $phone
 * ], [
 *     'email' => 'email',
 *     'phone' => 'phone'
 * ]);
 */
function validate_batch($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';
        
        if (empty($value) && $rule !== 'optional') {
            $errors[$field] = ucfirst($field) . ' không được để trống.';
            continue;
        }
        
        if (empty($value) && $rule === 'optional') {
            continue;
        }
        
        switch ($rule) {
            case 'email':
                if (!validate_email($value)) {
                    $errors[$field] = 'Email không hợp lệ.';
                }
                break;
            case 'phone':
                if (!validate_phone($value)) {
                    $errors[$field] = 'Số điện thoại không hợp lệ.';
                }
                break;
            case 'password':
                if (!validate_password($value)) {
                    $errors[$field] = 'Mật khẩu tối thiểu 6 ký tự.';
                }
                break;
            case 'name':
                if (!validate_full_name($value)) {
                    $errors[$field] = 'Tên phải từ 3 đến 100 ký tự.';
                }
                break;
            case 'optional':
                // No validation needed for optional fields
                break;
        }
    }
    
    return $errors;
}

/**
 * Verify CSRF token
 * 
 * @param string $token
 * @return bool
 */
function verify_csrf($token) {
    if (empty($token)) {
        return false;
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 * 
 * @return string
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
