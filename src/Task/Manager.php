<?php

namespace Task;

class Manager
{
    
    private $taskRepository;

    function __construct(RepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }
    
    /**
     * Creates a new task for a given user from a given URL
     * @param int $userId
     * @param string $url
     */
    public function createTask(int $userId, string $url)
    {
        $info = self::getTaskInfoFromUrl($url);
        $this->taskRepository->createTask($userId, $info['service'], $info['type'], $info['id']);
    }
    
    public function closeTask(int $userId, string $url)
    {
        $info = self::getTaskInfoFromUrl($url);
        $this->taskRepository->closeTask($userId, $info['service'], $info['type'], $info['id']);
    }
    
    /**
     * Returns task parameters from a given URL
     * @param string $url
     * @return array
     * @throws IncorrectUrlException
     */
    public static function getTaskInfoFromUrl(string $url): array
    {
        // Parse URL
        $urlInfo = parse_url($url);
        
        // Check task type by host name
        switch ($urlInfo['host']) {
            case 'www.youtube.com':
            case 'www.youtube.be':
            case 'youtube.com':
            case 'youtube.be':
                return self::getYoutubeTaskInfo($urlInfo);
                
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
    private static function getYoutubeTaskInfo(array $urlInfo)
    {
        $path = $urlInfo['path'];
        $query = $urlInfo['query'];
        
        // Check if this is video URL
        if ($path == '/watch' && substr($query, 0, 2) == 'v=') {
            return [
                'service' => 'youtube',
                'type' => 'like',
                'id' => substr($query, 2),
            ];
        }

        // Check if this is channel URL
        $matches = [];
        if (preg_match('/^\/channel\/([^\/?]+)/', $path, $matches)) {
            return [
                'service' => 'youtube',
                'type' => 'subscruption',
                'id' => $matches[1],
            ];
        }
        
        // Otherwise there is an error in the URL
        throw new IncorrectUrlException;
    }

}