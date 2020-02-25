<?php

class Nagios {
	//Deletes file and logs error if any
	public static function clear($test) {
		@unlink(APPLICATION_PATH . "/../cache/nagios-" . $test);
		$e = error_get_last();
		if($e['message']!==''){
			// An error occurred
			error_log("[nagios::clear]". $e)
		}
	}
	//Creates error file, sets the state and message of the error and logs error if any
	private static function set($test, $state, $msg) {
		$f = @fopen(APPLICATION_PATH . "/../cache/nagios-" . $test, "w");
		$e = error_get_last();
		if($e['message']!==''){
			// An error occurred
			error_log("[nagios::set]". $e)
		}
		@fwrite($f, $state . "\n");
		@fwrite($f, $msg . "\n");
		@fclose($f);
	}
	//OK error state setter 
	public static function ok($test, $msg) {
		Nagios::set($test, "OK", $msg);
	}
	//WARNING error state setter 
	public static function warning($test, $msg) {
		Nagios::set($test, "WARNING", $msg);
	}
	//CRITICAL error state setter 
	public static function critical($test, $msg) {
		Nagios::set($test, "CRITICAL", $msg);
	}
	//UNKNOWN error state setter 
	public static function unknown($test, $msg) {
		Nagios::set($test, "UNKNOWN", $msg);
	}
}
?>
