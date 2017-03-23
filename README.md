thinkphp5-curl extension
===================
[![Latest Stable Version](https://poser.pugx.org/lobtao/thinkphp5-curl/v/stable)](https://packagist.org/packages/lobtao/thinkphp5-curl)
[![Total Downloads](https://poser.pugx.org/lobtao/thinkphp5-curl/downloads)](https://packagist.org/packages/lobtao/thinkphp5-curl)
[![License](https://poser.pugx.org/lobtao/thinkphp5-curl/license)](https://packagist.org/packages/lobtao/thinkphp5-curl)
                   
Easy working cURL extension for thinkphp5, including RESTful support:

 - POST
 - GET
 - HEAD
 - PUT
 - PATCH
 - DELETE

Requirements
------------
- PHP 5.4+
- Curl and php-curl installed


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```bash
php composer.phar require --prefer-dist lobtao/thinkphp5-curl "*"
```


Usage
-----

Once the extension is installed, simply use it in your code. The following example shows you how to handling a simple GET Request. 

```php
use lobtao\thinkphp5\curl;
$curl = new curl\Curl();

//get http://example.com/
$response = $curl->get('http://example.com/');

if ($curl->errorCode === null) {
   echo $response;
} else {
     // List of curl error codes here https://curl.haxx.se/libcurl/c/libcurl-errors.html
    switch ($curl->errorCode) {
    
        case 6:
            //host unknown example
            break;
    }
} 
```

```php
// GET request with GET params
// http://example.com/?key=value&scondKey=secondValue
$curl = new curl\Curl();
$response = $curl->setGetParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->get('http://example.com/');
```


```php
// POST URL form-urlencoded 
$curl = new curl\Curl();
$response = $curl->setPostParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->post('http://example.com/');
```

```php
// POST with special headers
$curl = new curl\Curl();
$response = $curl->setPostParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->setHeaders([
        'Custom-Header' => 'user-b'
     ])
     ->post('http://example.com/');
```


```php
// POST JSON with body string & special headers
$curl = new curl\Curl();

$params = [
    'key' => 'value',
    'secondKey' => 'secondValue'
];

$response = $curl->setRequestBody(json_encode($params))
     ->setHeaders([
        'Content-Type' => 'application/json',
        'Content-Length' => strlen(json_encode($params))
     ])
     ->post('http://example.com/');
```

```php
// Avanced POST request with curl options & error handling
$curl = new curl\Curl();

$params = [
    'key' => 'value',
    'secondKey' => 'secondValue'
];

$response = $curl->setRequestBody(json_encode($params))
     ->setOption(CURLOPT_ENCODING, 'gzip')
     ->post('http://example.com/');
     
// List of status codes here http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
switch ($curl->responseCode) {

    case 'timeout':
        //timeout error logic here
        break;
        
    case 200:
        //success logic here
        break;

    case 404:
        //404 Error logic here
        break;
}

//list response headers
var_dump($curl->responseHeaders);
```
Support Thinkphp5