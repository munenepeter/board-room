<?php

$router->get('board-room/', 'PagesController@index');
$router->get('board-room/overview', 'PagesController@overview');
$router->get('board-room/meetings', 'PagesController@meetings');
$router->get('board-room/meetings/view', 'PagesController@MeetingsView');

$router->get('board-room/login', 'AuthController@index');
$router->post('board-room/auth/login', 'AuthController@login');
$router->post('board-room/auth/logout', 'AuthController@signout');

$router->post('board-room/events/book', 'EventsController@book');

$router->get('board-room/api/v1/meetings', 'ApiController@allMeetings');




//logs
$router->get('board-room/:system:/logs', 'SystemController@index');
$router->post('board-room/:system:/logs/delete', 'SystemController@deleteLogs');
//robots
$router->get('robots.txt', function (){
    return require __DIR__ ."/robots.txt";
});
