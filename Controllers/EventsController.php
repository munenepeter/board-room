<?php

namespace BoardRoom\Controllers;

class EventsController extends Controller {

    public function __construct() {
         $this->middleware('auth');
       }

    public function book() {
        var_dump(request()->all());
    }
 
}
