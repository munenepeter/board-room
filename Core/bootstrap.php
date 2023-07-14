<?php

use BoardRoom\Core\Mantle\App;
use BoardRoom\Core\Database\Connection;
use BoardRoom\Core\Database\QueryBuilder; 

//change TimeZone
date_default_timezone_set('Africa/Nairobi'); 
//production development
define('ENV','production');

//require all files here
require 'helpers.php';


require_once __DIR__.'/../vendor/autoload.php';


//configure config to always point to config.php
App::bind('config', require 'config.php'); 

session_start();

$database = (is_dev()) ? App::get('config')['sqlite'] : App::get('config')['mysql'];

/**
 *Bind the Database credentials and connect to the app
 *Bind the requred database file above to 
 *an instance of the connections
*/

App::bind('database', new QueryBuilder(Connection::make($database)));


