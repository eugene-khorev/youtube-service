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
$taskRepository = new Task\SqliteRepository(realpath('./tasks.sqlite'));

// Init task manager
$taskManager = new Task\Manager($taskRepository, $client);

// Create new tasks
$userId = 123;
$videoUrl = 'https://www.youtube.com/watch?v=4D03MCvb5Sc';
$subscribeUrl = 'https://www.youtube.com/user/AcademeG';
//$subscribeUrl = 'https://www.youtube.com/channel/UCWnNKC1wrH_NXAXc5bhbFnA';

$taskManager->createTask($userId, $videoUrl);
$taskManager->createTask($userId, $subscribeUrl);

// Close video task if it's completed
if ($taskManager->isTaskCompleted($videoUrl)) {
    $taskManager->closeTask($userId, $videoUrl);
}

// Close subscription task if it's completed
if ($taskManager->isTaskCompleted($subscribeUrl)) {
    $taskManager->closeTask($userId, $subscribeUrl);
}
