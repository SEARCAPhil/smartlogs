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
    $this->parsedData = self::build([], $jsonArray->data);

    # return pre-parsed log result
    return self::save($this->parsedData, $jsonArray->author);
  }



  public function write ($json, $prevJSON = null) {

    $new = self::parse ($json);
    $prev = self::parse ($prevJSON);

    # compare result if there is a previous result
    # otherwise save the first one
    if(!$prevJSON) {
      $this->root = $new;
    } else {
      var_dump($this->diff([], $new->data, $prev->data));
    }
    return $this;
  }

  public function diff($root = [], $new, $old) {
    return self::intersect ($root = [], $new, $old);
  }

  public function intersect ($root = [], $new, $old) { 
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
            $root->{$keyName} = $this->diff (new \StdClass, $value, $oldKey) ;
          } else { 
            $root[$keyName] = $this->diff ([], $value, $oldKey) ;
          }
        }

        if(gettype($newKey) === 'array') { 
          if(gettype($root) === 'array') {
            $root[$keyName] = $this->diff ([], $value, $oldKey) ;
          }
        }
      }
    }

    return $root;
  }

  public function build ($root = [], $data) {
    
    foreach ($data as $key => $value) {
      # alter spaces to underscore _
      $keyName = str_replace(' ', '_', $key);
  
      if(gettype($root) === 'object') { 
        $root->{$keyName} = $value;
        # traverse children for non-string value
        if(gettype($value) !== 'string') $root->{$keyName} = $this->build($root->{$keyName}, $value);
      } else { 
        $root[$keyName] = $value;
        if(gettype($value) !== 'string') $root[$keyName] = $this->build($root[$keyName], $value);
        
      }
    }
    
    return $root;
  }


  public function save($data, $author) {
    $obj = new \StdClass;
    $obj->name = (new \DateTime())->format('Y-m-d H:i:s');;
    $obj->data = $data;
    $obj->author = $author;
    return $obj;
  }

  function print () {
    //echo $this->root;
  }

  function json () {
    $this->root = json_encode((object) $this->root);
    return $this;
  }
}

