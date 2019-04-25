<?php
declare(strict_types=1);
require('src/Logger.php');

use Smartlogs\Logger as Logger;
use PHPUnit\Framework\TestCase;



final class LoggerTest extends TestCase {

  public function testClassCanBeCreated () {
    # new Logger Instance
    $a = new Logger();
    $this->assertInstanceOf(Logger::class, $a);
  }

  public function testResultMustBeTheSame() {

    # JSON files
    $file1 = file_get_contents('sample/json/01-31-2019 13-04-00.json');
    $file2 = file_get_contents('sample/json/01-31-2019 13-05-00.json');

    # The result should be look like this
    # NOTE: Address will be converted to array automatically
    # to prevent replacing. This is also stated in the documentation
    # and MUST be considered before using the library
    $expectedResult = '{"name":"Jane Hey","age":null,"hobbies":{"1":"surfing"},"address":{"primary":{"2":null},"tertiary":null},"company":"SEARCA"}';

    # new Logger Instance
    $a = new Logger();
    $a->diff($file1, $file2);
    $this->assertTrue(json_encode($a->payload) == $expectedResult );
  }
}