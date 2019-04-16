<?php
namespace SmartLogs;

class Logger {
  function __construct () {
    $this->root = [];
    $this->stagging = [];
    $this->parsedData;
  }

  private function convertToArray ($json) { 
    return json_decode($json);
  }

  private function checkComponents ($json) {
    # Logs MUST ALWAYS have a content and an author.
    # Removing either one of those fields might cause inaccuray during audit review
    if(!$json->data) throw new \Exception ('No specified data');
    if(!$json->author) throw new \Exception ('No specified author');
  }

  private function parse ($json) {
        
    # convert data to array
    $jsonArray = self::convertToArray($json);

    # check if author and data is present
    self::checkComponents ($jsonArray);

    # rebuild array to recommended structure
    # $this->parsedData = self::build([], $jsonArray->data);

    # return pre-parsed log result
    #return self::save($this->parsedData, $jsonArray->author);
    return $jsonArray ;
  }



  public function write ($json, $prevJSON = null, $sign = '') {
    $this->sign = $sign;
    $new = self::parse ($json);
    $prev = self::parse ($prevJSON);


    # compare result if there is a previous result
    # otherwise save the first one
    if(!$prevJSON) {
      $this->root = $new;
    } else {
      $this->diff([], $new->data, $prev->data);
    }
    return $this;
  }

  public function diff($root = [], $new, $old) {
    # generate a two different sets to identify which has been added or removed
    # then compare both results
    $setA = self::intersect ($root = [], $new, $old);
    $setB = self::intersect ($root = [], $old, $new, '.'); 

    var_dump(array_merge($setB,$setA));
    return $this;
  }

  public function intersect ($root = [], $new, $old, $attr = '') { 
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
        $r = (gettype($root) === 'object') ? ($root->{"${attr}${keyName}"} = $value) : ($root["${attr}${keyName}"] = $value);
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

}

