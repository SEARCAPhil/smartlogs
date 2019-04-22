<?php
namespace SmartLogs;

class Framer {
  function __construct () {
    $this->payload = [];
    $this->symbols = [
      'added' => '+',
      'changed' => '*',
      'removed' => '-',
      'space' => '_'
    ];
  }

  private function parse ($json) {
    # convert data to array
    return json_decode($json);
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

    # convert data to object first before reading
    $root = (object) $root;
    $new = (object) $new;
    $old = (object) $old;

    foreach ($new as $key => $value) { 
      # alter spaces with underscore _
      $keyName = str_replace(' ', $this->symbols['space'], $key);

      $oldKey = $old->{$keyName};
      $newKey = $new->{$keyName};

      # read all numeric arrays that have been converted to StdClass
      $o = (json_decode(json_encode($old)))->{"${keyName}"};
      $p = (json_decode(json_encode($new)))->{"${keyName}"};

      $parsedOldKey = !is_null($o) ? $o : $oldKey;
      $parsedNewKey = !is_null($p) ? $p : $newKey;


      # detect if new one exists in previous
      # if not add to stagging area
      # Note: there is no need to read all sub data since the parent does not exists
      # in the previous one and considered as relatively new
      # transformToNull is used for changing value to empty. It is used solely
      # for an item which has been removed to save space
      if(!$parsedOldKey) {  
        $root->{"${attr}${keyName}"} = $transformToNull ? '' : $value;

      }

      # if field is both present, consider comparing values
      # this will be marked as updated
      if($parsedOldKey && (in_array(gettype($parsedOldKey), array('integer', 'string'))) && ($parsedOldKey !== $parsedNewKey )) { 
        $kn = $this->symbols['changed']."${keyName}" ;
        $root->{$kn} = $value;

        
      }

      if($parsedOldKey && (in_array(gettype($parsedNewKey), array('array', 'object')))) { 
        # convert array to object
        $parsedNewKey = (object) $parsedNewKey; 
        $root->{$keyName} = $this->intersect(new \StdClass, $value, $parsedOldKey, $attr, $transformToNull) ;
      }
      
    }

    return (array) $root;
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

