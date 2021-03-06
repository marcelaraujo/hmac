Mardy-Git HMAC
==============
[![Build Status](https://travis-ci.org/mardy-git/hmac.png?branch=dev)](https://travis-ci.org/mardy-git/hmac)

A simple lightweight HMAC generator and checker.

Currently this is used to authenticate applications to other applications.

Installation
--------------

To install this use composer by adding

    "mardy/hmac": "2.*"

to your composer.json file

Usage Example
--------------------

Generating the HMAC
-------------------
```php
use Mardy\Hmac\Manager;
use Mardy\Hmac\Adapters\Hash;

//there are several adapters available 'Bcrypt', 'Hash', 'HashHmac', 'HashPbkdf2'
//you can inject any of them into the manager, they all share the same interface
//With the Bcrypt adapter the num of iteration config is applied to the cost
$manager = new Manager(new Hash);

//you can use any of the Hash algorithms that are available on your environment
$config = [
    'algorithm' => 'sha256',
    'num-first-iterations' => 10,
    'num-second-iterations' => 10,
    'num-final-iterations' => 100,
];

//the private key used in both applications to ensure the hash is the same
$key = "wul4RekRPOMw4a2A6frifPqnOxDqMXdtRQMt6v6lsCjxEeF9KgdwDCMpcwROTqyPxvs1ftw5qAHjL4Lb";

try {
    $manager->config($config);
} catch (\InvalidArgumentException $e) {
    //an \InvalidArgumentException can be caught here
    //"The algorithm ({$algorithm}) selected is not available"
}

//the secure private key that will be stored locally and not sent in the http headers
$manager->key($key);

//the data to be encoded with the hmac, you could use the URI for this
$manager->data('test');

//the current timestamp, this will be compared in the other API to ensure
$manager->time(time());

//encodes the hmac if all the requirements have been met
try {
    $manager->encode();
} catch (\InvalidArgumentException $e) {
    //an \InvalidArgumentException can be caught here
    //'The item is not encodable, make sure the key, time and data are set'
}

$hmac = $manager->toArray();

//these values need to be sent in the http headers of the request so they can
//be received by the api and used to authenticated the request
//$hmac = [
//    'data' => 'test',
//    'time' => 1396901689,
//    'hmac' => 'f22081d5fcdc64e3ee78e79d235f67b2d1a54ba24be6da4ac537976d313e07cf119731e76585b9b22f789c6043efe1df133497483f559899db7d2f4398084b08',
//];
```

Validating the HMAC
-------------------
```php
use Mardy\Hmac\Manager;
use Mardy\Hmac\Adapters\Hash;

//there are several adapters available 'Bcrypt', 'Hash', 'HashHmac', 'HashPbkdf2'
//you can inject any of them into the manager, they all share the same interface
//With the Bcrypt adapter the num of iteration config is applied to the cost
$manager = new Manager(new Hash);

//you can use any of the Hash algorithms that are available on your environment
$config = [
    'algorithm' => 'sha256',
    'num-first-iterations' => 10,
    'num-second-iterations' => 10,
    'num-final-iterations' => 100,
];

//the private key used in both applications to ensure the hash is the same
$key = "wul4RekRPOMw4a2A6frifPqnOxDqMXdtRQMt6v6lsCjxEeF9KgdwDCMpcwROTqyPxvs1ftw5qAHjL4Lb";
$ttl = 2;

try {
    $manager->config($config);
} catch (\InvalidArgumentException $e) {
    //an \InvalidArgumentException can be caught here
    //"The algorithm ({$algorithm}) selected is not available"
}

//time to live, when checking if the hmac isValid this will ensure
//that the time with have to be with this number of seconds
$manager->ttl($ttl);

//the secure private key that will be stored locally and not sent in the http headers
$manager->key($key);

//get the HMAC values from the $_SERVER/request headers (and make sure you sanitise the values)
$hmac['data'] = filter_var($_SERVER['data'], FILTER_SANITIZE_STRING);
$hmac['time'] = filter_var($_SERVER['time'], FILTER_SANITIZE_STRING);
$hmac['hmac'] = filter_var($_SERVER['hmac'], FILTER_SANITIZE_STRING);

//the data to be encoded with the hmac, you could use the URI for this
$manager->data($hmac['data']);

//the current timestamp, this will be compared in the other API to ensure
$manager->time($hmac['time']);

//to check if the hmac is valid you need to run the isValid() method
//this needs to be executed after the encode method has been ran
if (! $manager->isValid($hmac['hmac'])) {
    http_response_code(401);
    echo 'Invalid credentials';
}
```