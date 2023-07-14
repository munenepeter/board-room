<?php

namespace BoardRoom\Controllers;
use BoardRoom\Models\Meeting;

class PagesController extends Controller {

    public function __construct() {
         $this->middleware('auth');
       }

    public function index() {
        $meetings =  Meeting::all();
        return view('index',[
            'meetings' => $meetings
        ]);
    }
    public function overview() {
        $meetings =  Meeting::all();
        return view('overview', [
            'meetings' => $meetings
        ]);
    }
    public function MeetingsView(){
        return view('meeting_day');
    }
 
}
