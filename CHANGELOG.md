Yii Framework 2 redis extension Change Log
==========================================

2.0.6-dev development fork
-----------------------
ATTENTION!!! 
This fork use incompatible storage for key pool (SADD, SCARD, SMEMBERS)

 - Bug #18 Fixed integer-string key bug
 - Bug #20 Fixed findall pool duplicating keys
 - Bug #33 invalid expire time?
 - Enh #74 Added expire time
 - Added expire and geo records
 - optimized code

ActiveRecord:
 - Added consts TYPE_ID, TYPE_KEY, TYPE_GEO and TYPE_EXPIRE for prefixs
 - Method save() get safeAttributes() as $attributeNames parames
 - For best performance added _keyPrefix and _PrimaryKey properties;
 - Overwrite BaseActiveRecord method updateInternal and added static::updatePks. Don't use self::fetchPks() for find key because updateInternal already have [pk]
 - Change RPUSH for SADD for unique findall
 - Added beginTransaction and commitTransaction methods
 - Added ttl method
 - Added expire method
 - Added georadius method
 - Added geoAdd method
 - Added findGeoRadius method
 - Added findKeys method

ActiveQuery
 - Overwrite method createModels for call $model->afterFind() without addition foreach()
 - Union methods all() and one(), clear code
 - Added expire, withExpiring and georadius methods
 - Added getRows for converting redis lists to key->val
 - for best performance added field _PrimaryKey for all records

LuaScriptBuilder
 - optimized code, clear build key for each BuildMethod
 - Added methods geoRadius expire and allpks for build keys and get results
 - Change all keys "key .. ':a:' .. pk" on "pkey"
Cache
 - remove cast to int

2.0.6 under development
-----------------------

- Bug #67: Fixed regression from 2.0.5, reconnecting a closed connection fails (cebe)
- Enh: Optimized find by PK for relational queries and IN condition (cebe, andruha)


2.0.5 March 17, 2016
--------------------

- Bug #22: Fixed string escaping issue in LuaScriptBuilder (vistart)
- Bug #37: Fixed detection of open socket (mirocow)
- Bug #46: Fixed bug to execute session_regenerate_id in PHP 7.0 (githubjeka)
- Enh #31: Added `Connection::$socketClientFlags` property for connection flags to be passed to `stream_socket_client()` (hugh-lee)
- Chg #14: Added missing `BLPOP` command to `$redisCommands` (samdark)
- Chg #61: Added missing `GEO*` commands to `$redisCommands` (leadermt)


2.0.4 May 10, 2015
------------------

- Enh #8: Auto increment value was not updated when a primary key was explicitly set (cebe, andruha)


2.0.3 March 01, 2015
--------------------

- no changes in this release.


2.0.2 January 11, 2015
----------------------

- Bug #6547: Fixed redis connection to deal with large data in combination with `mget()` (pyurin)


2.0.1 December 07, 2014
-----------------------

- Bug #4745: value of simple string returns was ignored by redis client and `true` is returned instead, now only `OK` will result in a `true` while all other values are returned as is (cebe)
- Enh #3714: Added support for connecting to redis server using a unix socket (savvot, robregonm)


2.0.0 October 12, 2014
----------------------

- no changes in this release.


2.0.0-rc September 27, 2014
---------------------------

- Bug #1311: Fixed storage and finding of `null` and boolean values (samdark, cebe)
- Enh #3520: Added `unlinkAll()`-method to active record to remove all records of a model relation (NmDimas, samdark, cebe)
- Enh #4048: Added `init` event to `ActiveQuery` classes (qiangxue)
- Enh #4086: changedAttributes of afterSave Event now contain old values (dizews)


2.0.0-beta April 13, 2014
-------------------------

- Bug #1993: afterFind event in AR is now called after relations have been populated (cebe, creocoder)
- Enh #1773: keyPrefix property of Session and Cache is not restricted to alnum characters anymore (cebe)
- Enh #2002: Added filterWhere() method to yii\redis\ActiveQuery to allow easy addition of search filter conditions by ignoring empty search fields (samdark, cebe)
- Enh #2892: ActiveRecord dirty attributes are now reset after call to `afterSave()` so information about changed attributes is available in `afterSave`-event (cebe)
- Chg #2281: Renamed `ActiveRecord::create()` to `populateRecord()` and changed signature. This method will not call instantiate() anymore (cebe)
- Chg #2146: Removed `ActiveRelation` class and moved the functionality to `ActiveQuery`.
             All relational queries are now directly served by `ActiveQuery` allowing to use
             custom scopes in relations (cebe)

2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
