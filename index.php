<?php
if (!headers_sent() && '' == session_id()) {
    session_start();
}

$path = @parse_url($_SERVER['REQUEST_URI'])['path'];

// echo $path . '\n';

switch ($path) {
    case '/':
        require 'login.php';
        break;
    case '/grocery.php':
        require 'grocery.php';
        break;
    case '/login.php':
        require 'login.php';
        break;
    case '/signup.php':
        require 'signup.php';
        break;
    case '/admin.php':
        require 'admin.php';
        break;
    case '/admin_change_success.php':
        require 'admin_change_success.php';
        break;
    case '/my_profile.php':
        require 'my_profile.php';
        break;
    case '/my_favorites.php':
        require 'my_favorites.php';
        break;
    case '/my_reviews.php':
        require 'my_reviews.php';
        break;
    case '/my_watchlist.php':
        require 'my_watchlist.php';
        break;
    case '/my_history.php':
        require 'my_history.php';
        break;
    case '/my_memberships.php':
        require 'my_memberships.php';
        break;
    case '/request_change.php':
        require 'request_change.php';
        break;
    case '/request_change_success.php':
        require 'request_change_success.php';
        break;
    case '/write_review.php':
        require 'write_review.php';
        break;
    case '/write_review_success.php':
        require 'write_review_success.php';
        break;
    case '/changePassword.php':
        require 'changePassword.php';
        break;
    default:
        if (strpos($path, '/index.php') === 0) {
            require 'login.php';
        } else {
            http_response_code(404);
            exit('Not Found');
        }
        break;
}
?>