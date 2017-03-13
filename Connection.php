<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use yii\base\Component;
use yii\db\Exception;
use yii\helpers\Inflector;

/**
 * The redis connection class is used to establish a connection to a [redis](http://redis.io/) server.
 *
 * By default it assumes there is a redis server running on localhost at port 6379 and uses the database number 0.
 *
 * It is possible to connect to a redis server using [[hostname]] and [[port]] or using a [[unixSocket]].
 *
 * It also supports [the AUTH command](http://redis.io/commands/auth) of redis.
 * When the server needs authentication, you can set the [[password]] property to
 * authenticate with the server after connect.
 *
 * The execution of [redis commands](http://redis.io/commands) is possible with via [[executeCommand()]].
 *
 * @method mixed clientKill() ip:port Kill the connection of a client
 * @method mixed clientList() Get the list of client connections
 * @method mixed clientGetname() Get the current connection name
 * @method mixed clientSetname() connection-name Set the current connection name
 * @method mixed configGet() parameter Get the value of a configuration parameter
 * @method mixed configSet parameter value Set a configuration parameter to the given value
 * @method mixed configResetstat', // Reset the stats returned by INFO
 * @method mixed debugObject() key Get debugging information about a key
 * @method mixed debugSegfault() Make the server crash
 * @method mixed scriptExists() script [script ...] Check existence of scripts in the script cache.
 * @method mixed scriptFlush() Remove all the scripts from the script cache.
 * @method mixed scriptKill() Kill the script currently in execution.
 * @method mixed scriptLoad() script Load the specified Lua script into the script cache.
 * @method mixed blpop() key [key ...] timeout Remove and get the first element in a list, or block until one is available
 * @method mixed brpop() key [key ...] timeout Remove and get the last element in a list, or block until one is available
 * @method mixed brpoplpush() source destination timeout Pop a value from a list, push it to another list and return it; or block until one is available
 * @method mixed set($key, $value) Set the string value of a key
 * @method mixed get($key) Set the string value of a key
 * @method mixed config resetstat() Reset the stats returned by INFO
 * @method mixed dbsize() Return the number of keys in the selected database
 * @method mixed decr() key Decrement the integer value of a key by one
 * @method mixed decrby() key decrement Decrement the integer value of a key by the given number
 * @method mixed del() key [key ...] Delete a key
 * @method mixed discard() Discard all commands issued after MULTI
 * @method mixed dump() key Return a serialized version of the value stored at the specified key.
 * @method mixed echo () message Echo the given string
 * @method mixed eval() script numkeys key [key ...] arg [arg ...] Execute a Lua script server side
 * @method mixed evalsha() sha1 numkeys key [key ...] arg [arg ...] Execute a Lua script server side
 * @method mixed exec() Execute all commands issued after MULTI
 * @method mixed exists() key Determine if a key exists
 * @method mixed expire() key seconds Set a key's time to live in seconds
 * @method mixed expireat() key timestamp Set the expiration for a key as a UNIX timestamp
 * @method mixed flushall() Remove all keys from all databases
 * @method mixed flushdb() Remove all keys from the current database
 * @method mixed getbit() key offset Returns the bit value at offset in the string value stored at key
 * @method mixed getrange() key start end Get a substring of the string stored at a key
 * @method mixed getset() key value Set the string value of a key and return its old value
 * @method mixed hdel() key field [field ...] Delete one or more hash fields
 * @method mixed hexists() key field Determine if a hash field exists
 * @method mixed hget() key field Get the value of a hash field
 * @method mixed hgetall() key Get all the fields and values in a hash
 * @method mixed hincrby() key field increment Increment the integer value of a hash field by the given number
 * @method mixed hincrbyfloat() key field increment Increment the float value of a hash field by the given amount
 * @method mixed hkeys() key Get all the fields in a hash
 * @method mixed hlen() key Get the number of fields in a hash
 * @method mixed hmget() key field [field ...] Get the values of all the given hash fields
 * @method mixed hmset() key field value [field value ...] Set multiple hash fields to multiple values
 * @method mixed hset() key field value Set the string value of a hash field
 * @method mixed hsetnx() key field value Set the value of a hash field, only if the field does not exist
 * @method mixed hvals() key Get all the values in a hash
 * @method mixed incr() key Increment the integer value of a key by one
 * @method mixed incrby() key increment Increment the integer value of a key by the given amount
 * @method mixed incrbyfloat() key increment Increment the float value of a key by the given amount
 * @method mixed info() [section] Get information and statistics about the server
 * @method mixed keys() pattern Find all keys matching the given pattern
 * @method mixed lastsave() Get the UNIX time stamp of the last successful save to disk
 * @method mixed lindex() key index Get an element from a list by its index
 * @method mixed linsert() key BEFORE|AFTER pivot value Insert an element before or after another element in a list
 * @method mixed llen() key Get the length of a list
 * @method mixed lpop() key Remove and get the first element in a list
 * @method mixed lpush() key value [value ...] Prepend one or multiple values to a list
 * @method mixed lpushx() key value Prepend a value to a list, only if the list exists
 * @method mixed lrange() key start stop Get a range of elements from a list
 * @method mixed lrem() key count value Remove elements from a list
 * @method mixed lset() key index value Set the value of an element in a list by its index
 * @method mixed ltrim() key start stop Trim a list to the specified range
 * @method mixed mget() key [key ...] Get the values of all the given keys
 * @method mixed migrate() host port key destination-db timeout Atomically transfer a key from a Redis instance to another one.
 * @method mixed monitor() Listen for all requests received by the server in real time
 * @method mixed move() key db Move a key to another database
 * @method mixed mset() key value [key value ...] Set multiple keys to multiple values
 * @method mixed msetnx() key value [key value ...] Set multiple keys to multiple values, only if none of the keys exist
 * @method mixed multi() Mark the start of a transaction block
 * @method mixed object() subcommand [arguments [arguments ...]] Inspect the internals of Redis objects
 * @method mixed persist() key Remove the expiration from a key
 * @method mixed pexpire() key milliseconds Set a key's time to live in milliseconds
 * @method mixed pexpireat() key milliseconds-timestamp Set the expiration for a key as a UNIX timestamp specified in milliseconds
 * @method mixed ping() Ping the server
 * @method mixed psetex() key milliseconds value Set the value and expiration in milliseconds of a key
 * @method mixed psubscribe() pattern [pattern ...] Listen for messages published to channels matching the given patterns
 * @method mixed pttl() key Get the time to live for a key in milliseconds
 * @method mixed publish() channel message Post a message to a channel
 * @method mixed punsubscribe() [pattern [pattern ...]] Stop listening for messages posted to channels matching the given patterns
 * @method mixed quit() Close the connection
 * @method mixed randomkey() Return a random key from the keyspace
 * @method mixed rename() key newkey Rename a key
 * @method mixed renamenx() key newkey Rename a key, only if the new key does not exist
 * @method mixed restore() key ttl serialized-value Create a key using the provided serialized value, previously obtained using DUMP.
 * @method mixed rpop() key Remove and get the last element in a list
 * @method mixed rpoplpush() source destination Remove the last element in a list, append it to another list and return it
 * @method mixed rpush() key value [value ...] Append one or multiple values to a list
 * @method mixed rpushx() key value Append a value to a list, only if the list exists
 * @method mixed sadd() key member [member ...] Add one or more members to a set
 * @method mixed save() Synchronously save the dataset to disk
 * @method mixed scard() key Get the number of members in a set
 * @method mixed sdiff() key [key ...] Subtract multiple sets
 * @method mixed sdiffstore() destination key [key ...] Subtract multiple sets and store the resulting set in a key
 * @method mixed select() index Change the selected database for the current connection
 * @method mixed setbit() key offset value Sets or clears the bit at offset in the string value stored at key
 * @method mixed setex() key seconds value Set the value and expiration of a key
 * @method mixed setnx() key value Set the value of a key, only if the key does not exist
 * @method mixed setrange() key offset value Overwrite part of a string at key starting at the specified offset
 * @method mixed shutdown() [NOSAVE] [SAVE] Synchronously save the dataset to disk and then shut down the server
 * @method mixed sinter() key [key ...] Intersect multiple sets
 * @method mixed sinterstore() destination key [key ...] Intersect multiple sets and store the resulting set in a key
 * @method mixed sismember() key member Determine if a given value is a member of a set
 * @method mixed slaveof() host port Make the server a slave of another instance, or promote it as master
 * @method mixed slowlog() subcommand [argument] Manages the Redis slow queries log
 * @method mixed smembers() key Get all the members in a set
 * @method mixed smove() source destination member Move a member from one set to another
 * @method mixed sort() key [BY pattern] [LIMIT offset count] [GET pattern [GET pattern ...]] [ASC|DESC] [ALPHA] [STORE destination] Sort the elements in a list, set or sorted set
 * @method mixed spop() key Remove and return a random member from a set
 * @method mixed srandmember() key [count] Get one or multiple random members from a set
 * @method mixed srem() key member [member ...] Remove one or more members from a set
 * @method mixed strlen() key Get the length of the value stored in a key
 * @method mixed subscribe() channel [channel ...] Listen for messages published to the given channels
 * @method mixed sunion() key [key ...] Add multiple sets
 * @method mixed sunionstore() destination key [key ...] Add multiple sets and store the resulting set in a key
 * @method mixed sync() Internal command used for replication
 * @method mixed time() Return the current server time
 * @method mixed ttl() key Get the time to live for a key
 * @method mixed type() key Determine the type stored at key
 * @method mixed unsubscribe() [channel [channel ...]] Stop listening for messages posted to the given channels
 * @method mixed unwatch() Forget about all watched keys
 * @method mixed watch() key [key ...] Watch the given keys to determine execution of the MULTI/EXEC block
 * @method mixed zadd() key score member [score member ...] Add one or more members to a sorted set, or update its score if it already exists
 * @method mixed zcard() key Get the number of members in a sorted set
 * @method mixed zcount() key min max Count the members in a sorted set with scores within the given values
 * @method mixed zincrby() key increment member Increment the score of a member in a sorted set
 * @method mixed zinterstore() destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM|MIN|MAX] Intersect multiple sorted sets and store the resulting sorted set in a new key
 * @method mixed zrange() key start stop [WITHSCORES] Return a range of members in a sorted set, by index
 * @method mixed zrangebyscore() key min max [WITHSCORES] [LIMIT offset count] Return a range of members in a sorted set, by score
 * @method mixed zrank() key member Determine the index of a member in a sorted set
 * @method mixed zrem() key member [member ...] Remove one or more members from a sorted set
 * @method mixed zremrangebyrank() key start stop Remove all members in a sorted set within the given indexes
 * @method mixed zremrangebyscore() key min max Remove all members in a sorted set within the given scores
 * @method mixed zrevrange() key start stop [WITHSCORES] Return a range of members in a sorted set, by index, with scores ordered from high to low
 * @method mixed zrevrangebyscore() key max min [WITHSCORES] [LIMIT offset count] Return a range of members in a sorted set, by score, with scores ordered from high to low
 * @method mixed zrevrank() key member Determine the index of a member in a sorted set, with scores ordered from high to low
 * @method mixed zscore() key member Get the score associated with the given member in a sorted set
 * @method mixed zunionstore() destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM|MIN|MAX] Add multiple sorted sets and store the resulting sorted set in a new key
 * @method mixed geoadd() key longitude latitude member [longitude latitude member ...] Add point
 * @method mixed geodist() key member1 member2 [unit] Return the distance between two members
 * @method mixed geohash() key member [member ...] Return valid Geohash strings
 * @method mixed geopos() key member [member ...] Return the positions (longitude, latitude)
 * @method mixed georadius() key longitude latitude radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count] Return the members
 * @method mixed georadiusbymember() key member radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count]
 *
 * @property string $driverName Name of the DB driver. This property is read-only.
 * @property boolean $isActive Whether the DB connection is established. This property is read-only.
 * @property LuaScriptBuilder $luaScriptBuilder This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Connection extends Component
{
    /**
     * @event Event an event that is triggered after a DB connection is established
     */
    const EVENT_AFTER_OPEN = 'afterOpen';

    /**
     * @var string the hostname or ip address to use for connecting to the redis server. Defaults to 'localhost'.
     * If [[unixSocket]] is specified, hostname and port will be ignored.
     */
    public $hostname = 'localhost';
    /**
     * @var integer the port to use for connecting to the redis server. Default port is 6379.
     * If [[unixSocket]] is specified, hostname and port will be ignored.
     */
    public $port = 6379;
    /**
     * @var string the unix socket path (e.g. `/var/run/redis/redis.sock`) to use for connecting to the redis server.
     * This can be used instead of [[hostname]] and [[port]] to connect to the server using a unix socket.
     * If a unix socket path is specified, [[hostname]] and [[port]] will be ignored.
     * @since 2.0.1
     */
    public $unixSocket;
    /**
     * @var string the password for establishing DB connection. Defaults to null meaning no AUTH command is send.
     * See http://redis.io/commands/auth
     */
    public $password;
    /**
     * @var integer the redis database to use. This is an integer value starting from 0. Defaults to 0.
     * Since version 2.0.6 you can disable the SELECT command sent after connection by setting this property to `null`.
     */
    public $database = 0;
    /**
     * @var float timeout to use for connection to redis. If not set the timeout set in php.ini will be used: ini_get("default_socket_timeout")
     */
    public $connectionTimeout = null;
    /**
     * @var float timeout to use for redis socket when reading and writing data. If not set the php default value will be used.
     */
    public $dataTimeout = null;
    /**
     * @var integer Bitmask field which may be set to any combination of connection flags passed to [stream_socket_client()](http://php.net/manual/en/function.stream-socket-client.php).
     * Currently the select of connection flags is limited to `STREAM_CLIENT_CONNECT` (default), `STREAM_CLIENT_ASYNC_CONNECT` and `STREAM_CLIENT_PERSISTENT`.
     * @see http://php.net/manual/en/function.stream-socket-client.php
     * @since 2.0.5
     */
    public $socketClientFlags = STREAM_CLIENT_CONNECT;
    /**
     * @var array List of available redis commands http://redis.io/commands
     */
    public $redisCommands = [
        'BLPOP', // key [key ...] timeout Remove and get the first element in a list, or block until one is available
        'BRPOP', // key [key ...] timeout Remove and get the last element in a list, or block until one is available
        'BRPOPLPUSH', // source destination timeout Pop a value from a list, push it to another list and return it; or block until one is available
        'CLIENT KILL', // ip:port Kill the connection of a client
        'CLIENT LIST', // Get the list of client connections
        'CLIENT GETNAME', // Get the current connection name
        'CLIENT SETNAME', // connection-name Set the current connection name
        'CONFIG GET', // parameter Get the value of a configuration parameter
        'CONFIG SET', // parameter value Set a configuration parameter to the given value
        'CONFIG RESETSTAT', // Reset the stats returned by INFO
        'DBSIZE', // Return the number of keys in the selected database
        'DEBUG OBJECT', // key Get debugging information about a key
        'DEBUG SEGFAULT', // Make the server crash
        'DECR', // key Decrement the integer value of a key by one
        'DECRBY', // key decrement Decrement the integer value of a key by the given number
        'DEL', // key [key ...] Delete a key
        'DISCARD', // Discard all commands issued after MULTI
        'DUMP', // key Return a serialized version of the value stored at the specified key.
        'ECHO', // message Echo the given string
        'EVAL', // script numkeys key [key ...] arg [arg ...] Execute a Lua script server side
        'EVALSHA', // sha1 numkeys key [key ...] arg [arg ...] Execute a Lua script server side
        'EXEC', // Execute all commands issued after MULTI
        'EXISTS', // key Determine if a key exists
        'EXPIRE', // key seconds Set a key's time to live in seconds
        'EXPIREAT', // key timestamp Set the expiration for a key as a UNIX timestamp
        'FLUSHALL', // Remove all keys from all databases
        'FLUSHDB', // Remove all keys from the current database
        'GET', // key Get the value of a key
        'GETBIT', // key offset Returns the bit value at offset in the string value stored at key
        'GETRANGE', // key start end Get a substring of the string stored at a key
        'GETSET', // key value Set the string value of a key and return its old value
        'HDEL', // key field [field ...] Delete one or more hash fields
        'HEXISTS', // key field Determine if a hash field exists
        'HGET', // key field Get the value of a hash field
        'HGETALL', // key Get all the fields and values in a hash
        'HINCRBY', // key field increment Increment the integer value of a hash field by the given number
        'HINCRBYFLOAT', // key field increment Increment the float value of a hash field by the given amount
        'HKEYS', // key Get all the fields in a hash
        'HLEN', // key Get the number of fields in a hash
        'HMGET', // key field [field ...] Get the values of all the given hash fields
        'HMSET', // key field value [field value ...] Set multiple hash fields to multiple values
        'HSET', // key field value Set the string value of a hash field
        'HSETNX', // key field value Set the value of a hash field, only if the field does not exist
        'HVALS', // key Get all the values in a hash
        'INCR', // key Increment the integer value of a key by one
        'INCRBY', // key increment Increment the integer value of a key by the given amount
        'INCRBYFLOAT', // key increment Increment the float value of a key by the given amount
        'INFO', // [section] Get information and statistics about the server
        'KEYS', // pattern Find all keys matching the given pattern
        'LASTSAVE', // Get the UNIX time stamp of the last successful save to disk
        'LINDEX', // key index Get an element from a list by its index
        'LINSERT', // key BEFORE|AFTER pivot value Insert an element before or after another element in a list
        'LLEN', // key Get the length of a list
        'LPOP', // key Remove and get the first element in a list
        'LPUSH', // key value [value ...] Prepend one or multiple values to a list
        'LPUSHX', // key value Prepend a value to a list, only if the list exists
        'LRANGE', // key start stop Get a range of elements from a list
        'LREM', // key count value Remove elements from a list
        'LSET', // key index value Set the value of an element in a list by its index
        'LTRIM', // key start stop Trim a list to the specified range
        'MGET', // key [key ...] Get the values of all the given keys
        'MIGRATE', // host port key destination-db timeout Atomically transfer a key from a Redis instance to another one.
        'MONITOR', // Listen for all requests received by the server in real time
        'MOVE', // key db Move a key to another database
        'MSET', // key value [key value ...] Set multiple keys to multiple values
        'MSETNX', // key value [key value ...] Set multiple keys to multiple values, only if none of the keys exist
        'MULTI', // Mark the start of a transaction block
        'OBJECT', // subcommand [arguments [arguments ...]] Inspect the internals of Redis objects
        'PERSIST', // key Remove the expiration from a key
        'PEXPIRE', // key milliseconds Set a key's time to live in milliseconds
        'PEXPIREAT', // key milliseconds-timestamp Set the expiration for a key as a UNIX timestamp specified in milliseconds
        'PING', // Ping the server
        'PSETEX', // key milliseconds value Set the value and expiration in milliseconds of a key
        'PSUBSCRIBE', // pattern [pattern ...] Listen for messages published to channels matching the given patterns
        'PTTL', // key Get the time to live for a key in milliseconds
        'PUBLISH', // channel message Post a message to a channel
        'PUNSUBSCRIBE', // [pattern [pattern ...]] Stop listening for messages posted to channels matching the given patterns
        'QUIT', // Close the connection
        'RANDOMKEY', // Return a random key from the keyspace
        'RENAME', // key newkey Rename a key
        'RENAMENX', // key newkey Rename a key, only if the new key does not exist
        'RESTORE', // key ttl serialized-value Create a key using the provided serialized value, previously obtained using DUMP.
        'RPOP', // key Remove and get the last element in a list
        'RPOPLPUSH', // source destination Remove the last element in a list, append it to another list and return it
        'RPUSH', // key value [value ...] Append one or multiple values to a list
        'RPUSHX', // key value Append a value to a list, only if the list exists
        'SADD', // key member [member ...] Add one or more members to a set
        'SAVE', // Synchronously save the dataset to disk
        'SCARD', // key Get the number of members in a set
        'SCRIPT EXISTS', // script [script ...] Check existence of scripts in the script cache.
        'SCRIPT FLUSH', // Remove all the scripts from the script cache.
        'SCRIPT KILL', // Kill the script currently in execution.
        'SCRIPT LOAD', // script Load the specified Lua script into the script cache.
        'SDIFF', // key [key ...] Subtract multiple sets
        'SDIFFSTORE', // destination key [key ...] Subtract multiple sets and store the resulting set in a key
        'SELECT', // index Change the selected database for the current connection
        'SET', // key value Set the string value of a key
        'SETBIT', // key offset value Sets or clears the bit at offset in the string value stored at key
        'SETEX', // key seconds value Set the value and expiration of a key
        'SETNX', // key value Set the value of a key, only if the key does not exist
        'SETRANGE', // key offset value Overwrite part of a string at key starting at the specified offset
        'SHUTDOWN', // [NOSAVE] [SAVE] Synchronously save the dataset to disk and then shut down the server
        'SINTER', // key [key ...] Intersect multiple sets
        'SINTERSTORE', // destination key [key ...] Intersect multiple sets and store the resulting set in a key
        'SISMEMBER', // key member Determine if a given value is a member of a set
        'SLAVEOF', // host port Make the server a slave of another instance, or promote it as master
        'SLOWLOG', // subcommand [argument] Manages the Redis slow queries log
        'SMEMBERS', // key Get all the members in a set
        'SMOVE', // source destination member Move a member from one set to another
        'SORT', // key [BY pattern] [LIMIT offset count] [GET pattern [GET pattern ...]] [ASC|DESC] [ALPHA] [STORE destination] Sort the elements in a list, set or sorted set
        'SPOP', // key Remove and return a random member from a set
        'SRANDMEMBER', // key [count] Get one or multiple random members from a set
        'SREM', // key member [member ...] Remove one or more members from a set
        'STRLEN', // key Get the length of the value stored in a key
        'SUBSCRIBE', // channel [channel ...] Listen for messages published to the given channels
        'SUNION', // key [key ...] Add multiple sets
        'SUNIONSTORE', // destination key [key ...] Add multiple sets and store the resulting set in a key
        'SYNC', // Internal command used for replication
        'TIME', // Return the current server time
        'TTL', // key Get the time to live for a key
        'TYPE', // key Determine the type stored at key
        'UNSUBSCRIBE', // [channel [channel ...]] Stop listening for messages posted to the given channels
        'UNWATCH', // Forget about all watched keys
        'WATCH', // key [key ...] Watch the given keys to determine execution of the MULTI/EXEC block
        'ZADD', // key score member [score member ...] Add one or more members to a sorted set, or update its score if it already exists
        'ZCARD', // key Get the number of members in a sorted set
        'ZCOUNT', // key min max Count the members in a sorted set with scores within the given values
        'ZINCRBY', // key increment member Increment the score of a member in a sorted set
        'ZINTERSTORE', // destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM|MIN|MAX] Intersect multiple sorted sets and store the resulting sorted set in a new key
        'ZRANGE', // key start stop [WITHSCORES] Return a range of members in a sorted set, by index
        'ZRANGEBYSCORE', // key min max [WITHSCORES] [LIMIT offset count] Return a range of members in a sorted set, by score
        'ZRANK', // key member Determine the index of a member in a sorted set
        'ZREM', // key member [member ...] Remove one or more members from a sorted set
        'ZREMRANGEBYRANK', // key start stop Remove all members in a sorted set within the given indexes
        'ZREMRANGEBYSCORE', // key min max Remove all members in a sorted set within the given scores
        'ZREVRANGE', // key start stop [WITHSCORES] Return a range of members in a sorted set, by index, with scores ordered from high to low
        'ZREVRANGEBYSCORE', // key max min [WITHSCORES] [LIMIT offset count] Return a range of members in a sorted set, by score, with scores ordered from high to low
        'ZREVRANK', // key member Determine the index of a member in a sorted set, with scores ordered from high to low
        'ZSCORE', // key member Get the score associated with the given member in a sorted set
        'ZUNIONSTORE', // destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM|MIN|MAX] Add multiple sorted sets and store the resulting sorted set in a new key
        'GEOADD', // key longitude latitude member [longitude latitude member ...] Add point
        'GEODIST', // key member1 member2 [unit] Return the distance between two members
        'GEOHASH', // key member [member ...] Return valid Geohash strings
        'GEOPOS', // key member [member ...] Return the positions (longitude,latitude)
        'GEORADIUS', // key longitude latitude radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count] Return the members
        'GEORADIUSBYMEMBER', // key member radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count]
    ];

    /**
     * @var resource redis socket connection
     */
    private $_socket = false;


    /**
     * Closes the connection when this component is being serialized.
     * @return array
     */
    public function __sleep()
    {
        $this->close();
        return array_keys(get_object_vars($this));
    }

    /**
     * Returns a value indicating whether the DB connection is established.
     * @return boolean whether the DB connection is established
     */
    public function getIsActive()
    {
        return $this->_socket !== false;
    }

    /**
     * Establishes a DB connection.
     * It does nothing if a DB connection has already been established.
     * @throws Exception if connection fails
     */
    public function open()
    {
        if ($this->_socket !== false) {
            return;
        }
        $connection = ($this->unixSocket ?: $this->hostname . ':' . $this->port) . ', database=' . $this->database;
        \Yii::trace('Opening redis DB connection: ' . $connection, __METHOD__);
        $this->_socket = @stream_socket_client(
            $this->unixSocket ? 'unix://' . $this->unixSocket : 'tcp://' . $this->hostname . ':' . $this->port,
            $errorNumber,
            $errorDescription,
            $this->connectionTimeout ? $this->connectionTimeout : ini_get('default_socket_timeout'),
            $this->socketClientFlags
        );
        if ($this->_socket) {
            if ($this->dataTimeout !== null) {
                stream_set_timeout($this->_socket, $timeout = (int) $this->dataTimeout, (int) (($this->dataTimeout - $timeout) * 1000000));
            }
            if ($this->password !== null) {
                $this->executeCommand('AUTH', [$this->password]);
            }
            if ($this->database !== null) {
                $this->executeCommand('SELECT', [$this->database]);
            }
            $this->initConnection();
        } else {
            \Yii::error("Failed to open redis DB connection ($connection): $errorNumber - $errorDescription", __CLASS__);
            $message = YII_DEBUG ? "Failed to open redis DB connection ($connection): $errorNumber - $errorDescription" : 'Failed to open DB connection.';
            throw new Exception($message, $errorDescription, $errorNumber);
        }
    }

    /**
     * Closes the currently active DB connection.
     * It does nothing if the connection is already closed.
     */
    public function close()
    {
        if ($this->_socket !== false) {
            $connection = ($this->unixSocket ?: $this->hostname . ':' . $this->port) . ', database=' . $this->database;
            \Yii::trace('Closing DB connection: ' . $connection, __METHOD__);
            $this->executeCommand('QUIT');
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
            $this->_socket = false;
        }
    }

    /**
     * Initializes the DB connection.
     * This method is invoked right after the DB connection is established.
     * The default implementation triggers an [[EVENT_AFTER_OPEN]] event.
     */
    protected function initConnection()
    {
        $this->trigger(self::EVENT_AFTER_OPEN);
    }

    /**
     * Returns the name of the DB driver for the current [[dsn]].
     * @return string name of the DB driver
     */
    public function getDriverName()
    {
        return 'redis';
    }

    /**
     * @return LuaScriptBuilder
     */
    public function getLuaScriptBuilder()
    {
        return new LuaScriptBuilder();
    }

    /**
     * Allows issuing all supported commands via magic methods.
     *
     * ```php
     * $redis->hmset(['test_collection', 'key1', 'val1', 'key2', 'val2'])
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
            return $this->executeCommand($name, $params);
        } else {
            return parent::__call($name, $params);
        }
    }

    /**
     * Executes a redis command.
     * For a list of available commands and their parameters see http://redis.io/commands.
     *
     * @param string $name the name of the command
     * @param array $params list of parameters for the command
     * @return array|boolean|null|string Dependent on the executed command this method
     * will return different data types:
     *
     * - `true` for commands that return "status reply" with the message `'OK'` or `'PONG'`.
     * - `string` for commands that return "status reply" that does not have the message `OK` (since version 2.0.1).
     * - `string` for commands that return "integer reply"
     *   as the value is in the range of a signed 64 bit integer.
     * - `string` or `null` for commands that return "bulk reply".
     * - `array` for commands that return "Multi-bulk replies".
     *
     * See [redis protocol description](http://redis.io/topics/protocol)
     * for details on the mentioned reply types.
     * @throws Exception for commands that return [error reply](http://redis.io/topics/protocol#error-reply).
     */
    public function executeCommand($name, $params = [])
    {
        $this->open();

        array_unshift($params, $name);
        $command = '*' . count($params) . "\r\n";
        foreach ($params as $arg) {
            $command .= '$' . mb_strlen($arg, '8bit') . "\r\n" . $arg . "\r\n";
        }

        \Yii::trace("Executing Redis Command: {$name}", __METHOD__);
        fwrite($this->_socket, $command);

        return $this->parseResponse(implode(' ', $params));
    }

    /**
     * @param string $command
     * @return mixed
     * @throws Exception on error
     */
    private function parseResponse($command)
    {
        if (($line = fgets($this->_socket)) === false) {
            throw new Exception("Failed to read from socket.\nRedis command was: " . $command);
        }
        $type = $line[0];
        $line = mb_substr($line, 1, -2, '8bit');
        switch ($type) {
            case '+': // Status reply
                if ($line === 'OK' || $line === 'PONG') {
                    return true;
                } else {
                    return $line;
                }
            case '-': // Error reply
                throw new Exception("Redis error: " . $line . "\nRedis command was: " . $command);
            case ':': // Integer reply
                // no cast to int as it is in the range of a signed 64 bit integer
                return $line;
            case '$': // Bulk replies
                if ($line == '-1') {
                    return null;
                }
                $length = $line + 2;
                $data = '';
                while ($length > 0) {
                    if (($block = fread($this->_socket, $length)) === false) {
                        throw new Exception("Failed to read from socket.\nRedis command was: " . $command);
                    }
                    $data .= $block;
                    $length -= mb_strlen($block, '8bit');
                }

                return mb_substr($data, 0, -2, '8bit');
            case '*': // Multi-bulk replies
                $count = (int) $line;
                $data = [];
                for ($i = 0; $i < $count; $i++) {
                    $data[] = $this->parseResponse($command);
                }

                return $data;
            default:
                throw new Exception('Received illegal data from redis: ' . $line . "\nRedis command was: " . $command);
        }
    }
}
