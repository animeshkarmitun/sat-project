<?php

return [

    /*
    |--------------------------------------------------------------------------
    | General System Error Messages
    |--------------------------------------------------------------------------
    */

    'general' => [
        'unexpected_error' => 'An unexpected error occurred. Please try again later.',
        'unauthorized' => 'Unauthorized access. Please log in to continue.',
        'forbidden' => 'You do not have permission to perform this action.',
        'not_found' => ':resource not found.',
        'invalid_request' => 'Invalid request parameters.',
        'rate_limit_exceeded' => 'Too many requests. Please slow down.',
        'database_error' => 'A database error occurred. Please try again later.',
        'maintenance_mode' => 'The system is currently under maintenance. Please try again later.',
        'server_busy' => 'The server is currently experiencing high traffic. Please try again shortly.',
        'action_failed' => 'The requested action could not be completed. Please try again.',
        'bad_request' => 'Bad request. Please check your input and try again.',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Validation Messages
    |--------------------------------------------------------------------------
    */

    'user' => [
        'username_required' => 'Username is required.',
        'username_unique' => 'This username is already taken.',
        'username_invalid' => 'Username can only contain letters, numbers, underscores, and hyphens.',
        'username_length' => 'Username must be between 3 and 30 characters long.',
        'email_required' => 'Email is required.',
        'email_unique' => 'This email is already registered.',
        'email_invalid' => 'Invalid email format.',
        'password_required' => 'Password is required.',
        'password_weak' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        'password_mismatch' => 'Passwords do not match.',
        'password_reused' => 'New password must be different from the old password.',
        'phone_invalid' => 'Invalid phone number format.',
        'phone_unique' => 'This phone number is already registered.',
        'first_name_required' => 'First name is required.',
        'first_name_invalid' => 'First name can only contain letters and spaces.',
        'last_name_required' => 'Last name is required.',
        'last_name_invalid' => 'Last name can only contain letters and spaces.',
        'profile_picture_invalid' => 'Invalid profile picture format. Only JPG, PNG, and GIF are allowed.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication & Security Messages
    |--------------------------------------------------------------------------
    */

    'auth' => [
        'login_failed' => 'Invalid username or password.',
        'too_many_attempts' => 'Too many login attempts. Please try again later.',
        'unauthenticated' => 'You must be logged in to access this resource.',
        'session_expired' => 'Your session has expired. Please log in again.',
        'token_invalid' => 'Invalid authentication token.',
        'account_disabled' => 'Your account has been disabled. Please contact support.',
        'account_not_verified' => 'Your account has not been verified. Please check your email.',
        'password_reset_failed' => 'Password reset failed. Please try again.',
        'password_reset_success' => 'Password reset successful. You can now log in with your new password.',
        'old_password_mismatch' => 'The old password does not match our records.',
        'new_password_invalid' => 'New password must be different from the old password.',
        'logout_success' => 'You have been successfully logged out.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Validation Messages
    |--------------------------------------------------------------------------
    */

    'test' => [
        'name_required' => 'Test name is required.',
        'name_unique' => 'A test with this name already exists.',
        'type_invalid' => 'Invalid test type. Accepted values are SAT 1, SAT 2, or Personalized.',
        'duration_invalid' => 'Test duration must be between 10 and 300 minutes.',
        'admin_required' => 'Only admins can create or modify tests.',
        'section_required' => 'A test must have at least one section.',
        'language_invalid' => 'Invalid language code. Accepted values are en, es, fr, de, it.',
        'test_deletion_restricted' => 'This test cannot be deleted as it has active participants.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Section Validation Messages
    |--------------------------------------------------------------------------
    */

    'section' => [
        'name_required' => 'Section name is required.',
        'name_unique' => 'A section with this name already exists in the test.',
        'test_not_found' => 'The specified test does not exist.',
        'order_invalid' => 'Section order must be a positive number.',
        'time_limit_invalid' => 'Time limit must be a valid integer within the allowed range.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment & Refund Messages
    |--------------------------------------------------------------------------
    */

    'payment' => [
        'amount_required' => 'Payment amount is required.',
        'amount_invalid' => 'Invalid payment amount.',
        'method_invalid' => 'Invalid payment method. Accepted values are credit_card, paypal, stripe.',
        'transaction_failed' => 'Transaction failed. Please try again later.',
        'currency_invalid' => 'Invalid currency type selected.',
        'refund_not_allowed' => 'Refund is not allowed for this transaction.',
    ],

    'refund' => [
        'user_not_found' => 'User not found for refund processing.',
        'payment_not_found' => 'Original payment transaction not found.',
        'amount_invalid' => 'Refund amount must not exceed the original payment amount.',
        'status_invalid' => 'Invalid refund status. Accepted values are pending, approved, rejected.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Validation Messages
    |--------------------------------------------------------------------------
    */

    'subscription' => [
        'plan_required' => 'Subscription plan is required.',
        'plan_invalid' => 'Invalid subscription plan. Accepted values are free, basic, premium.',
        'start_date_invalid' => 'Start date must be a valid timestamp.',
        'end_date_invalid' => 'End date must be after the start date.',
        'status_invalid' => 'Invalid subscription status. Accepted values are active, expired, cancelled.',
        'cancellation_not_allowed' => 'Subscription cancellation is not allowed at this time.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Coupon & Discount Messages
    |--------------------------------------------------------------------------
    */

    'coupon' => [
        'code_required' => 'Coupon code is required.',
        'code_invalid' => 'Invalid coupon code.',
        'expired' => 'This coupon has expired.',
        'usage_limit_reached' => 'Coupon usage limit has been reached.',
        'minimum_purchase_required' => 'Minimum purchase amount required to use this coupon.',
        'already_used' => 'You have already used this coupon.',
    ],
];
