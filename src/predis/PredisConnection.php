<?php
declare(strict_types=1);

namespace yii\redis\predis;

use Predis\Client;
use Predis\Response\ErrorInterface;
use Predis\Response\ResponseInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use yii\redis\Connection as YiiRedisConnection;
use yii\redis\predis\Command\CommandDecorator;

/**
 * Class PredisConnection
 *
 * @see https://github.com/predis/predis
 * ```php
 * // redis-sentinel
 * 'redis' = [
 *      'class' => PredisConnection::class,
 *      'parameters' => [
 *          'tcp://127.0.0.1:26379?timeout=0.100',
 *          'tcp://127.0.0.1:26380?timeout=0.100',
 *          'tcp://127.0.0.1:26381?timeout=0.100',
 *      ],
 *      'options' => [
 *          'replication' => 'sentinel',
 *          // значение для 'service' можно получить подключившись к redis
 *          // через redis-cli -h 127.0.0.1 -p 26379 --pass 'password'
 *          // и выполнить SENTINEL masters
 *          'service' => 'mymaster',
 *          'parameters' => [
 *              'password' => 'password',
 *              'database' => 10,
 *              // @see \Predis\Connection\StreamConnection
 *              'persistent' => true, // performs the connection asynchronously
 *              'async_connect' => true, //the connection asynchronously
 *              'read_write_timeout' => 0.1, // timeout of read / write operations
 *              //'timeout' => 0.1, // @note timeout переопределяется в predis, timeout в строке подключения
 *           ],
 *      ],
 * ];
 * // redis-cluster
 *  'redis' = [
 *       'class' => PredisConnection::class,
 *       'parameters' => [
 *           'tcp://127.0.0.1:5380?timeout=0.100',
 *           'tcp://127.0.0.1:5381?timeout=0.100',
 *           'tcp://127.0.0.1:5382?timeout=0.100',
 *       ],
 *       'options' => [
 *           'cluster' => 'redis'
 *          'parameters' => [
 *              'password' => 'password',
 *              // @see \Predis\Connection\StreamConnection
 *              'persistent' => true, // performs the connection asynchronously
 *              'async_connect' => true, //the connection asynchronously
 *              'read_write_timeout' => 0.1, // timeout of read / write operations
 *              //'timeout' => 0.1, // @note timeout переопределяется в predis, используй timeout в строке подключения
 *          ],
 *  ];
 * ```
 */
class PredisConnection extends YiiRedisConnection
{
    /**
     * @var mixed Connection parameters for one or more servers.
     */
    public mixed $parameters;

    /**
     * @var mixed Options to configure some behaviours of the client.
     */
    public mixed $options = [];

    /**
     * @var Client|null redis connection
     */
    protected Client|null $clientSocket = null;

    /**
     * Returns a value indicating whether the DB connection is established.
     *
     * @return bool whether the DB connection is established
     */
    public function getIsActive(): bool
    {
        return (bool)$this->clientSocket?->isConnected();
    }

    /**
     * @inheritdoc
     * @return mixed|ErrorInterface|ResponseInterface
     * @throws InvalidConfigException
     */
    public function executeCommand($name, $params = []): mixed
    {
        $this->open();

        Yii::debug("Executing Redis Command: {$name} " . implode(' ', $params), __METHOD__);

//        $aaa = $this->database;
//        $this->clientSocket->select(1);

        $command = $this->clientSocket->createCommand($name, $params);
        $res = $this->clientSocket->executeCommand(new CommandDecorator($command));
        return $res;
    }

    /**
     * Establishes a DB connection.
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function open(): void
    {
        if (null !== $this->clientSocket) {
            return;
        }

        if (empty($this->parameters)) {
            throw new InvalidConfigException('Connection::parameters cannot be empty');
        }

        Yii::debug('Opening redis DB connection', __METHOD__);

//        $otp = array_merge([
//            'commands' => new CommandFactory(),
//            $this->options,
//        ]);

        $this->clientSocket = new Client($this->parameters, $this->options);
        $this->initConnection();
    }

    /**
     * @inheritdoc
     */
    public function close(): void
    {
        $this->clientSocket?->disconnect();
    }

    /**
     * Get predis Client
     *
     * @return Client|null
     * @throws InvalidConfigException
     */
    public function getClientSocket(): ?Client
    {
        $this->open();

        return $this->clientSocket;
    }

    /**
     * @inheritdoc
     */
    public function ping($message = null): bool
    {
        $this->open();
        return (string)$this->clientSocket->ping() === 'PONG';
    }

    /**
     * Allows issuing all supported commands via magic methods.
     * ```php
     * $redis->hmset('test_collection', 'key1', 'val1', 'key2', 'val2')
     * ```
     *
     * @param string $name name of the missing method to execute
     * @param array $params method call arguments
     * @return mixed
     */
    public function __call($name, $params)
    {
        $redisCommand = strtoupper(Inflector::camel2words($name, false));
        if (in_array($redisCommand, $this->redisCommands)) {
            $res = $this->executeCommand($redisCommand, $params);
            return $res;
        }

        return parent::__call($name, $params);
    }
}
