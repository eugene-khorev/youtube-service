<?php

// Call set_include_path() as needed to point to your client library.
if (!file_exists($file = __DIR__ . '/vendor/autoload.php')) {
    throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ . '"');
}
require_once __DIR__ . '/vendor/autoload.php';
session_start();

// Setup path to config file
$configFile = 'client_secrets.json';

// Setup redirect URL
$redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . basename(__FILE__);

// Create session repository
$tokenRepo = new Youtube\SessionTokenRepository;

// Create client
$client = new Youtube\Client($tokenRepo, $configFile, $redirectUrl);

// Authenticate client
$client->authenticate(
        function () {
            return $_GET['code'] ?? null;
        },
        function ($authUrl) {
            header('Location: ' . $authUrl);
        }
    );

// Get video ratings
$ratings = $client->videosGetRating('4D03MCvb5Sc');

// Get video subscriptions
$subscriptions = $client->subscriptionsListForChannelId('UCWnNKC1wrH_NXAXc5bhbFnA');

var_dump($ratings, $subscriptions);

