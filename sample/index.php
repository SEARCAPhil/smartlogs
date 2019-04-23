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
$a->diff($file1, $file2);
#$a->unsigned()->json();

# inpect and convert data to JSON
# This is a sample result from comparing two different JSON
# and will be the SECOND smartLog since the FIRST log is the initial data
$log1 = $file1;
$log2 = $a->payload; // results from changing something by a certain user
 #var_dump($log2);
 

# NOTE: WHEN log1 and log2 is combined, the result must be equivalent to the 2nd data / file
$decoded_file1 = (array) (json_decode($file1)->data);
#$mergedLog = $a->merge($log2, $decoded_file1);
#var_dump($mergedLog);
#var_dump($log2);
var_dump(array_merge_recursive_distinct($log2, $decoded_file1));
#var_dump($decoded_file1);

$a1 = array();
$a1[0] = 'c';

$a2 = array('0' => 'a', '1'=> 'b');

$a3 = new \StdClass;
$a3->{0} = 'x';

#var_dump(array_replace_recursive($a2, (array)$a3));
#var_dump((array) $a3);

function array_merge_recursive_distinct($array1, $array2 = null)
{
  $merged = $array1;
  
  if (is_array($array2))
    foreach ($array2 as $key => $val)
      if (is_array($array2[$key]))
        $merged[$key] = is_array($merged[$key]) ? array_merge_recursive_distinct($merged[$key], $array2[$key]) : $array2[$key];
      else
        $merged[$key] = $val;
  
  return $merged;
}