<?php declare(strict_types=1);


namespace Swoft\Db;


use Swoft\Bean\BeanFactory;
use Swoft\Connection\Pool\AbstractPool;
use Swoft\Connection\Pool\ConnectionInterface;
use Swoft\Context\Context;
use Swoft\Db\Exception\PoolException;

/**
 * Class Pool
 *
 * @since 2.0
 */
class Pool extends AbstractPool
{
    /**
     * Default pool name
     */
    const DEFAULT_POOL = 'db.pool';

    /**
     * Database
     *
     * @var Database
     */
    protected $database;

    /**
     * Rewrite connection
     *
     * @return ConnectionInterface
     * @throws PoolException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Connection\Pool\Exception\ConnectionPoolException
     */
    public function getConnection(): ConnectionInterface
    {
//        /* @var ConnectionManager $cm */
//        $cm = BeanFactory::getBean(ConnectionManager::class);
//        if (!$cm->isTransaction()) {
//            $connection = parent::getConnection();
//            $connection->setRelease(true);
//
//            return $connection;
//        }
//
//        $connection = $cm->getTransactionConnection();
//        if (empty($connection) || !$connection instanceof ConnectionInterface) {
//            throw new PoolException('Connection from connection manager is not exist!');
//        }
        $connection = parent::getConnection();
        $connection->setRelease(true);

        return $connection;
    }

    /**
     * Create connection
     *
     * @return ConnectionInterface
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function createConnection(): ConnectionInterface
    {
        return $this->database->createConnection($this);
    }
}