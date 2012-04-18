<?php 
App::uses('FileLog', 'Log/Engine');
/**
 * Extended File Log
 *
 * I disliked the fact that I had to manually add __FILE__ an __LINE__ to the
 * log and wanted to automatically get the file and line logged for each log()
 * call. Here we go.
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class ExtendedFileLog extends FileLog {
/**
 * Implements writing to log files.
 *
 * @param string $type The type of log you are making.
 * @param string $message The message you want to log.
 * @return boolean success of write.
 */
	public function write($type, $message) {
		$trace = debug_backtrace();
		if (isset($trace[2])) {
			parent::write($type, $trace[2]['file'] . ' Line: ' . $trace[2]['line']);
		}
		parent::write($type, $message);
	}

}
