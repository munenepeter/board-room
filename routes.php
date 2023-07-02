<?php

$router->get('', 'PagesController@index');
$router->get('test', 'AuthController@test');
$router->get('login', 'AuthController@index');
$router->post('auth/login', 'AuthController@login');
$router->post('auth/logout', 'AuthController@signout');

$router->post('events/book', 'EventsController@book');

$router->get('events', 'ApiController@allEvents');




//logs
$router->get(':a:/logs', 'SystemController@index');
//robots
$router->get('robots.txt', function (){
    return require __DIR__ ."/robots.txt";
});
