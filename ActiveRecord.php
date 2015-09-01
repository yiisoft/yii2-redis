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
    /**
     * Returns the database connection used by this AR class.
     * By default, the "redis" application component is used as the database connection.
     * You may override this method if you want to use a different database connection.
     * @return Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('redis');
    }

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
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
     * @inheritdoc
     */
    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            return false;
        }
        if (!$this->beforeSave(true)) {
            return false;
        }
        $db = static::getDb();
        $values = $this->getDirtyAttributes($attributes);
        $pk = [];
        foreach ($this->primaryKey() as $key) {
            $pk[$key] = $values[$key] = $this->getAttribute($key);
            if ($pk[$key] === null) {
                // use auto increment if pk is null
                $pk[$key] = $values[$key] = $db->executeCommand('INCR', [static::keyPrefix() . ':s:' . $key]);
                $this->setAttribute($key, $values[$key]);
            } elseif (is_numeric($pk[$key])) {
                // if pk is numeric update auto increment value
                $currentPk = $db->executeCommand('GET', [static::keyPrefix() . ':s:' . $key]);
                if ($pk[$key] > $currentPk) {
                    $db->executeCommand('SET', [static::keyPrefix() . ':s:' . $key, $pk[$key]]);
                }
            }
        }
        // save pk in a findall pool
        $db->executeCommand('HSET', [static::keyPrefix(), static::buildKey($pk), 0]);

        $key = static::keyPrefix() . ':a:' . static::buildKey($pk);
        // save attributes
        $setArgs = [$key];
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

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
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
        $db = static::getDb();
        $n = 0;
        foreach (self::fetchPks($condition) as $pk) {
            $newPk = $pk;
            $pk = static::buildKey($pk);
            $key = static::keyPrefix() . ':a:' . $pk;
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
            $newKey = static::keyPrefix() . ':a:' . $newPk;
            // rename index if pk changed
            if ($newPk != $pk) {
                $db->executeCommand('MULTI');
                if (count($setArgs) > 1) {
                    $db->executeCommand('HMSET', $setArgs);
                }
                if (count($delArgs) > 1) {
                    $db->executeCommand('HDEL', $delArgs);
                }
                $db->executeCommand('HSET', [static::keyPrefix(), $newPk, 0]);
                $db->executeCommand('HDEL', [static::keyPrefix(), $pk]);
                $db->executeCommand('RENAME', [$key, $newKey]);
                $db->executeCommand('EXEC');
            } else {
                if (count($setArgs) > 1) {
                    $db->executeCommand('HMSET', $setArgs);
                }
                if (count($delArgs) > 1) {
                    $db->executeCommand('HDEL', $delArgs);
                }
            }
            $n++;
        }

        return $n;
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
        foreach (self::fetchPks($condition) as $pk) {
            $key = static::keyPrefix() . ':a:' . static::buildKey($pk);
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
        $pks = self::fetchPks($condition);
        if (empty($pks)) {
            return 0;
        }

        $db = static::getDb();
        $attributeKeys = [];
        $db->executeCommand('MULTI');
        foreach ($pks as $pk) {
            $pk = static::buildKey($pk);
            $db->executeCommand('HDEL', [static::keyPrefix(), $pk]);
            $attributeKeys[] = static::keyPrefix() . ':a:' . $pk;
        }

        $db->executeCommand('DEL', $attributeKeys);
        $result = $db->executeCommand('EXEC');

        return end($result);
    }

    private static function fetchPks($condition)
    {
        $query = static::find();
        $query->where($condition);
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
            foreach ($key as $value) {
                if (!is_numeric($value)) {
                    $isNumeric = false;
                }
            }
            if ($isNumeric) {
                return implode('-', $key);
            }
        }

        return md5(json_encode($key, JSON_NUMERIC_CHECK));
    }

    /**
     * Returns the attribute values that have been modified since they are loaded or saved most recently.
     * @param string[]|null $names the names of the attributes whose values may be returned if they are
     * changed recently. If null, [[attributes()]] will be used.
     * @return array the changed attribute values (name-value pairs)
     */
    public function getDirtyAttributes($names = null)
    {
        if ($names === null) {
            $names = $this->attributes();
        }
        $names = array_flip($names);
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            if (isset($names[$name]) && (!array_key_exists($name, $this->oldAttributes) || $this->isAttributeChanged($name))) {
                $attributes[$name] = $value;
            }
        }
        return $attributes;
    }

    /**
     * Returns a value indicating whether the named attribute has been changed.
     * @param string $name the name of the attribute.
     * @param bool $identical whether the comparison of new and old value is made for
     * identical values using `===`, defaults to `true`. Otherwise `==` is used for comparison.
     * This parameter is available since version 2.0.4.
     * @return bool whether the attribute has been changed
     */
    public function isAttributeChanged($name, $identical = true)
    {
        if (isset($this->attributes[$name], $this->oldAttributes[$name])) {
            if ($identical) {
                if (is_bool($newValue = $this->attributes[$name])) {
                    $newValue = (int)$this->attributes[$name];
                }
                if (is_bool($oldValue = $this->oldAttributes[$name])) {
                    $oldValue = (int)$this->oldAttributes[$name];
                }

                if (is_array($newValue)) {
                    $newValue = serialize($newValue);
                }
                if (is_array($oldValue)) {
                    $oldValue = serialize($oldValue);
                }

                return (string)$newValue !== (string)$oldValue;
            } else {
                return $this->attributes[$name] != $this->oldAttributes[$name];
            }
        } else {
            return isset($this->attributes[$name]) || isset($this->oldAttributes[$name]);
        }
    }

    /**
     * Populates an active record object using a row of data from the database/storage.
     *
     * This is an internal method meant to be called to create active record objects after
     * fetching data from the database. It is mainly used by [[ActiveQuery]] to populate
     * the query results into active records.
     *
     * When calling this method manually you should call [[afterFind()]] on the created
     * record to trigger the [[EVENT_AFTER_FIND|afterFind Event]].
     *
     * @param BaseActiveRecord $record the record to be populated. In most cases this will be an instance
     * created by [[instantiate()]] beforehand.
     * @param array $row attribute values (name => value)
     */
    public static function populateRecord($record, $row)
    {
        foreach ($record->attributes() as $name) {
            $record->setAttribute($name, isset($row[$name]) ? $row[$name] : null);
            if (isset($row[$name]) && $record->canSetProperty($name)) {
                $record->$name = $row[$name];
            }
        }
        $record->setOldAttributes($record->attributes);
    }
}
