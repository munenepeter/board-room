<?php

namespace BoardRoom\Controllers;

use BoardRoom\Models\Meeting;

/**
 * Api controller
 *
 * this resends database results in a json parsed way to the front-end
 *
 * @author Peter.Njega <munenenjega@gmail.com>
 *
 */

class ApiController extends Controller {
    public function __construct() {
        $this->middleware('auth');
    }

    public function allMeetings() {
        return $this->json(Meeting::all());
    }
}
