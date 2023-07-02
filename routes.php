<?php

$router->get('', 'PagesController@index');
$router->get('test', 'PagesController@test');
$router->get('login', 'AuthController@index');
$router->post('auth/login', 'AuthController@login');
$router->post('auth/logout', 'AuthController@signout');

$router->post('events/book', 'EventsController@book');

$router->get('events', 'ApiController@allEvents');




//logs
$router->get(':system:/logs', 'SystemController@index');
$router->get(':system:/logs/delete', 'SystemController@deleteLogs');
//robots
$router->get('robots.txt', function (){
    return require __DIR__ ."/robots.txt";
});
