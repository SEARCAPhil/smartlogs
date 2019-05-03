<?php
/**
 * Smartlogs
 * 
 * @package SmartLogs
 * @author jkga <johnkennethgibasabella@gmail.com>
 */

namespace SmartLogs;

/**
 * Core class of the library which handles the merging and getting 
 * the difference between arrays or objects
 */
class Logger {

  /**
   * Constructor
   */
  function __construct () {
    $this->payload = [];
    $this->log = new \StdClass;
  }

  /**
   * Check JSON content data and author
   * 
   * Identify if both data and author is present otherwise throws an error
   * @param json $json
   */
  private function checkComponents ($json) {
    # Logs MUST ALWAYS have a content and an author.
    # Removing either one of those fields might cause inaccuray during audit review
    if(!$json->data) throw new \Exception ('No specified data');
    if(!$json->author) throw new \Exception ('No specified author');
  }


  /**
   * Validate JSON input
   * 
   * This function decodes json and check if required components are needed
   * @param json $json
   * @return Array
   */
  private function parse ($json) {   
    # convert data to array
    $jsonArray = json_decode($json);

    # check if author and data is present
    self::checkComponents ($jsonArray);

    return $jsonArray;
  }


  /**
   * Compare two arrays
   * 
   * Compare set of arrays to determine changes that occured recursively
   * 
   * @param Array|Object $new
   * @param Array|Object $old
   * @param Array|Object $root
   * @return Object
   */
  private function compare($new, $old, $root = []) {

    # generate a two different sets to identify which has been added, updated, and removed
    # then compare both results
    $setA = self::intersect ($root = [], $new, $old);
    $setB = self::intersect ($root = [], $old, $new, true); 

    # combine all items in the element
    $this->payload = array_replace_recursive($setB, $setA);
    return $this;
  }


  /**
   * Create an intersection for two arrays
   * 
   * This function get all the difference between the first and the second argument
   * NOTE: The structure of an object will  slightly change to allow merging of array recursively
   * If an array contains an object child, the child will be automatically converted to array
   * Doing this conversion allows merging of nested object (now an array) instead of replacing
   * the old one which yields inaccurate result. You may try merging an array that contains an object
   * using php's built in function 'array_replace_recursive' to see what it really does  
   * @param Array|StdClass $root
   * @param Array|StdClass $new
   * @param Array|StdClass $old
   * @param Array|StdClass $transformToNull Removes the value of an element if this element does not exists on the previous/old set
   * @return Array
   */
  private function intersect ($root = [], $new, $old, $transformToNull = false) { 

    foreach ($new as $key => $value) { 

      # alter spaces to underscore _
      $keyName = str_replace(' ', '_', $key);

      # prevent missused of stdClass and array
      # Undefined offset:
      # Undefined property: stdClass
      # If onject does not found, it will return an empty value instead
      $oldKey = is_object($old) ? @$old->{$keyName} : @$old[$keyName];
      $newKey = is_object($new) ? @$new->{$keyName} : @$new[$keyName];

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
          $root->{$keyName} = $this->intersect(new \StdClass, $value, $oldKey, $transformToNull) ; 
        }

        if(is_object($newKey) && !is_object($root)) { 
          $root[$keyName] = $this->intersect($newParentElement, $value, $oldKey, $transformToNull) ;
        }

        if(is_array($newKey) && is_array($root)) { 
          $root[$keyName] = $this->intersect ([], $value, $oldKey, $transformToNull) ;
        }

        if(is_object($oldKey) && is_array($root)) {
          $root[$keyName] = $this->intersect ([], $value, $oldKey, $transformToNull) ;
        }
      }
    }
    return $root;
  }

  /**
   * Compare the difference between arrays
   * 
   * Thes calls the 'compare' and 'parse' function under the hood to do the JSON validation.
   * It returns the first argument if only one JSON is present
   * @param JSON $json 
   * @param JSON $prevJSON 
   * @return Object
   */
  public function diff ($json, $prevJSON = null) {
    # validate data
    $new = self::parse ($json);
    

    # compare result if there is a previous result
    # otherwise save the first one
    if(!$prevJSON) {
      $this->payload = self::toArray($new->data);  
    } else {
      $prev = self::parse ($prevJSON);
      $this->compare($new->data, $prev->data);
    }
    
    # combine data and author
    $this->log = self::encapsulate($this->payload, self::toArray($new->author));
    return $this->log;
  }


  /**
   * Merge two sets of array
   * 
   * Combine two arrays and remove those elements with null values
   * @param Array $array1
   * @param Array $array2
   * @param bool $excludeNullElements default = true
   */
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

  /**
   * Create a payload with data and author
   * 
   * {
   *    data: array,
   *    author: array
   * }
   */
  function encapsulate ($data, $author) {
    $this->nlog = new \StdClass;
    $this->nlog->data = $data;
    $this->nlog->author = $author;
    return $this->nlog;
  }

  public function toArray ($data, $root = []) {
    # convert data to object first before reading
    $root = (array) $root;
    foreach ($data as $key => $value) {
      if(is_array($value)) {
        $root[$key] = $this->toArray([], $value);
      } else {
        $root[$key] = $value;
      }
    }
    return $root;
  }

  public function frame($log, $file) {
    $decoded_file = json_decode($file);

    $original_context = self::merge($log->data, (array) $decoded_file->data);
    
    // get the previous author and data
    // and produce a new object
    return  self::encapsulate($original_context, self::toArray($decoded_file->author));
  }
}

