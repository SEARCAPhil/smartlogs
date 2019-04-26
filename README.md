# Smartlogs
A standard, comprehensive, effective and efficient logging library for inspecting information that will allow auditing data much more faster and accurate

## What it does
1. Compare JSON and save the result as log
2. Parse and create a comprehensive timeline from logs


## How it works ?
### Comparing
1. Compare data in JSON format (new and old)
2. Capture the items that have been added, changed or deleted
3. Generate JSON in memory that contains those changes

### Merging
1. Read logs synchronously
2. **Merge 2** logs to generate a **Frame** which contains the data before a recent changes occur. 


![sample](https://southeastasia1-mediap.svc.ms/transform/thumbnail?provider=spo&inputFormat=png&cs=fFNQTw&docid=https%3A%2F%2Fsearca-my.sharepoint.com%3A443%2F_api%2Fv2.0%2Fdrives%2Fb!VS5JxtFF4k-IUg9bfl3v_Oxr3thTBSlEirqqG2YFi7p7oyoUIGF1TZxVaFQ08GC4%2Fitems%2F01PCGULUFKXDTNPET6LRBK4KB5MDOFJ5FA%3Fversion%3DPublished&access_token=eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJhdWQiOiIwMDAwMDAwMy0wMDAwLTBmZjEtY2UwMC0wMDAwMDAwMDAwMDAvc2VhcmNhLW15LnNoYXJlcG9pbnQuY29tQDMwN2E1NGY0LThjZGUtNDY4MS05M2FmLWJlNmNlYmYzMGMyNiIsImlzcyI6IjAwMDAwMDAzLTAwMDAtMGZmMS1jZTAwLTAwMDAwMDAwMDAwMCIsIm5iZiI6IjE1NTYyNTgyMTYiLCJleHAiOiIxNTU2Mjc5ODE2IiwiZW5kcG9pbnR1cmwiOiJQTkplV3U2SG41dWZ3Zjdlc081T01jYThLZjd4NDkyd1JLZUthOUpCak5ZPSIsImVuZHBvaW50dXJsTGVuZ3RoIjoiMTE2IiwiaXNsb29wYmFjayI6IlRydWUiLCJjaWQiOiJOR0ZoTm1RMk9XVXROakJtWkMwNE1EQXdMVFk0TVRVdFlqbGlPRFZsWXpVMllXRTEiLCJ2ZXIiOiJoYXNoZWRwcm9vZnRva2VuIiwic2l0ZWlkIjoiWXpZME9USmxOVFV0TkRWa01TMDBabVV5TFRnNE5USXRNR1kxWWpkbE5XUmxabVpqIiwibmFtZWlkIjoiMCMuZnxtZW1iZXJzaGlwfGprZ2FAc2VhcmNhLm9yZyIsIm5paSI6Im1pY3Jvc29mdC5zaGFyZXBvaW50IiwiaXN1c2VyIjoidHJ1ZSIsImNhY2hla2V5IjoiMGguZnxtZW1iZXJzaGlwfDEwMDMzZmZmOGQyMzUxNTNAbGl2ZS5jb20iLCJ0dCI6IjAiLCJ1c2VQZXJzaXN0ZW50Q29va2llIjoiMiJ9.dzU0aVpZbXArMExYMk9HTDJYMnlzamRud0o5d25adEhrOTdDeVF4VnRKUT0&encodeFailures=1&width=905&height=592&srcWidth=905&srcHeight=592)


## Installation
> composer install

## Unit Testing
Run the code below in your terminal to run the tests 
> ./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/

## Documentation
Run this command and open the ***docs/index.html*** file in your browser
> php phpDocumentor.phar -d src -t docs

> You must download the phpDocumentor official [phar binary](http://phpdoc.org/phpDocumentor.phar) and copy it inside the project folder.
