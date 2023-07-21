<?php

$router->get('', 'PagesController@index');
$router->get('overview', 'PagesController@overview');
$router->get('meetings', 'PagesController@meetings');
$router->get('meetings/view', 'PagesController@MeetingsView');

$router->get('login', 'AuthController@index');
$router->post('auth/login', 'AuthController@login');
$router->post('auth/logout', 'AuthController@signout');

$router->post('events/book', 'EventsController@book');

$router->get('api/v1/meetings', 'ApiController@allMeetings');




//logs
$router->get(':system:/logs', 'SystemController@index');
$router->post(':system:/logs/delete', 'SystemController@deleteLogs');
//robots
$router->get('robots.txt', function (){
    return require __DIR__ ."/robots.txt";
});
