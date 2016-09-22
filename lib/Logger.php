<?php

class Logger {

	var $logfile;
	var $maxsize;
	var $fileaccess;


	function __construct($logfile, $maxsize="999999999999999999999999999")
	{
		$flag = false;

		/* some filesystem checks if log file exists */
		if( file_exists($logfile) ) {

			/* trying to open file for read */
			if( is_readable($logfile) ) {
				$flag = true;
			} else {
				$flag = false;
			}

			/* trying to open file for write */
			if( is_writeable($logfile) ) {
				$flag = true;
			} else {
				$flag = false;
			}

			/* if log file dont exists do this code */
		} else {

			/* try to create the file */
			$fp = fopen($logfile, "w");
			if($fp>-1) {
				$flag = true;
				fclose($fp);
			} else {
				$flag = false;
			}

		}

		/* flush file contents if filesize exceed maxsize paramter */
		if($flag && $maxsize < filesize($logfile)) {
			unlink($logfile);
			$fp = fopen($logfile, "w");
			fclose($fp);
		}

		/* check for read/write capabilities of logfile */
		if($flag) {
			/* storing internal values */
			$this->logfile = $logfile;
			$this->maxsize = $maxsize;
			$this->fileaccess = "a+";
			return true;
		} else {
			return false;
		}

	}


	/*
	 *  Sends a new line into the log file
	 */
	function write($line) {

		/* open log file */
		$fp = fopen($this->logfile, $this->fileaccess);

		/* get formatted date and client ip */
		$timestamp = "[".date("Y-m-d H:i:s")."] ";

		/* merge infos in one line */
		$line = $timestamp.trim($line)."\n";

		/* dump the new line in the log file */
		$what = fwrite($fp, $line);

		/* close log file */
		fclose($fp);

		/* check for success */
		if( $what )
			return true;
		else
			return false;
	}


}
