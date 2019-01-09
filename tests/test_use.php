<?php
require_once '../vendor/autoload.php';

use Logger\FileLogger;

const LOG_FILENAME = 'test.log';
$log = new FileLogger(LOG_FILENAME);

$log->logInfo('Start event.');
$arr = [];
for ($i = 0; $i < 4; ++$i) {
    /** in microseconds 1*10^6 */
    $pausePeriod = random_int(0, 2000000);
    $arr[] = sprintf('%s s', $pausePeriod/1000000);
    usleep($pausePeriod);
    $log->logInfo('Log event: '.$i);
}
$log->logDebug('Just a debug of pauses', $arr);
$log->logInfo('End event.');
$log->save();

echo file_get_contents(LOG_FILENAME);
unlink(LOG_FILENAME);
