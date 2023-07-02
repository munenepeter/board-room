<?php

namespace BoardRoom\Models;

use BoardRoom\Core\Mantle\App;
use BoardRoom\Models\Model;


class Meeting extends Model{

    public static function all(){
        return App::get('database')->query(   
        "SELECT meetings.id, meetings.employee_no as owner, meeting_types.type as type, meeting_details.name as name, meeting_details.duration as duration, meeting_details.date as date, meetings.created_at
        FROM meetings
        JOIN meeting_types ON meetings.meeting_type_id = meeting_types.id
        JOIN meeting_details ON meetings.meeting_details_id = meeting_details.id;
        ");
    }

    // public static function create(){
    //     return App::get('database')->query();
    // }
    
}