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
 * @Bean()
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
     *  'tid' => [
     *      'transaction' => [
     *          'cid' => [
     *              'transactions' => 0,
     *              'connection' => Connection
     *          ]
     *      ],
     *
     *     'connection' => [
     *          'connectionId' => Connection
     *      ]
     *   ],
     *  'tid2' => [
     *      'transaction' => [
     *          'cid' => [
     *              'transactions' => 0,
     *              'connection' => Connection
     *          ]
     *      ],
     *
     *     'connection' => [
     *          'connectionId' => Connection
     *      ]
     *   ],
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
     * @param int $transactions
     */
    public function setTransactions(int $transactions): void
    {
        $this->set($this->getTtKey(), $transactions);
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
        $tid  = Co::tid();
        $cid  = Co::id();
        $tKey = sprintf('%d.%s.%d', $tid, self::TRANSACTION_KEY, $cid);
        $this->unset($tKey);

        $this->releaseConnection($connectionId);
    }

    /**
     * @param int $id
     */
    public function releaseConnection(int $id)
    {
        $tid  = Co::tid();
        $cKey = sprintf('%d.%s.%d', $tid, self::CONNECTION_KEY, $id);
        $this->unset($cKey);
    }

    /**
     * Destroy
     *
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function destroy()
    {
//        $tid   = Co::tid();
//        $tkeys = sprintf('%d.%s', $tid, self::TRANSACTION_KEY);
//        $ckeys = sprintf('%d.%s', $tid, self::CONNECTION_KEY);
//
//        $transactions = $this->get($tkeys, []);
//        $connections  = $this->get($ckeys, []);
//
//        // Destroy transaction
//        foreach ($transactions as $cid => $connection) {
//            $connection = $connection['connection'] ?? null;
//            if (!empty($connection) && $connection instanceof Connection) {
//                $connection->forceRollback();
//            }
//        }
//
//        // Destroy connection
//        foreach ($connections as $connectionId => $cConnection) {
//            if (!empty($cConnection) && $cConnection instanceof Connection) {
//                $cConnection->release(true);
//            }
//        }
//
//        $this->unset((string)$tid);
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
        $tid = Co::tid();
        return sprintf('%d.%s.%d', $tid, self::CONNECTION_KEY, $id);
    }

    /**
     * @return string
     */
    private function getTtKey(): string
    {
        $tid = Co::tid();
        $cid = Co::id();
        return sprintf('%d.%s.%d.transactions', $tid, self::TRANSACTION_KEY, $cid);
    }

    /**
     *
     * @return string
     */
    private function getTckey(): string
    {
        $tid = Co::tid();
        $cid = Co::id();
        return sprintf('%d.%s.%d.connection', $tid, self::TRANSACTION_KEY, $cid);
    }
}