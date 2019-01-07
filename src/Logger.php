<?php
// namespace RahPT/Logger;
// use DateTime;
// use 

	class ElapsedTag {
		const START   = 'start';
		const FINISH  = 'finish';
		const ELAPSED = 'elapsed';
		const PARTIAL = 'partial';
	}
	
	class ElapsedTask {
	
		protected $_tasks = [];
		
		private function doStart($taskName)
		{
			$this->_tasks[$taskName][ElapsedTag::START] = microtime(true);
		}		
		public function start($taskName, $forced = true)
		{
			if ($forced || empty($this->_tasks[$taskName][ElapsedTag::START])) {
				$this->doStart($taskName);
			}
		}
		private function doStop($taskName) 
		{
			//remove partial
			unset($this->_tasks[$taskName][ElapsedTag::PARTIAL]);
			
			$this->_tasks[$taskName][ElapsedTag::FINISH]     = microtime(true);			
			$this->_tasks[$taskName][ElapsedTag::ELAPSED] = 
				$this->_tasks[$taskName][ElapsedTag::FINISH] 
				- $this->_tasks[$taskName][ElapsedTag::START];
		}
		public function stop($taskName)
		{
			// stop once
			if (! empty($this->_tasks[$taskName][ElapsedTag::FINISH]))
				return;
			$this->doStop($taskName);
		}
		private function getEndTask($taskName)
		{
			if(empty($this->_tasks[$taskName][ElapsedTag::FINISH])) 
			{
				return microtime(true);
			}
			
			return $this->_tasks[$taskName][ElapsedTag::FINISH];
		}
		public function getPartial($taskName) 
		{
			$startTask = $this->_tasks[$taskName][ElapsedTag::START];
			// if have partial, use 
			if (! empty($this->_tasks[$taskName][ElapsedTag::PARTIAL])) {
				$startTask = $this->_tasks[$taskName][ElapsedTag::PARTIAL];
			}
			// refresh partial 
			$endTask = microtime(true);			
			$this->_tasks[$taskName][ElapsedTag::PARTIAL] = $endTask;

			return $endTask - $startTask;
		}

		// get Total elapsed time
		public function getElapsed($taskName)
		{
			$endTask = $this->getEndTask($taskName);
			$startTask = $this->_tasks[$taskName][ElapsedTag::START];
			return $endTask - $startTask;
		}
		
		public function getArrayElapsed($taskName = '') 
		{
			if (! empty($taskName)) {
				return $this->_tasks[$taskName];
			}

			return $this->_tasks;
		}
		
	}
	
	class LogLevel
	{
		const ERROR = 'ERROR';
		const INFO  = 'INFO';
		const DEBUG = 'DEBUG';
	}
	
	class FileLogger {
	
		private $_filename;
		private $_log;
		private $_logThreshold;
		private $_fileHandle;
		
		private $_options = [
			'dateFormat' => 'Y-m-d H:i:s.u',
			'logInFile'  => FALSE
		];

		private $_arrLogLevels = array(
			LogLevel::ERROR     => 0,
			LogLevel::INFO      => 1,
			LogLevel::DEBUG     => 2
		);
		
		public function __construct($filename, $logThreshold = LogLevel::DEBUG, $options = [])
		{
			$this->_logThreshold = $logThreshold;
			$this->_filename = $filename;
			$this->_options = array_merge($this->_options, $options);
			
			if ($this->_options['logInFile']) {
				$this->fileHandle = fopen($this->_filename, 'a+');
			}
		}
		
		public function __destruct() 
		{
			if ($this->_fileHandle) {
				fclose($this->_fileHandle);
			}
		}
		
		private function getMicro($t) 
		{
			return sprintf("%06d",($t - floor($t)) * 1000000);			
		}
		private function getDateMS($t = 0) 
		{
			if ($t ==0) {
				$t = microtime(true);
			}
			$micro = $this->getMicro($t);
			$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
			return $d->format($this->_options['dateFormat']);
		}
		
		private function logData($msgLevel, $msg) 
		{
			// Limit Log Leveling
		    if ($this->_arrLogLevels[$this->_logThreshold] < $this->_arrLogLevels[$msgLevel] ) {
				return;
			}
			
			$logData = sprintf('[%s] [%s] %s', $this->getDateMS(), $msgLevel, $msg) . PHP_EOL;
			
			if (! $this->_options['logInFile']) {
				$this->_log .= $logData;
			} else {
				$this->writeLog($logData);
			}
		}
		
		public function logInfo($message) {
			$this->logData(LogLevel::INFO, $message);
		}
		public function logError($message) {
			$this->logData(LogLevel::ERROR, $message);
		}
		public function logDebug($message, $debugData = null) {
			if (! empty($debugData)) {
				$message = $message . PHP_EOL . var_export($data, true);
			}
			$this->logData(LogLevel::DEBUG, $message);
		}
		
		public function save($filename = '')
		{
			if (! empty($filename)) {
				$this->_filename = $filename;
			}
			
			// Saves just in memory approach
			if ($this->_options['logInFile']) {
				return;
			}
			
			! empty($this->_log) && file_put_contents($this->_filename, $this->_log);
		}
		
		private function writeLog($message)
		{
			if (null !== $this->_fileHandle) {
				if (fwrite($this->_fileHandle, $message) === false) {
					throw new RuntimeException('The log file coul\'d not be written. Please check that appropriate permissions.');
				}
				fflush($this->fileHandle);
			}
		}
	}
	