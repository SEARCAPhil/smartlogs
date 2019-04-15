<?php
require_once('Logger.php');
require_once('SampleJson.php');

use SmartLogs\Logger;


$a = new Logger();
$a->write($json, $json2)->print();