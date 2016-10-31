<?php

class Logger {

    var $logfile;
    var $fileaccess;

    function __construct($logfile)
    {
        $this->logfile = $logfile;

        if(file_exists($logfile) && is_writeable($logfile))
        {
            $this->fileaccess = "a+";
        } else {
            $this->fileaccess = "w+";
        }

    }


    /*
     *  Sends a new line into the log file
     */
    function write($line) {

        /* open log file */
        $fp = fopen($this->logfile, $this->fileaccess);

        /* merge infos in one line */
        $line = "[".date("Y-m-d H:i:s")."][".$_SERVER['REMOTE_ADDR']."] ".trim($line)."\n";

        /* dump the new line in the log file */
        $done = fwrite($fp, $line);

        /* close log file */
        fclose($fp);

        /* check for success */
        if( $done )
            return true;
        else
            return false;
    }


}
