<?php

namespace Task;

interface RepositoryInterface
{
    
    /**
     * Creates a new task
     * @param int $userId
     * @param string $service
     * @param string $type
     * @param string $entityId
     */
    public function createTask(int $userId, string $service, string $type, string $entityId);
    
    /**
     * Sets task status
     * @param int $userId
     * @param string $service
     * @param string $type
     * @param string $entityId
     */
    public function closeTask(int $userId, string $service, string $type, string $entityId);
    
}