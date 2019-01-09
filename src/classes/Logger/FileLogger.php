<?php

namespace Logger;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Description of newPHPClass
 *
 * @author jneto
 */
class LogLevel
{
    const NONE  = 'DISABLED';
    const ERROR = 'ERROR';
    const INFO  = 'INFO';
    const DEBUG = 'DEBUG';

}

class FileLogger
{
    const DEFAULT_TIMEZONE = 'defaultTimezone';

    private $_filename;
    private $_log;
    private $_logThreshold;
    private $_options      = array(
        'dateFormat' => 'Y-m-d H:i:s.u',
        self::DEFAULT_TIMEZONE => 'UTC'
    );
    private $_arrLogLevels = array(
        LogLevel::NONE => -1,
        LogLevel::ERROR => 0,
        LogLevel::INFO => 1,
        LogLevel::DEBUG => 2
    );

    public function __construct($filename, $logThreshold = LogLevel::DEBUG,
                                $options = [])
    {
        $this->_logThreshold = $logThreshold;
        $this->_filename     = $filename;

        $this->_options[self::DEFAULT_TIMEZONE] = date_default_timezone_get();
        $this->_options                         = array_merge($this->_options,
            $options);
    }

    /**
     * Returns string with Date
     *
     * @param float $microtime
     * @return string
     */
    private function getDateMS($microtime = 0)
    {
        if ($microtime == 0) {
            $microtime = microtime(true);
        }
        $microsec   = sprintf("%06d", ($microtime - floor($microtime)) * 1000000);
        $dateMs     = date('Y-m-d H:i:s.'.$microsec, $microtime);
        $timezone   = new DateTimeZone($this->_options[self::DEFAULT_TIMEZONE]);
        $date       = new DateTime($dateMs, $timezone);
        $dateFormat = $this->_options['dateFormat'];
        return $date->format($dateFormat);
    }

    private function logData($msgLevel, $msg)
    {
        // Limit Log Leveling
        if ($this->_arrLogLevels[$this->_logThreshold] < $this->_arrLogLevels[$msgLevel]) {
            return;
        }

        $logData = sprintf('[%s] [%s] %s', $this->getDateMS(), $msgLevel, $msg).PHP_EOL;

        $this->_log .= $logData;
    }

    public function logInfo($message)
    {
        $this->logData(LogLevel::INFO, $message);
    }

    public function logError($message)
    {
        $this->logData(LogLevel::ERROR, $message);
    }

    public function logDebug($message, $debugData = null)
    {
        if (!empty($debugData)) {
            $message = $message.PHP_EOL.var_export($debugData, true);
        }
        $this->logData(LogLevel::DEBUG, $message);
    }

    public function save($filename = '')
    {
        if (!empty($filename)) {
            $this->_filename = $filename;
        }
        !empty($this->_log) && file_put_contents($this->_filename, $this->_log);
    }
}