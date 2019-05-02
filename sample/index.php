<?php
require_once('src/Logger.php');
use SmartLogs\Logger;

# JSON files
$ajax1 = file_get_contents('sample/json/01-31-2019 13-04-00.json');
$ajax2 = file_get_contents('sample/json/01-31-2019 13-05-00.json');


# new Logger Instance
$a = new Logger();

# Log1
# When logging, the initial data would always be the parsed original data
$log1 = $ajax1;

# PARSING
# (new, old)
# Note: $ajax2 is the data that came from a parsed input sent through HTTP
# which means this request contains raw data and author.
# To create a new log, we must get the difference between the initial data 
# and the data coming from a certain request
$log2 = $a->diff($ajax2, $log1);
var_dump($log2); exit;

# FRAMING
# NOTE: WHEN the diff result is combined with the most recent log ($log1 in this case), 
# we can derive a new set of data containing the PREVIOUS data before the changes occur
$frame = json_encode($a->frame($log2, $log1));
var_dump($frame);