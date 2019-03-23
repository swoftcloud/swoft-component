<?php declare(strict_types=1);


namespace Swoft\Db;

use Swoft\Bean\BeanFactory;
use Swoft\Context\Context;
use Swoft\Db\Exception\PoolException;
use Swoft\Db\Exception\QueryException;
use Swoft\Db\Query\Builder;

/**
 * Class Db
 *
 * @see   Connection
 * @since 2.0
 *
 * @method static Builder table($table);
 */
class DB
{
    /**
     * Supported methods
     *
     * @var array
     */
    private static $passthru = [
        'table',
        'raw',
        'selectOne',
        'select',
        'cursor',
        'insert',
        'update',
        'delete',
        'statement',
        'affectingStatement',
        'unprepared',
        'prepareBindings',
        'transaction',
        'beginTransaction',
        'commit',
        'rollBack',
        'transactionLevel',
        'pretend',
    ];

    /**
     * @param string $name
     *
     * @return Connection
     * @throws PoolException
     */
    public static function pool(string $name = Pool::DEFAULT_POOL): Connection
    {
        try {
            $pool = \bean($name);
            if (!$pool instanceof Pool) {
                throw new PoolException(sprintf('%s is not instance of pool', $name));
            }

            $con = $pool->getConnection();
//            $cm  = self::getConnectionManager();
//            $cm->setConnection($con);
            
            return $con;
        } catch (\Throwable $e) {
            throw new PoolException(
                sprintf('Pool error is %s file=%s line=%d', $e->getMessage(), $e->getFile(), $e->getLine())
            );
        }
    }

    /**
     * Proxy method
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws PoolException
     * @throws QueryException
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (!in_array($name, self::$passthru)) {
            throw new QueryException(sprintf('Method(%s) is not exist!', $name));
        }

        $connection = self::pool();
        return $connection->$name(...$arguments);
    }

    /**
     * Get transaction manager
     *
     * @return ConnectionManager
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    private static function getConnectionManager(): ConnectionManager
    {
        return BeanFactory::getBean(ConnectionManager::class);
    }
}