<?php

namespace BoardRoom\Controllers;

use BoardRoom\Models\Meeting;

class EventsController extends Controller {

    public function __construct() {
         $this->middleware('auth');
       }

    public function book() {
        $meeting = request()->all();
        Meeting::create($meeting); 
        return redirect('/');
    }
 
}
