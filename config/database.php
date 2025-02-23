<?php

use Illuminate\Support\Str;

return [

  /*
  |--------------------------------------------------------------------------
  | Default Database Connection Name
  |--------------------------------------------------------------------------
  |
  | Here you may specify which of the database connections below you wish
  | to use as your default connection for database operations. This is
  | the connection which will be utilized unless another connection
  | is explicitly specified when you execute a query / statement.
  |
  */

  'default' => env('DB_CONNECTION', 'jo'),

  /*
  |--------------------------------------------------------------------------
  | Database Connections
  |--------------------------------------------------------------------------
  |
  | Below are all of the database connections defined for your application.
  | An example configuration is provided for each database system which
  | is supported by Laravel. You're free to add / remove connections.
  |
  */
  'connections' => [

    // قاعدة البيانات الرئيسية (Jordan)
    'jo' => [
      'driver' => 'mysql',
      'url' => env('DB_URL'),
      'host' => env('DB_HOST', '127.0.0.1'),
      'port' => env('DB_PORT', '3306'),
      'database' => env('DB_DATABASE', 'JO_data'),
      'username' => env('DB_USERNAME', 'root'),
      'password' => env('DB_PASSWORD', ''),
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'strict' => true,
      'engine' => null,
      'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
      ]) : [],
    ],

    // قاعدة البيانات الفرعية 1 (Saudi Arabia)
    'sa' => [
      'driver' => 'mysql',
      'host' => env('DB_HOST_sa', '127.0.0.1'),
      'port' => env('DB_PORT_sa', '3306'),
      'database' => env('DB_DATABASE_sa', 'SA_data'),
      'username' => env('DB_USERNAME_sa', 'root'),
      'password' => env('DB_PASSWORD_sa', ''),
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'strict' => true,
      'engine' => null,
      'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
      ]) : [],
    ],

    // قاعدة البيانات الفرعية 2 (Egypt)
    'eg' => [
      'driver' => 'mysql',
      'host' => env('DB_HOST_eg', '127.0.0.1'),
      'port' => env('DB_PORT_eg', '3306'),
      'database' => env('DB_DATABASE_eg', 'EG_data'),
      'username' => env('DB_USERNAME_eg', 'root'),
      'password' => env('DB_PASSWORD_eg', ''),
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'strict' => true,
      'engine' => null,
      'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
      ]) : [],
    ],

    // قاعدة البيانات الفرعية 3 (Palestine)
    'ps' => [
      'driver' => 'mysql',
      'host' => env('DB_HOST_ps', '127.0.0.1'),
      'port' => env('DB_PORT_ps', '3306'),
      'database' => env('DB_DATABASE_ps', 'PS_data'),
      'username' => env('DB_USERNAME_ps', 'root'),
      'password' => env('DB_PASSWORD_ps', ''),
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'strict' => true,
      'engine' => null,
      'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
      ]) : [],
    ],


  ],

  /*
  |--------------------------------------------------------------------------
  | Migration Repository Table
  |--------------------------------------------------------------------------
  |
  | This table keeps track of all the migrations that have already run for
  | your application. Using this information, we can determine which of
  | the migrations on disk haven't actually been run on the database.
  |
  */

  'migrations' => [
    'table' => 'migrations',
    'update_date_on_publish' => true,
  ],

  /*
  |--------------------------------------------------------------------------
  | Redis Databases
  |--------------------------------------------------------------------------
  |
  | Redis is an open source, fast, and advanced key-value store that also
  | provides a richer body of commands than a typical key-value system
  | such as Memcached. You may define your connection settings here.
  |
  */

  'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),

    'options' => [
      'cluster' => env('REDIS_CLUSTER', 'redis'),
      'prefix' => env('REDIS_PREFIX', ''),
    ],

    'default' => [
      'url' => env('REDIS_URL'),
      'host' => env('REDIS_HOST', '127.0.0.1'),
      'username' => env('REDIS_USERNAME'),
      'password' => env('REDIS_PASSWORD'),
      'port' => env('REDIS_PORT', '6379'),
      'database' => env('REDIS_DB', '0'),
    ],

    'jo_redis' => [
      'url' => env('REDIS_URL'),
      'host' => env('REDIS_HOST', '127.0.0.1'),
      'username' => env('REDIS_USERNAME'),
      'password' => env('REDIS_PASSWORD'),
      'port' => env('REDIS_PORT', '6379'),
      'database' => '1',
      'prefix' => 'jo:',
    ],

    'sa_redis' => [
      'url' => env('REDIS_URL'),
      'host' => env('REDIS_HOST', '127.0.0.1'),
      'username' => env('REDIS_USERNAME'),
      'password' => env('REDIS_PASSWORD'),
      'port' => env('REDIS_PORT', '6379'),
      'database' => '2',
      'prefix' => 'sa:',
    ],

    'eg_redis' => [
      'url' => env('REDIS_URL'),
      'host' => env('REDIS_HOST', '127.0.0.1'),
      'username' => env('REDIS_USERNAME'),
      'password' => env('REDIS_PASSWORD'),
      'port' => env('REDIS_PORT', '6379'),
      'database' => '3',
      'prefix' => 'eg:',
    ],

    'ps_redis' => [
      'url' => env('REDIS_URL'),
      'host' => env('REDIS_HOST', '127.0.0.1'),
      'username' => env('REDIS_USERNAME'),
      'password' => env('REDIS_PASSWORD'),
      'port' => env('REDIS_PORT', '6379'),
      'database' => '4',
      'prefix' => 'ps:',
    ],

    'cache' => [
      'url' => env('REDIS_URL'),
      'host' => env('REDIS_HOST', '127.0.0.1'),
      'username' => env('REDIS_USERNAME'),
      'password' => env('REDIS_PASSWORD'),
      'port' => env('REDIS_PORT', '6379'),
      'database' => env('REDIS_CACHE_DB', '5'),
    ],

    'session' => [
      'url' => env('REDIS_URL'),
      'host' => env('REDIS_HOST', '127.0.0.1'),
      'username' => env('REDIS_USERNAME'),
      'password' => env('REDIS_PASSWORD'),
      'port' => env('REDIS_PORT', '6379'),
      'database' => env('REDIS_SESSION_DB', '6'),
    ],
  ],

];
