# Smartlogs
A standard, comprehensive, effective and efficient logging library for inspecting information that will allow auditing data much more faster and accurate

## What it does
___
1. Compare JSON and save it as log
2. Create a comprehensive timeline from logs


## How it works ?
___

### Comparing
1. Compare data in JSON format (new and old)
2. Capture the items that have been added, changed or deleted
3. Generate JSON in memory that contains those changes


## Installation
> composer install

## Unit Testing
Run the code below in your terminal to run the tests 
> ./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/

## Documentation
Run this command and open the ***docs/index.html*** file in your browser
> php phpDocumentor.phar -d src -t docs

> You must download the phpDocumentor official [phar binary](http://phpdoc.org/phpDocumentor.phar) and copy it inside the project folder.
