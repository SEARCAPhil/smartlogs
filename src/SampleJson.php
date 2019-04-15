<?php

// example JSON 1
$arr = new \StdClass;
$arr->data = [
    'name' => 'Jane Doe',
    'age' => '16',
    //'hobbies' => ['basketball', 'soccer'],
    'address' => [
      'primary' => ['a', 'b', 'c'],
      'secondary' => 'address 2'
    ]
];

$arr->author = [
  'name' => 'John'
];

$json = \json_encode($arr);


// example JSON 2
$arr2 = new \StdClass;
$arr2->data = [
    'name' => 'Jane Hey',
    //'hobbies' => ['basketball', 'soccer'],
    'address' => [
      'primary' => ['a', 'b'],
      'secondary' => 'address 2'
    ],
    'company' => 'SEARCA'
];

$arr2->author = [
  'name' => 'John'
];

$json2 = \json_encode($arr2);