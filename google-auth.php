<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once 'functions.php';
require_once 'config-google.php';

// Debug logging
error_log('Google Auth - Script started');

// Handle Google OAuth callback
if (isset($_GET['code'])) {
    if (!isset($_GET['state']) || !verifyState($_GET['state'])) {
        die('Invalid state parameter');
    }

    // Exchange authorization code for access token
    $tokenResponse = file_get_contents($googleTokenURL, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'code' => $_GET['code'],
                'client_id' => GOOGLE_CLIENT_ID,
                'client_secret' => GOOGLE_CLIENT_SECRET,
                'redirect_uri' => GOOGLE_REDIRECT_URI,
                'grant_type' => 'authorization_code'
            ])
        ]
    ]));

    $tokenData = json_decode($tokenResponse, true);
    
    if (isset($tokenData['access_token'])) {
        // Get user info using access token
        $userInfoResponse = file_get_contents($googleUserInfoURL . '?access_token=' . $tokenData['access_token']);
        $userInfo = json_decode($userInfoResponse, true);
        
        if (!empty($userInfo['email'])) {
            error_log('Google Auth - Processing user: ' . $userInfo['email']);
            
            // Prepare user data
            $userData = [
                'email' => $userInfo['email'],
                'name' => $userInfo['name'] ?? '',
                'first_name' => $userInfo['given_name'] ?? '',
                'last_name' => $userInfo['family_name'] ?? '',
                'picture' => $userInfo['picture'] ?? '',
                'google_id' => $userInfo['id'] ?? '',
                'is_verified' => 1
            ];

            // Handle user login/registration
            $result = handleGoogleUser($userData);
            
            if ($result['status'] === 'success') {
                // Ensure we have a proper redirect URL
                $redirectUrl = $result['redirect'];
                if (!preg_match('/^https?:\/\//', $redirectUrl)) {
                    // If it's not a full URL, prepend the site URL
                    $redirectUrl = rtrim(SITE_URL, '/') . '/' . ltrim($redirectUrl, '/');
                }
                
                error_log('Google Auth - Final redirect URL: ' . $redirectUrl);
                header('Location: ' . $redirectUrl);
                exit();
            } else {
                throw new Exception($result['message'] ?? 'Login failed');
            }
        } else {
            // Handle error
            header('Location: login.php?error=google_auth_failed');
            exit();
        }
    }
    
    // If we get here, something went wrong
    header('Location: login.php?error=google_auth_failed');
    exit();
}

// Handle login request
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

if ($action === 'login') {
    // Generate and store state
    $state = generateState();
    $_SESSION['oauth2state'] = $state;
    
    // Create Google OAuth URL with consent screen parameters
    $authParams = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => implode(' ', GOOGLE_SCOPES),
        'state' => $state,
        'access_type' => GOOGLE_ACCESS_TYPE,
        'prompt' => GOOGLE_PROMPT,
        'include_granted_scopes' => 'true'
    ];
    
    $authUrl = $googleOauthURL . '?' . http_build_query($authParams);
    
    // Redirect to Google OAuth
    header('Location: ' . $authUrl);
    exit();
}

// If no valid action, redirect to sign in
header('Location: login.php');
exit();
?>
