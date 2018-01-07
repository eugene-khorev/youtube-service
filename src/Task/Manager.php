<?php

namespace Task;

class Manager
{
    
    /**
     * Task repository
     * @var RepositoryInterface 
     */
    private $taskRepository;
    
    /**
     * Youtube client
     * @var \Youtube\Client 
     */
    private $youtubeClient;

    /**
     * Constructor
     * @param \Task\RepositoryInterface $taskRepository
     * @param \Youtube\Client $youtubeClient
     */
    function __construct(RepositoryInterface $taskRepository, \Youtube\Client $youtubeClient)
    {
        $this->taskRepository = $taskRepository;
        $this->youtubeClient = $youtubeClient;
    }
    
    /**
     * Creates a new task for a given user from a given URL
     * @param int $userId
     * @param string $url
     */
    public function createTask(int $userId, string $url)
    {
        $info = self::getTaskInfoFromUrl($url);
        return $this->taskRepository->createTask($userId, $info['service'], $info['type'], $info['id']);
    }
    
    /**
     * Closes a task for a given user within a given URL
     * @param int $userId
     * @param string $url
     */
    public function closeTask(int $userId, string $url)
    {
        $info = self::getTaskInfoFromUrl($url);
        return $this->taskRepository->closeTask($userId, $info['service'], $info['type'], $info['id']);
    }
    
    /**
     * Checks if a task is completed by a given task URL
     * @param string $url
     * @return boolean
     */
    public function isTaskCompleted(string $url)
    {
        $info = self::getTaskInfoFromUrl($url);
        
        switch ($info['service']) {
            case 'youtube':
                return $this->isYoutubeTaskCompleted($info['type'], $info['id']);
            
            default:
                return false;
        }
    }
    
    /**
     * Checks if Youtube task is completed by a given type and id
     * @param string $type
     * @param string $id
     * @return boolean
     */
    private function isYoutubeTaskCompleted(string $type, string $id)
    {
        // Run an API request depending on the task type
        switch ($type) {
            case 'like':
                $ratings = $this->youtubeClient->videosGetRating($id);
                return (
                        !empty($ratings) && 
                        isset($ratings['items']) && 
                        !empty($ratings['items'])
                    );
            
            case 'subscription':
                $subscriptions = $this->youtubeClient->subscriptionsListForChannelId($id);
                return (
                        !empty($subscriptions) && 
                        isset($subscriptions['items']) && 
                        !empty($subscriptions['items'])
                    );
            
            default:
                return false;
        }
    }
    
    /**
     * Returns task parameters from a given URL
     * @param string $url
     * @return array
     * @throws IncorrectUrlException
     */
    public function getTaskInfoFromUrl(string $url): array
    {
        // Parse URL
        $urlInfo = parse_url($url);
        
        // Check task type by host name
        switch ($urlInfo['host']) {
            case 'www.youtube.com':
            case 'www.youtube.be':
            case 'youtube.com':
            case 'youtube.be':
                return $this->getYoutubeTaskInfo($urlInfo);
                
            default: 
                // Otherwise there is an error in the URL
                throw new IncorrectUrlException;        
        }
    }
    
    /**
     * Parses Youtube task parameters from a given URL data
     * @param array $urlInfo
     * @return array
     * @throws IncorrectUrlException
     */
    private function getYoutubeTaskInfo(array $urlInfo)
    {
        $matches = [];
        $query = [];
        $path = $urlInfo['path'] ?? '';
        parse_str($urlInfo['query'] ?? '', $query);
        
        // Check if this is video URL
        if ($path == '/watch' && isset($query['v'])) {
            return [
                'service' => 'youtube',
                'type' => 'like',
                'id' => $query['v'],
            ];
        }

        // Check if this is channel URL
        if (preg_match('/^\/channel\/([^\/?]+)/', $path, $matches)) {
            return [
                'service' => 'youtube',
                'type' => 'subscription',
                'id' => $matches[1],
            ];
        }
        
        // Check if this is user URL
        if (preg_match('/^\/user\/([^\/?]+)/', $path, $matches)) {
            // Get channel ID by given user name
            $channelInfo = $this->youtubeClient->cannelsListByUsername($matches[1]);
            if (empty($channelInfo) || !isset($channelInfo['items']) || empty($channelInfo['items'])) {
                throw new IncorrectUrlException;
            }
            
            return [
                'service' => 'youtube',
                'type' => 'subscription',
                'id' => $channelInfo['items'][0]['id'],
            ];
        }
        
        // Otherwise there is an error in the URL
        throw new IncorrectUrlException;
    }
    
}
