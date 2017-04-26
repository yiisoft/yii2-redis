<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * This class implements the ActiveRecord pattern for the [redis](http://redis.io/) key-value store.
 *
 * For defining a record a subclass should at least implement the [[attributes()]] method to define
 * attributes. A primary key can be defined via [[primaryKey()]] which defaults to `id` if not specified.
 *
 * The following is an example model called `Customer`:
 *
 * ```php
 * class Customer extends \yii\redis\ActiveRecord
 * {
 *     public function attributes()
 *     {
 *         return ['id', 'name', 'address', 'registration_date'];
 *     }
 * }
 * ```
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRecord extends BaseActiveRecord
{
    private $_oldAttributes;
    
    protected $_keyPrefix;
    protected $_PrimaryKey;
    protected $_expireTime;
    protected $_lonlat;

    const TYPE_ID       = 's';
    const TYPE_KEY      = 'a';
    const TYPE_GEO      = 'g';
    const TYPE_EXPIRE   = 'e';
    
    /**
     * Returns the database connection used by this AR class.
     * By default, the "redis" application component is used as the database connection.
     * You may override this method if you want to use a different database connection.
     * @return Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('redis');
    }

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if (!$attributeNames) {
            $attributeNames = $this->safeAttributes();
        }
        return parent::save($runValidation, $attributeNames);
    }    
    
    public function safeAttributes() 
    {
        return null;
    }
    
    /**
     * Returns the primary key name(s) for this AR class.
     * This method should be overridden by child classes to define the primary key.
     *
     * Note that an array should be returned even when it is a single primary key.
     *
     * @return string[] the primary keys of this record.
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * Returns the list of all attribute names of the model.
     * This method must be overridden by child classes to define available attributes.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        throw new InvalidConfigException('The attributes() method of redis ActiveRecord has to be implemented by child classes.');
    }

    /**
     * Declares prefix of the key that represents the keys that store this records in redis.
     * By default this method returns the class name as the table name by calling [[Inflector::camel2id()]].
     * For example, 'Customer' becomes 'customer', and 'OrderItem' becomes
     * 'order_item'. You may override this method if you want different key naming.
     * @return string the prefix to apply to all AR keys
     */
    public static function keyPrefix()
    {
        return Inflector::camel2id(StringHelper::basename(get_called_class()), '_');
    }
    
    /**
     * Return stored keyPrefix for best performance
     * @return type
     */
    public function keyPrefixStored() 
    {
        if (!$this->_keyPrefix) {
            $this->_keyPrefix = static::keyPrefix();
        }
        return $this->_keyPrefix;
    }

    protected function getPKey($prefix, &$values = [])
    {
        $db = static::getDb();
        $pk = [];

        foreach ($this->primaryKey() as $key) {
            $pk[$key] = $values[$key] = $this->getAttribute($key);
            if ($pk[$key] === null) {
                // use auto increment if pk is null
                $pkey = static::buildKeyPrefix($key, $prefix, static::TYPE_ID);
                $pk[$key] = $values[$key] = $db->executeCommand('INCR', [$pkey]);
                $this->setAttribute($key, $pk[$key]);
            } elseif (is_numeric($pk[$key])) {
                // if pk is numeric update auto increment value
                $pkey = static::buildKeyPrefix($key, $prefix, static::TYPE_ID);
                $currentPk = $db->executeCommand('GET', [$pkey]);
                if ($pk[$key] > $currentPk) {
                    $db->executeCommand('SET', [$pkey, $pk[$key]]);
                }
            }
        }
        return static::buildKey($pk);
    }
    
    /**
     * Insert record
     * @param bool $runValidation
     * @param array $attributes
     * @return boolean
     */
    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            return false;
        }
        if (!$this->beforeSave(true)) {
            return false;
        }
        $prefix = $this->keyPrefixStored();
        $db = static::getDb();
        $values = $this->getDirtyAttributes($attributes);

        $key = $this->getPKey($prefix);
        $time = ''; $type = null;
        if ($this->_expireTime) {
            $time = time()+$this->_expireTime;
            $type = static::TYPE_EXPIRE;
        }
        // save unique pk in a findall pool if key isn't expire
        $pool = static::buildKeyPrefix($time, $prefix, $type);
        $db->executeCommand('SADD', [$pool, $key]);

        $this->_PrimaryKey = static::buildKeyPrefix($key, $prefix, static::TYPE_KEY);
        // save attributes
        $setArgs = [$this->_PrimaryKey];
        
        foreach ($values as $attribute => $value) {
            // only insert attributes that are not null
            if ($value !== null) {
                if (is_bool($value)) {
                    $value = (int) $value;
                }
                $setArgs[] = $attribute;
                $setArgs[] = $value;
            }
        }

        if (count($setArgs) > 1) {
            $db->executeCommand('HMSET', $setArgs);
        }
        
        if ($this->_expireTime) {
            $args = [
                'key' => $this->_PrimaryKey,
                'expire' => $this->_expireTime,
            ];
            $db->executeCommand('EXPIRE', $args);
            $args['key'] = $pool;
            $db->executeCommand('EXPIRE', $args);
        }
        
        if ($this->_lonlat) {
            $k = $prefix.':'.static::TYPE_GEO;
            if ($this->_expireTime) {
                $k .= ':'.$time;
            }
            $geo = [
                $k,
                $this->_lonlat['lon'],
                $this->_lonlat['lat'],
                $key,
            ];
            $db->executeCommand('GEOADD', $geo);
            if ($this->_expireTime) {
                $args['key'] = $k;
                $db->executeCommand('EXPIRE', $args);
            }
        }
        

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    /**
     * @see update()
     * @param array $attributes attributes to update
     * @return integer number of rows updated
     * @throws StaleObjectException
     */
    protected function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }
        $pk = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $values[$lock] = $this->$lock + 1;
            $pk[$lock] = $this->$lock;
        }

        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        $keys = static::updatePks($values, [$pk], $this->_expireTime);
        $rows = count($keys);
        if ($rows) {
            $this->_PrimaryKey = reset($keys);
        }

        if ($lock !== null && !$rows) {
            throw new StaleObjectException('The object being updated is outdated.');
        }

        if (isset($values[$lock])) {
            $this->$lock = $values[$lock];
        }

        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
            $this->_oldAttributes[$name] = $value;
        }
        $this->afterSave(false, $changedAttributes);

        return $rows;
    }    
    
    /**
     * Update records by primary keys
     * @param array $attributes attribute values (name-value pairs) to be saved into the table
     * @param array $pks the primary keys
     * @param integer|null $expire time in seconds
     * @return integer the number of rows updated
     */
    protected static function updatePks($attributes, $pks, $expire = null)
    {
        $prefix = static::keyPrefix();
        $db = static::getDb();
        $n = [];

        foreach ($pks as $pk) {
            $newPk = $pk;
            $pk = static::buildKey($pk);
            $key = static::buildKeyPrefix($pk, $prefix, static::TYPE_KEY);
            
            // save attributes
            $delArgs = [$key];
            $setArgs = [$key];
            foreach ($attributes as $attribute => $value) {
                if (isset($newPk[$attribute])) {
                    $newPk[$attribute] = $value;
                }
                if ($value !== null) {
                    if (is_bool($value)) {
                        $value = (int) $value;
                    }
                    $setArgs[] = $attribute;
                    $setArgs[] = $value;
                } else {
                    $delArgs[] = $attribute;
                }
            }
            $newPk = static::buildKey($newPk);
            // rename index if pk changed
            if ($newPk != $pk) {
                $newKey = static::buildKeyPrefix($newPk, $prefix, static::TYPE_KEY);
                static::beginTransaction();
                if (count($setArgs) > 1) {
                    $db->executeCommand('HMSET', $setArgs);
                }
                if (count($delArgs) > 1) {
                    $db->executeCommand('HDEL', $delArgs);
                }
                $db->executeCommand('SADD', [$prefix, $newPk]);
                $db->executeCommand('SREM', [$prefix, 0, $pk]);
                $db->executeCommand('RENAME', [$key, $newKey]);

                static::commitTransaction();
            } else {
                $newKey = $key;
                if (count($setArgs) > 1) {
                    $db->executeCommand('HMSET', $setArgs);
                }
                if (count($delArgs) > 1) {
                    $db->executeCommand('HDEL', $delArgs);
                }
            }
            if ($expire) {
                $args = [
                    'key' => $newPk,
                    'expire' => $expire,
                ];
                $db->executeCommand('EXPIRE', $args);
            }            
            $n[] = $newKey;
        }

        return $n;        
    }

        /**
     * Updates the whole table using the provided attribute values and conditions.
     * For example, to change the status to be 1 for all customers whose status is 2:
     *
     * ~~~
     * Customer::updateAll(['status' => 1], ['id' => 2]);
     * ~~~
     *
     * @param array $attributes attribute values (name-value pairs) to be saved into the table
     * @param array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
     * Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
     * @return integer the number of rows updated
     */
    public static function updateAll($attributes, $condition = null)
    {
        if (empty($attributes)) {
            return 0;
        }
        $pks = static::fetchPks($condition);
        $keys = static::updatePks($attributes, $pks);
        
        return count($keys);
    }

    /**
     * Updates the whole table using the provided counter changes and conditions.
     * For example, to increment all customers' age by 1,
     *
     * ~~~
     * Customer::updateAllCounters(['age' => 1]);
     * ~~~
     *
     * @param array $counters the counters to be updated (attribute name => increment value).
     * Use negative values if you want to decrement the counters.
     * @param array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
     * Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
     * @return integer the number of rows updated
     */
    public static function updateAllCounters($counters, $condition = null)
    {
        if (empty($counters)) {
            return 0;
        }
        $db = static::getDb();
        $n = 0;
        foreach (static::fetchPks($condition) as $pk) {
            $key = static::buildKeyPrefix(static::buildKey($pk), static::keyPrefix(), static::TYPE_KEY);

            foreach ($counters as $attribute => $value) {
                $db->executeCommand('HINCRBY', [$key, $attribute, $value]);
            }
            $n++;
        }

        return $n;
    }

    /**
     * Deletes rows in the table using the provided conditions.
     * WARNING: If you do not specify any condition, this method will delete ALL rows in the table.
     *
     * For example, to delete all customers whose status is 3:
     *
     * ~~~
     * Customer::deleteAll(['status' => 3]);
     * ~~~
     *
     * @param array $condition the conditions that will be put in the WHERE part of the DELETE SQL.
     * Please refer to [[ActiveQuery::where()]] on how to specify this parameter.
     * @return integer the number of rows deleted
     */
    public static function deleteAll($condition = null)
    {
        $pks = static::fetchPks($condition);
        if (empty($pks)) {
            return 0;
        }

        $db = static::getDb();
        $attributeKeys = [];
        static::beginTransaction();
        $prefix = static::keyPrefix();
        foreach ($pks as $pk) {
            $pk = static::buildKey($pk);
            $db->executeCommand('SREM', [$prefix, 0, $pk]);
            $attributeKeys[] = static::buildKeyPrefix($pk, $prefix,  static::TYPE_KEY);
        }
        $db->executeCommand('DEL', $attributeKeys);
        $result = static::commitTransaction();

        return end($result);
    }

    /**
     * Find keys in key pool
     * @param type $condition
     * @return array of keys
     */
    protected static function fetchPks($condition = [])
    {
        $query = static::find();
        if ($condition) {
            $query->where($condition);
        }
        $records = $query->asArray()->all(); // TODO limit fetched columns to pk
        $primaryKey = static::primaryKey();

        $pks = [];
        foreach ($records as $record) {
            $pk = [];
            foreach ($primaryKey as $key) {
                $pk[$key] = $record[$key];
            }
            $pks[] = $pk;
        }

        return $pks;
    }

    /**
     * Builds a normalized key from a given primary key value.
     *
     * @param mixed $key the key to be normalized
     * @return string the generated key
     */
    public static function buildKey($key)
    {
        if (is_numeric($key)) {
            return $key;
        } elseif (is_string($key)) {
            return ctype_alnum($key) && StringHelper::byteLength($key) <= 32 ? $key : md5($key);
        } elseif (is_array($key)) {
            if (count($key) == 1) {
                return self::buildKey(reset($key));
            }
            ksort($key); // ensure order is always the same
            $isNumeric = true;
            foreach ($key as $k => $value) {
                if (!is_numeric($value)) {
                    $isNumeric = false;
                }
                $key[$k] = strval($value);
            }
            if ($isNumeric) {
                return implode('-', $key);
            }
        }

        return md5(json_encode($key));
    }
    
    /**
     * Build key whith key prefix, for example User:a:1
     * @param string $key
     * @param string $prefix 
     * @param type $type
     * @return string 
     */
    public static function buildKeyPrefix($key, $prefix = '', $type = null)
    {
        if (!$prefix) {
            $prefix = static::keyPrefix();
        }
        if (!$type) {
            return $prefix;
        }
        
        return "$prefix:$type:$key";
    }
    
    /**
     * Begin redis transaction
     */
    public static function beginTransaction()
    {
        $db = static::getDb();
        $db->executeCommand('MULTI');
    }

    /**
     * Commit redis transaction
     * @return type
     */
    public static function commitTransaction()
    {
        $db = static::getDb();
        return $db->executeCommand('EXEC');
    }
    
    /**
     * Returns the remaining time to live of a key that has a timeout.
     * @return type
     */
    public function ttl()
    {
        $db = static::getDb();

        if (!$this->_PrimaryKey) {
            $pk = [];
            foreach ($this->primaryKey() as $key) {
                $pk[$key] = $this->getAttribute($key);
            }
            $this->_PrimaryKey = static::buildKeyPrefix(static::buildKey($pk), $this->keyPrefixStored(), static::TYPE_KEY);
        }

        return $db->executeCommand('TTL', [$this->_PrimaryKey]);
    }
    
    /**
     * Set a timeout on key
     * @param int $expire
     */
    public function expire($expire)
    {
        $this->_expireTime = $expire;
    }

    /**
     * Adds the specified geospatial items (latitude, longitude) to the key
     * @param type $params
     */
    public function geo($params)
    {
        $this->_lonlat = $params;
    }
    
    /**
     * Adds the specified geospatial items (latitude, longitude, name) to the specified key
     * @param string $key
     * @param array $members
     */
    public static function geoAdd($key, $members)
    {
        $db = static::getDb();
        $db->executeCommand('GEOADD', array_merge([$key], $members));
    }
    
    /**
     * Return the members of a sorted set populated with geospatial information
     * @param strind $key
     * @param float $lon
     * @param float $lat
     * @param string $radius - the radius for example '1 km'
     * @return type
     */
    public static function findGeoRadius($key, $lon, $lat, $radius)
    {
        $db = static::getDb();
        $params = explode(' ', $radius);
        return $db->executeCommand('GEORADIUS', array_merge([$key, $lon, $lat], $params));
    }
    
    /**
     * Returns all keys matching pattern
     * @param type $mask
     * @return array of keys
     */
    public static function findKeys($mask)
    {
        $db = static::getDb();
        return $db->executeCommand('KEYS', [$mask]);
    }

}
