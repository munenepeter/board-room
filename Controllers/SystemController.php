<?php

namespace BoardRoom\Controllers;

class SystemController extends Controller {

    private $logFile = "Core/Mantle/Logs/logs.log";
    public function __construct() {
     //   $this->middleware('auth');
    }
    public function index() {
        
        if (!file_exists($this->logFile)) {
            $newLogFile = fopen($this->logFile, "w") or die("Unable to open file!");

            fclose($newLogFile);
    
            exit;
        }
        $data = file_get_contents($this->logFile);

        $logs = explode(PHP_EOL, $data);

        array_pop($logs);

        return view('log', [
            'logs' => array_reverse($logs)
        ]);
    }

    public function deleteLogs(){

        if (!file_exists($this->logFile)) {
            $newLogFile = fopen($this->logFile, "w") or die("Unable to open file!");

            fclose($newLogFile);
    
            exit;
        }

        //delete the file and create a new one

        unlink($this->logFile);
        //recreate the file
        $newLogFile = fopen($this->logFile, "w") or die("Unable to open file!");

        fclose($newLogFile);

        exit;

    }

}
