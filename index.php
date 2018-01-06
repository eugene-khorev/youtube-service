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

// Init SQLite task repository
$taskRepository = new Task\SqliteRepository(realpath('./tsaks.sqlite'));

// Init task manager
$taskManager = new Task\Manager($taskRepository);

// Create new tasks
$userId = 123;
$videoUrl = 'https://www.youtube.com/watch?v=4D03MCvb5Sc';
$subscribeUrl = 'https://www.youtube.com/channel/UCWnNKC1wrH_NXAXc5bhbFnA';

$taskManager->createTask($userId, $videoUrl);
$taskManager->createTask($userId, $subscribeUrl);

// Check if video is liked by user
$videoInfo = Task\Manager::getTaskInfoFromUrl($videoUrl);
$ratings = $client->videosGetRating($videoInfo);
if (!empty($ratings) && isset($ratings['items']) && !empty($ratings['items'])) {
    // Close the task
    $taskManager->closeTask($userId, $videoUrl);
}

// Check if user subscribed to the channel
$subscribeInfo = Task\Manager::getTaskInfoFromUrl($subscribeUrl);
$subscriptions = $client->subscriptionsListForChannelId($subscribeInfo['id']);
if (!empty($subscriptions) && isset($subscriptions['items']) && !empty($subscriptions['items'])) {
    // Close the task
    $taskManager->closeTask($userId, $subscribeUrl);
}