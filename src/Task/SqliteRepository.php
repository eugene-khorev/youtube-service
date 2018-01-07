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
        $this->db = new \PDO('sqlite:' . $dbFilename);
        if (!$this->db) {
            throw new \Exception('Unable to open database file');
        }
    }
    
    /**
     * @inheritdoc
     */
    public function createTask(int $userId, string $service, string $type, string $entityId)
    {
        /*return */$this->db->exec
                ("INSERT INTO tasks (user_id, service, type, entity_id, status) "
                . "VALUES('{$userId}', '{$service}', '{$type}', '{$entityId}', '" . self::STATUS_OPEN . "')");
    }

    /**
     * @inheritdoc
     */
    public function closeTask(int $userId, string $service, string $type, string $entityId)
    {
        return $this->db->exec
                ("UPDATE tasks SET "
                    . "status = " . self::STATUS_CLOSED
                . " WHERE "
                    . "user_id = '{$userId}' AND "
                    . "service= '{$service}' AND "
                    . "type = '{$type}' AND "
                    . "entity_id = '{$entityId}'"
            );
        
    }

}