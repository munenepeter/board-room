<?php

namespace BoardRoom\Controllers;

class SystemController extends Controller
{

    private $logFile = "Core/Mantle/Logs/logs.log";
    public function __construct()
    {
        //   $this->middleware('auth');
    }
    public function index()
    {

        if (!file_exists($this->logFile)) {
            $newLogFile = fopen($this->logFile, "w") or die("Unable to open file!");

            fclose($newLogFile);

            exit;
        }
        $data = file_get_contents($this->logFile);

        $logs = explode(PHP_EOL, $data);

        array_pop($logs);

        return view('log', [
            'logs' => array_reverse($logs),
        ]);
    }

    public function deleteLogs()
    {
        $this->json("Deleting Logs...");
        if (request('_delete_logs') !== md5(session_get('email'))) {
            logger("Warning", "System: Someone is trying to force delete logs" . session_get('email'));
            return redirect(':system:/logs');
        }
        $this->actuallyDeleteLogs();
    }

    public function actuallyDeleteLogs()
    {

        if (!file_exists($this->logFile)) {
            $newLogFile = fopen($this->logFile, "w") or die("Unable to open file!");
            $this->json("Unable to delete", 500);
            fclose($newLogFile);
            return;
        }

        //delete the file and create a new one
        if(!unlink($this->logFile)){
            logger("Debug", "System: Couldn't delete the logs!");
            $this->json("Unable to delete", 500);
        }
        //recreate the file
        $newLogFile = fopen($this->logFile, "w") or die("Unable to open file!");

        logger("Info", "System: Logs have been deleted by ". session_get('email'));

        fclose($newLogFile);

        $this->json("Logs Deleted");

        return;

    }

}
