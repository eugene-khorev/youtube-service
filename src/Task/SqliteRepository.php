<?php

namespace Task;

class SqliteRepository implements RepositoryInterface
{
    
    const STATUS_OPEN = 0;
    const STATUS_CLOSED = 1;
    
    private $db;

    /**
     * Initializes DB storage 
     * @param type $dbFilename
     * @throws \Exception
     */
    public function __construct($dbFilename)
    {
        $this->db = new \SQLite3($dbFilename);
    }
    
    /**
     * @inheritdoc
     */
    public function createTask(int $userId, string $service, string $type, string $entityId)
    {
        $this->db->query
                ("INSERT INTO `tasks` SET "
                    . "user_id = '{$userId}', "
                    . "service= '{$service}', "
                    . "type = '{$type}', "
                    . "entity_id = '{$entityId}', "
                    . "status = " . self::STATUS_OPEN
            );
    }

    /**
     * @inheritdoc
     */
    public function closeTask(int $userId, string $service, string $type, string $entityId)
    {
        $this->db->query
                ("UPDATE `tasks` SET "
                    . "status = " . self::STATUS_CLOSED
                . " WHERE "
                    . "user_id = '{$userId}' AND "
                    . "service= '{$service}' AND "
                    . "type = '{$type}' AND "
                    . "entity_id = '{$entityId}'"
            );
        
    }

}