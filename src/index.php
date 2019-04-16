<?php
require_once('SmartLogs.php');
require_once('SampleJson.php');

use SmartLogs\Logger;


$a = new Logger();
$a->write($json2, $json);
