<?php
namespace SmartLogs;

class Logger {
  function __construct () {
    $this->payload = [];
    $this->symbols = [
      'added' => '+',
      'changed' => '*',
      'removed' => '-',
      'space' => '_'
    ];
  }


  private function checkComponents ($json) {

    # Logs MUST ALWAYS have a content and an author.
    # Removing either one of those fields might cause inaccuray during audit review
    if(!$json->data) throw new \Exception ('No specified data');
    if(!$json->author) throw new \Exception ('No specified author');
  }

  private function parse ($json) {
        
    # convert data to array
    $jsonArray = json_decode($json);

    # check if author and data is present
    self::checkComponents ($jsonArray);

    return $jsonArray;
  }


  private function compare($root = [], $new, $old) {

    # generate a two different sets to identify which has been added, updated, and removed
    # then compare both results
    $setA = self::intersect ($root = [], $new, $old, $this->symbols['added']);
    $setB = self::intersect ($root = [], $old, $new, $this->symbols['removed'], true); 

    # combine all items in the element
    $this->payload = array_replace_recursive($setB, $setA);
    return $this;
  }


  private function intersect ($root = [], $new, $old, $attr = '', $transformToNull = false) { 

    foreach ($new as $key => $value) { 

      # alter spaces to underscore _
      $keyName = str_replace(' ', '_', $key);

      # prevent missused of stdClass and array
      $oldKey = is_object($old) ? $old->{$keyName} :$old[$keyName];
      $newKey = is_object($new) ? $new->{$keyName} :$new[$keyName];

      # detect if new one exists in previous
      # if not add to stagging area
      # Note: there is no need to read all sub data since the parent does not exists
      # in the previous one and considered as relatively new
      if(!$oldKey) { 
        $val = $transformToNull ? null : $value;
        $r = is_object($root) ? ($root->{$keyName} = $val) : ($root[$keyName] = $val);

      } else {
        # prevent conversion of an element from stdClass to array or vised versa
        $newParentElement = is_object($oldKey) ? new \StdClass : [];

        # if field is both present, consider comparing values
        # compare value only if it is a string or an integer
        # and contains different value
        if((in_array(gettype($oldKey), array('integer', 'string'))) && ($oldKey !== $newKey )) {
          (is_object($root)) ? ($root->{$keyName} = $value) : ($root[$keyName] = $value);
        } 

        # this is for objects or array
        if(is_object($newKey) && is_object($root)) {  
          $root->{$keyName} = $this->intersect(new \StdClass, $value, $oldKey, $attr, $transformToNull) ; 
        }

        if(is_object($newKey) && !is_object($root)) { 
          $root[$keyName] = $this->intersect($newParentElement, $value, $oldKey, $attr, $transformToNull) ;
        }

        if(is_array($newKey) && is_array($root)) { 
          $root[$keyName] = $this->intersect ([], $value, $oldKey, $attr, $transformToNull) ;
        }

        if(is_object($oldKey) && is_array($root)) {
          $root[$keyName] = $this->intersect ([], $value, $oldKey, $attr, $transformToNull) ;
        }
      }
    }
    return $root;
  }

  public function diff ($json, $prevJSON = null, $sign = '') {
    # validate data
    $this->sign = $sign;
    $new = self::parse ($json);
    $prev = self::parse ($prevJSON);

    # compare result if there is a previous result
    # otherwise save the first one
    if(!$prevJSON) {
      $this->payload = $new;  
    } else {
      $this->compare([], $new->data, $prev->data);
    }
    return $this;
  }

  function merge($array1, $array2, $excludeNullElements = true) {
    $array = $array2;
    foreach($array1 as $key => $val) { 
      if($excludeNullElements && is_null($val)) {
        // Do not insert null elements
      } else {
        $array[$key] = is_array($val) ? self::merge((array) $val, (array) $array[$key]) : $array[$key] = $val;
      }
      
    }
    return $array;
  }

  public function print () {
    print_r($this->payload);
  }

  public function json () {
    # returns JSON encoded result
    $this->payload = json_encode($this->payload);
    return $this;
  }


}
