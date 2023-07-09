<?php

namespace BoardRoom\Controllers;
use BoardRoom\Models\Meeting;

class PagesController extends Controller {

    public function __construct() {
         $this->middleware('auth');
       }

    public function index() {
        return view('index');
    }
    public function test() {
        $meetings =  Meeting::all();
        return view('test', [
            'meetings' => $meetings
        ]);
    }
 
}
