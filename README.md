Numero-log
==========

[![Build Status](https://img.shields.io/travis/NamelessCoder/numerolog.svg?style=flat-square&label=package)](https://travis-ci.org/NamelessCoder/numerolog) [![Coverage Status](https://img.shields.io/coveralls/NamelessCoder/numerolog/master.svg?style=flat-square)](https://coveralls.io/r/NamelessCoder/numerolog)

**Numero-log** is a simple client/server application written in PHP which collects
arbitrary numerical data about a named resource (package, for example), stores it
and performs calculations when data is requested.

It consists of a tiny client and equally tiny server; the client is the main API
through which you submit the collected numerical data - and the server is the API
through which you request various renditions of numbers.

The output is ideal for, but not limited to, displaying in graphs that use JSON
as data source (uses a two-axis array with time and value).

Demonstration
-------------

![Demonstration](http://numerolog.namelesscoder.net/numerolog-demo.gif)

*The GIF demonstrates how to create a counter for a package using a custom token
(happens in first save action) and recording additional values for the counter.
It finishes off by showing how those values can then be retrieved with statistics.
The collection of values happens locally - the storing and calculating of values
happens on the default remote that is open and free to use (but can be changed).
Note that `| json_pp` is used to "pipe" a compact format JSON to pretty print.*

Companion packages
------------------

* [Numerolog Lavacharts](https://github.com/NamelessCoder/numerolog-lavacharts)
  enables charting output of Numerolog counters; is publicly available on the
  public Numerolog server. See project page for usage!
* [Numerolog PHPUnit](https://github.com/NamelessCoder/numerolog-phpunit) enables
  unit tests to make assertions based on statistical data while also collecting new
  data. The result is a "learning" system that perpetually adjusts the expected
  limits of chosen indicators (memory usage, file size, functions called, stack
  depth etc).

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

# poll a the maximum recorded temporature over last 30 recoded values
./vendor/bin/numerolog --action poll --package myvendor/mypackage \
    --token 1234567890abcdefg1234567890abcdefg
    --counter temperature --poll max --count 30
```

The `get` and `poll` commands are very similar in nature and support the same limit
and range arguments, but differ in that `poll` only returns a specific value from
the set, for example one of the statistics counters as in this example. Names that
can be used for `--poll` are the indexes from the root array and indexes of the
`statitics` array. E.g. `max`, `average`, `sum`, `values` or all of `statistics`.

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

Usage with monitoring and systems like Cacti
--------------------------------------------

**Numero-log** contains an easy to use method of polling individual parameters of
a `get` response - which means that rather than have to parse JSON to know a certain
value, you can simply request that value. And this means that the `poll` command is
ideal for collecting a single value and logging the value or performing some action.
For example, you might want to perform some action if the average temperature reaches
a certain point, a small executable script with an exit code might do well:

```bash
#!/usr/bin/env bash

# Variable $TEMPERATURE30 will contain a single numerical value which we can check:
TEMPERATURE30=`./vendor/bin/numerolog --action poll --package myvendor/mypackage \
                 --token 1234567890abcdefg1234567890abcdefg
                 --counter temperature --poll average --count 30`

# The value is the average measured across the last 30 recorded values.

if [[ $TEMPERATURE30 -gt 40 ]]; then
    echo "It's getting hot in here!"
    echo "$TEMPERATURE30 degrees on average, hot!"
    exit 1
fi

if [[ $TEMPERATURE30 -lt -10 ]]; then
    echo "It's cold out there today!"
    echo "$TEMPERATURE30 degrees on average, brrr!"
    exit 2
fi

echo "Just right. A perfect $TEMPERATURE30"
exit 0
```

Which means that you can execute the script and any values that are too high or
too low cause a unique error code. If the temperature is fine, script exists with
success code. Note that for an actual monitoring of critical values you may find
the `--poll min` or `--poll max` parameters more informative.

Now, regarding monitoring systems like Cacti: such systems usually allow a value to
be collected via a script. Running the `poll` command from **Numero-log** as script,
and selecting the value to graph (for example, `average` over the last 30 values)
will cause that monitoring system to grab the value that is output and use that in
its own storages.

*This means that you can use Numero-log to record values at any given intervals and
from any place you desire, for example distributed build systems or monitoring
stations, and make your "main" monitoring system pull in those values from a single
place and only receive an average; smoothing out the polled values.*

For example: **Numero-log** will allow you to record values at very low intervals,
and from as many hosts as you desire, but Cacti usually requires either a one- or
five- minute average polling frequency and in the default setup would have to poll
each and every host. So, recording the very frequent measurements in **Numero-log**
and reading the average means you're "pre-processing" the statistical data to make
it a single, graph-friendly unit of measurement with fixed polling intervals (and
as long term storage as you want).

Security
--------

**Numero-log** was built for public access via the HTTP part and imposes no
authentication requirements when **reading** data. If you only want secure
access to the data make sure you set up your own HTTP server and protect it
accordingly. The command line utility allows you to create any number of named
trackings and works by issuing a single token when first creating the database
for your package. The token is only returned in the very first response and must
be saved and reused when reporting new data to the remote storage.

### Tokens

**Numero-log** has two ways to operate using tokens. The first is to simply provide
the token on the command line or as parameter for various functions - the second is
to rely on a specially named token file named: `.numerolog-token-{sha1:packagename}`.
The `sha1` is added for those projects that integrate with multiple pages; as well as
sanitize the value for a proper filename regardless of package name.

The first mode, providing the token manually, requires that you use the `token`
parameter in every request. The second mode allows the token to be automatically
generated by the remote host and delivered to the client (which then stores it in the
mentioned file). If your package wants an automatically generated token simply leave
out the `token` in the very first `save` action you perform on the first counter in
the package. The generated token can then be backed up, encrypted, shared with other
devs, used in continuous integration etc.

**The token is transmitted in cleartext**. It is not considered sensitive information
beyond the fact that you should not share it publicly - and when you need to use it
in for example continuous integration, encrypt and decrypt it using the methods
available on the CI platform. However you twist it, the client still must know about
the token in clear text format - which means that any additional layer of encryption
of said key becomes redundant. That being said: if you want an additional layer of
security you can, for example, set up your own **Numero-log** remote server and use
any type of security you like, for example IP restrictions (and make every token a
simple dummy value).
