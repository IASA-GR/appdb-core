<?php
/**
 * Class Nagios sets states to application errors and
 * creates a readable error file with current 
 * error, state , message and timestamp.
 */
class Nagios {
	/**
	 * clear function deletes previous error file
	 * and logs any process errors
	 *
	 * @param [type] $test	//Application error. 
	 * @return void
	 */
	public static function clear($test) {
		@unlink(APPLICATION_PATH . "/../cache/nagios-" . $test);
		$e = error_get_last();
		if($e['message']!==''){
			error_log("[nagios::clear]". $e)
		}
	}
	/**
	 * set function Creates error file, 
	 * sets the state and message of the error,
	 * documents it and logs any process errors
	 *
	 * @param [type] $test	//Application error.
	 * @param [type] $state	//Application error state.
	 * @param [type] $msg  	//Application error message.
	 * @return void
	 */
	private static function set($test, $state, $msg) {
		$f = @fopen(APPLICATION_PATH . "/../cache/nagios-" . $test, "w");
		$e = error_get_last();
		$date = date(DATE_ISO8601, strtotime(date('m/d/Y H:i:s', time())));
		if($e['message']!==''){
			error_log("[nagios::set]". $e)
		}
		if ( $f !== false ) {
			@fwrite($f, $state . "\n");
	    	@fwrite($date . "\n");
		    @fwrite($f, $msg . "\n");
		    @fclose($f);
		}
	}
	/**
	 * OK error state setter 
	 *
	 * @param [type] $test	//Application error.
	 * @param [type] $msg	//Application error message.
	 * @return void
	 */
	public static function ok($test, $msg) {
		Nagios::set($test, "OK", $msg);
	}
	/**
	 * WARNING error state setter 
	 *
	 * @param [type] $test	//Application error.
	 * @param [type] $msg	//Application error message.
	 * @return void
	 */
	public static function warning($test, $msg) {
		Nagios::set($test, "WARNING", $msg);
	}
	/**
	 * CRITICAL error state setter 
	 *
	 * @param [type] $test	//Application error.
	 * @param [type] $msg	//Application error message.
	 * @return void
	 */
	public static function critical($test, $msg) {
		Nagios::set($test, "CRITICAL", $msg);
	}
	/**
	 * UNKNOWN error state setter
	 *
	 * @param [type] $test	//Application error.
	 * @param [type] $msg	//Application error message.
	 * @return void
	 */ 
	public static function unknown($test, $msg) {
		Nagios::set($test, "UNKNOWN", $msg);
	}
}
?>
