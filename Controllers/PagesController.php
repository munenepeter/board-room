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
        $meetings =  Meeting::upcoming();
        return view('overview', [
            'meetings' => $meetings
        ]);
    }
    public function meetings(){
        $meetings =  Meeting::all();
        return view('meetings',[
            'meetings' => $meetings
        ]);
    }
    public function MeetingsView(){
        return view('meeting_day');
    }
 
}
