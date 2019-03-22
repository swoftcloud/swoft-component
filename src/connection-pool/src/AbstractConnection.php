<?php declare(strict_types=1);


namespace Swoft\Connection\Pool;

use Swoft\Bean\BeanFactory;

/**
 * Class AbstractConnection
 *
 * @since 2.0
 */
abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * Whether to release connection
     *
     * @var bool
     */
    protected $release = false;

    /**
     * Connection id(Inc from 0)
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Init connection
     *
     * @param PoolInterface $pool
     *
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function initConnection(PoolInterface $pool): void
    {
        $this->pool = $pool;

        /* @var PoolManager $poolManager */
        $poolManager = BeanFactory::getBean(PoolManager::class);

        // Init connection id
        $this->id = $poolManager->getConnectionId();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param bool $release
     */
    public function setRelease(bool $release): void
    {
        $this->release = $release;
    }

    /**
     * Release Connection
     *
     * @param bool $force
     */
    public function release(bool $force = false): void
    {
        if ($this->release) {
            $this->release = false;
            $this->pool->release($this);
        }
    }
}