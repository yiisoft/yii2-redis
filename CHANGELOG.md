Yii Framework 2 redis extension Change Log
==========================================

2.0.18 September 04, 2022
-------------------------

- Enh #249 Added support to set the redis scheme to tls. Add to configuration: `'scheme' => 'tls'` (tychovbh)
- Bug #247: `Cache::getValue()` now returns `false` in case of missing key (rhertogh)


2.0.17 January 11, 2022
-----------------------

- Enh #176: Fix reconnect logic bug, add `protected function sendRawCommand()` (ilyaplot)


2.0.16 October 04, 2021
-----------------------

- Enh #223: Add `Connection::$username` for using username for authentication (samdark, rvkulikov)


2.0.15 May 05, 2021
-------------------

- Enh #227: Added support for adjusting PHP context options and parameters. This allows e.g. supporting self-signed certificates (akselikap)


2.0.14 November 10, 2020
------------------------

- Bug #215: Fix `Connection::isActive()` returns `false` when the connection is active (cornernote)
- Enh #212: Added support for the 'session.use_strict_mode' ini directive in `yii\web\Session` (rhertogh)


2.0.13 May 02, 2020
-------------------

- Enh #210: Add Redis 5.0 stream commands. Read more at [streams intro](https://redis.io/topics/streams-intro) (sartor)


2.0.12 March 13, 2020
---------------------

- Bug #182: Better handle `cache/flush-all` command when cache component is using shared database (rob006)
- Bug #190: Accept null values (razonyang)
- Bug #191: getIsActive() returns true when socket is not connected (mdx86)
- Enh #174: Add ability to set up SSL connection (kulavvy)
- Enh #195: Use `Instance::ensure()` to initialize `Session::$redis` (rob006)
- Enh #199: Increase frequency of lock tries when `$timeout` is used in `Mutex::acquire()` (rob006)


2.0.11 November 05, 2019
------------------------

- Enh #66, #134, #135, #136, #142, #143, #147: Support Redis in cluster mode (hofrob)


2.0.10 October 22, 2019
-----------------------

- Enh #188: Added option to wait between connection retry (marty-macfly, rob006)


2.0.9 September 23, 2018
------------------------

- Bug #166: zrangebyscore without scores does not work (razonyang)
- Enh #13: Added `>`, `<`, `>=` and `<=` conditions support in ActiveQuery (nailfor, zacksleo)


2.0.8 March 20, 2018
--------------------

- Bug #141: Calling ActiveQuery::indexBy() had no effect since Yii 2.0.14 (cebe)
- Bug: (CVE-2018-8073): Fix possible remote code execution when improperly filtered user input is passed to `ActiveRecord::findOne()` and `::findAll()` (cebe)
- Enh #66: Cache component can be configured to read / get from replicas (ryusoft)


2.0.7 December 11, 2017
-----------------------

- Bug #114: Fixed ActiveQuery `not between` and `not` conditions which where not working correctly (cebe, ak1987)
- Bug #123: Fixed ActiveQuery to work with negative limit values, which are used in ActiveDataProvider for the count query (cebe)
- Enh #9: Added orderBy support to redis ActiveQuery and LuaScriptBuilder (valinurovam)
- Enh #91: Added option to retry connection after failing to communicate with redis server on stale socket (cebe)
- Enh #106: Improved handling of connection errors and introduced `yii\redis\SocketException` for these (cebe)
- Chg #127: Added PHP 7.2 compatibility (brandonkelly)


2.0.6 April 05, 2017
--------------------

- Bug #44: Remove quotes from numeric parts of composite key to avoid problem with different hashes for the same record (uniserpl)
- Bug #67: Fixed regression from 2.0.5, reconnecting a closed connection fails (cebe)
- Bug #82: Fixed session object destruction failure when key expires (juffin-halli, samdark)
- Bug #93: Fixed `yii\redis\ActiveRecord::deleteAll()` with condition (samdark)
- Bug #104: Fixed execution of two-word commands (cebe,branimir93)
- Enh #53: Added `Mutex` that implements a Redis based mutex (turboezh, sergeymakinen)
- Enh #81: Allow setting `Connection::$database` to `null` to avoid sending a `SELECT` command after connection (cebe)
- Enh #89: Added support for `\yii\db\QueryInterface::emulateExecution()` (samdark)
- Enh #103: Added missing commands and `@method` documentation for redis commands (cebe)
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
