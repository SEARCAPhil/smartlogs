<?php
require_once('src/Logger.php');
use SmartLogs\Logger;

# JSON files
$file1 = file_get_contents('sample/json/01-31-2019 13-04-00.json');
$file2 = file_get_contents('sample/json/01-31-2019 13-05-00.json');


# new Logger Instance
$a = new Logger();

# parsing
# (new, old)
$a->diff($file2, $file1);
#$a->unsigned()->json();

# inpect and convert data to JSON
# This is a sample result from comparing two different JSON
# and will be the SECOND smartLog since the FIRST log is the initial data
$log1 = $file1;
$log2 = $a->payload; // results from changing something by a certain user
 #var_dump($log2);
# NOTE: WHEN log1 and log2 is combined, the result must be equivalent to the 2nd data / file
$decoded_file1 = (array) (json_decode($file1)->data);
var_dump(array_replace_recursive($decoded_file1, $log2));


$a1 = array();
$a1[0] = 'c';

$a2 = array('0' => 'a', '1'=> 'b');

$a3 = new \StdClass;
$a3->{0} = 'x';

#var_dump(array_replace_recursive($a2, (array)$a3));
#var_dump((array) $a3);

