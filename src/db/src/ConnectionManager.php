<?php declare(strict_types=1);


namespace Swoft\Db;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Co;
use Swoft\Concern\DataPropertyTrait;
use Swoft\Connection\Pool\ConnectionInterface as BaseConnection;

/**
 * Class TransactionManager
 *
 * @since 2.0
 *
 * @Bean(scope=Bean::REQUEST)
 */
class ConnectionManager
{
    /**
     * Transaction key
     */
    const TRANSACTION_KEY = 'transaction';

    /**
     * Connection key
     */
    const CONNECTION_KEY = 'connection';

    /**
     * @example
     * [
     *   'transaction' =>[
     *      'cid' => [
     *          'transactions' => 0,
     *          'connection' => Connection
     *      ]
     *   ]
     *  'connection' =>[
     *      'connectionId' => Connection
     *  ]
     * ]
     */
    use DataPropertyTrait;

    /**
     * @return bool
     */
    public function isTransaction(): bool
    {
        return $this->has($this->getTckey());
    }

    /**
     * @param Connection $connection
     */
    public function setTransactionConnection(Connection $connection): void
    {
        $this->set($this->getTckey(), $connection);
    }

    /**
     * @param int $transactions
     */
    public function setTransactions(int $transactions): void
    {
        $this->set($this->getTtKey(), $transactions);
    }

    /**
     * BaseConnection
     *
     * @param BaseConnection $connection
     */
    public function setConnection(BaseConnection $connection): void
    {
        $cKey = $this->getCkey($connection->getId());
        $this->set($cKey, $connection);
    }

    /**
     * @return Connection|null
     */
    public function getTransactionConnection(): ?Connection
    {
        return $this->get($this->getTckey(), null);
    }

    /**
     * Inc transactions
     */
    public function IncTransactions()
    {
        $transactions = $this->get($this->getTtKey(), 0);
        $this->setTransactions($transactions + 1);
    }

    /**
     * Dec transactions
     */
    public function DecTransactions()
    {
        $transactions = $this->get($this->getTtKey(), 0);
        if ($transactions > 0) {
            $this->setTransactions($transactions - 1);
        }
    }

    /**
     * @return int
     */
    public function getTransactions(): int
    {
        return $this->get($this->getTtKey(), 0);
    }

    /**
     * Release transaction
     *
     * @param int $connectionId
     */
    public function releaseTransaction(int $connectionId)
    {
        $cid  = Co::id();
        $tKey = sprintf('%s.%d', self::TRANSACTION_KEY, $cid);
        $this->unset($tKey);

        $this->releaseConnection($connectionId);
    }

    /**
     * @param int $id
     */
    public function releaseConnection(int $id)
    {
        $cKey = sprintf('%s.%d', self::CONNECTION_KEY, $id);
        $this->unset($cKey);
    }

    /**
     * Destroy
     *
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function __destroy()
    {
        $transactions = $this->get(self::TRANSACTION_KEY);
        $connections  = $this->get(self::CONNECTION_KEY);

        // Destroy transaction
        foreach ($transactions as $cid => $transaction) {
            if (!empty($connection) && $connection instanceof Connection) {
                $connection->release(true);
            }
        }

        // Destroy connection
        foreach ($connections as $connectionId => $cConnection) {
            if (!empty($cConnection) && $cConnection instanceof Connection) {
                $cConnection->release(true);
            }
        }

        $this->unset(self::TRANSACTION_KEY);
        $this->unset(self::CONNECTION_KEY);
    }

    /**
     * Get connection key
     *
     * @param int $id
     *
     * @return string
     */
    private function getCkey(int $id): string
    {
        return sprintf('%s.%d', self::CONNECTION_KEY, $id);
    }

    /**
     * @return string
     */
    private function getTtKey(): string
    {
        $cid = Co::id();
        return sprintf('%s.%d.transactions', self::TRANSACTION_KEY, $cid);
    }

    /**
     *
     * @return string
     */
    private function getTckey(): string
    {
        $cid = Co::id();
        return sprintf('%s.%d.connection', self::TRANSACTION_KEY, $cid);
    }
}