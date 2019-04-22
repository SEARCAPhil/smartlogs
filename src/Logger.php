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
      $oldKey = (gettype($old) === 'object') ? $old->{$keyName} :$old[$keyName];
      $newKey = (gettype($new) === 'object') ? $new->{$keyName} :$new[$keyName];
      # detect if new one exists in previous
      # if not add to stagging area
      # Note: there is no need to read all sub data since the parent does not exists
      # in the previous one and considered as relatively new
      if(!$oldKey) { 
        $r = (gettype($root) === 'object') ? ($root->{$keyName} = $value) : ($root[$keyName] = $value);
      }
      # if field is both present, consider comparing values
      if($oldKey) { 
        # compare value only if it is a string or an integer
        # and contains different value
        if((in_array(gettype($oldKey), array('integer', 'string'))) && ($oldKey !== $newKey )) {
          (gettype($root) === 'object') ? ($root->{$keyName} = $value) : ($root[$keyName] = $value);
        } 
        # this is for objects or array
        if(gettype($newKey) === 'object') { 
          if(gettype($root) === 'object') {
            $root->{$keyName} = $this->intersect(new \StdClass, $value, $oldKey, $attr) ;
          } else { 
            $root[$keyName] = $this->intersect([], $value, $oldKey, $attr) ;
          }
        }
        if(gettype($newKey) === 'array') { 
          if(gettype($root) === 'array') {
            $root[$keyName] = $this->intersect ([], $value, $oldKey, $attr) ;
          }
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

  # returns parsed result without the symbols
  public function unsigned () {
    $this->payload = (array) self::build([], (object) $this->payload);
    return $this;
  }

  public function build ($root = [], $data) {

    # convert data to object first before reading
    $root = (object) $root;

    foreach ($data as $key => $value) {
      # remove symbols at the beggining of the key
      $firstLetter = substr($key, 0, 1);
      $firstWord = substr($key, 1, strlen($key));
      $keyName = in_array($firstLetter, $this->symbols) ? $firstWord : $key;

      if(in_array(gettype($value), array('array', 'object'))) {
        $root->{$keyName} = $this->build($root->{$keyName}, $value);
      } else {
        $root->{"${keyName}"} = $value;
      }
    }

    return $root;
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

