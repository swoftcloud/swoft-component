<?php declare(strict_types=1);


namespace Swoft\Connection\Pool;

use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class PoolManager
 *
 * @since 2.0
 *
 * @Bean()
 */
class PoolManager
{
    /**
     * All connection counter from 0
     *
     * @var int
     */
    private $counter = 0;

    /**
     * @return int
     */
    public function getConnectionId()
    {
        $this->counter++;

        return $this->counter;
    }
}