Numero-log
==========

[![Build Status](https://img.shields.io/travis/NamelessCoder/numerolog.svg?style=flat-square&label=package)](https://travis-ci.org/NamelessCoder/numerolog) [![Coverage Status](https://img.shields.io/coveralls/NamelessCoder/numerolog/master.svg?style=flat-square)](https://coveralls.io/r/NamelessCoder/numerolog)

**Numero-log** is a simple client/server application written in PHP which collects
arbitrary numerical data about a named resource (package, for example), stores it
and performs calculations when data is requested.

It consists of a tiny client and equally tiny server; the client is the main API
through which you submit the collected numerical data - and the server is the API
through which you request various renditions of numbers.

Demonstration
-------------

![Demonstration](http://numerolog.namelesscoder.net/numerolog-demo.gif)

Purpose
-------

**Numero-log** lets you store and retrieve numeric-only information from a remote
host, and lets you not care about creating various storages for the numbers (this
happens automatically). When retrieving numbers, **Numero-log** also lets you read
basic information about the data set such as minimum value, maximum value, average
and sum.

We can illustrate this with a basic example: tracking a temperature.

Not concerning ourselves with *how* you retrieve each temperature value, let's say
you have an automated task that runs every five minutes to gather a temperature.
You wish to store this value **remotely** (this is a key aspect: if you do not
require remote storage, there's no need to use this package - just use a plain DB
instead). You then ask **Numero-log** to log each and every number, every five
minutes. After collecting values for some time you would then be able to query:

* What the temperatures were at any given time
* What the temperature range was at any given time range
* What the maximum temperature was in any given time range
* What the median temperature was
* And more

The data sets are returned with extreme speed, as JSON. The fast responses and JSON
format means you can (just for example) use this from JavaScript applications that
render graphs, timelines, peak warnings, tendency projections etc.

**Numero-log is not a substitute for actual monitoring software. It is intended as
a light-weight alternative to a much larger setup when all you need to track, is a
handful of numeric statistics**.

Requirements
------------

To use the server part, a HTTP server like Apache or Nginx is required. In a pinch
even PHP's built-in server can be used.

A HTTP server is not required when you use **Numero-log** with the official data
server which is provided for free community use (note: this access may change in the
future but **Numero-log** will not lose the ability to work with custom servers).

To use the command line and client API you only need PHP and a network connection.

Configuration
-------------

**Numero-log** supports a minimum of configuration - in fact, just a single setting.
Because there is only one setting it was elected to put this in the composer.json
file of the folder you're in. It was chosen this way because the expected usages all
have composer at their core: even if you just `require` this package, composer will
generate the composer.json file for you.

In the composer file you can place this singular setting:

```yaml
{
    "name": "myvendor/mypackage",
    "extra": {
        "mamelesscoder/numerolog": {
            "host": "http://mydomain.com/"
        }
    }
}
```

Changing this `host` setting makes **Numero-log** communicate with that host as
endpoint (storage). You can then make your HTTP server on that host serve the
`index.php` file from the `web` directory in this package (copy it, symlink it
or serve it directly from vendor).

NB: **Numero-log** will also use the `name` setting from composer.json as package
identification when speaking to the remote storage. **Each value that you track is
associated with the package**.

Usage
-----

There are two ways to use the **Numero-log** client (obviously, the server part only
has a single mode of operation: serve index.php and you're done):

In command line mode you can call `./vendor/bin/numerolog` to both report and get
data. Simply call the command without parameters for a brief help text. Some example
commands that should immediately make sense to you:

```bash
# increment "mycounter" by 10
./vendor/bin/numerolog --action save --package myvendor/mypackage \
    --token 1234567890abcdefg1234567890abcdefg
    --counter mycounter --value +10

# record a new temperature measurement
./vendor/bin/numerolog --action save --package myvendor/mypackage \
    --token 1234567890abcdefg1234567890abcdefg
    --counter temperature --value 31.5

# get the most recent recorded temperature
./vendor/bin/numerolog --action get --package myvendor/mypackage \
    --token 1234567890abcdefg1234567890abcdefg
    --counter temperature

# get the twenty most recent recorded temperatures
./vendor/bin/numerolog --action save --package myvendor/mypackage \
    --token 1234567890abcdefg1234567890abcdefg
    --counter temperature --count 20

# get January's recorded temperatures
./vendor/bin/numerolog --action get --package myvendor/mypackage \
    --token 1234567890abcdefg1234567890abcdefg
    --counter temperature --from 2015-01-01 --to 2015-01-31

# get temperatures recorded from January up to today
./vendor/bin/numerolog --action get --package myvendor/mypackage \
    --token 1234567890abcdefg1234567890abcdefg
    --counter temperature --from 2015-01-01
```

Note that the `--package myvendor/mypackage` argument can be left out as long as your
project folder's composer.json file has a `name`. **Numero-log** will then use this
value automatically and you can skip it in commands. And note that the `--token`
parameter can also be set as an ENV variable which the `numerolog` command will use.
With the package coming from composer.json and token coming from ENV, a command might
look like this instead:

```bash
# passing the "token" as ENV variable; read package from composer.json
NUMEROLOG_TOKEN="1234567890abcdefg1234567890abcdefg" ./vendor/bin/numerolog \
    --action get --counter mycounter --count 10
```

The second mode of operation is directly via PHP:

```php
$client = new \NamelessCoder\Numerolog\Client();
$client->get($package, $counter, $count = 1);
$client->getRange($package, $counter, $from, $to = NULL);
$client->save($package, $counter, 3.14);
```

Security
--------

**Numero-log** was built for public access via the HTTP part and imposes no
authentication requirements when **reading** data. If you only want secure
access to the data make sure you set up your own HTTP server and protect it
accordingly. The command line utility allows you to create any number of named
trackings and works by issuing a single token when first creating the database
for your package. The token is only returned in the very first response and must
be saved and reused when reporting new data to the remote storage.
