# Phial

Phial is a blazing fast php micro-framework heavily inspired by python's Flask and Bottle.

## Install

As simple as:

1.  git clone [https://github.com/metalogico/phial.git]() phial
2.  cd phial
3.  composer install
4.  _done._

point your browser to [http://localhost/phial/]() and you will see the landing page.

* * *

## Hello World!

Starting from the initial Phial installation files, just remove everything inside the index.php file located in the root folder of your Phial installation.
Replace it's contents with this code:

```php
  // hello world example
  $app->route('/hello/(.*)', function($name) use($app) {

        return "Hello {$name}!";

  });
```

Now point your browser to [http://localhost/phial/hello/john]()





* * *

## What's inside ?

*   phial core framework   ( [github](https://github.com/metalogico/phial) )
*   phpactiverecord ( [website](http://www.phpactiverecord.org) )
*   plates php templating framework ( [website](http://platesphp.com) )

***

## Application
The concept of **application** in Phial is very important. There are 2 types of applications:

1. The basic application (or default application), the one where you create the Phial() object and call the run() function
2. The custom applications. These are located inside the **apps/** directory and extend Phial class.

***

## Configuration

Phial configuration can be extended to suite all your needs but 
if you want change the basic application configuration just edit the **/config/default.php** file.

Here you will find the database config parameters, pretty straightforward actually.

Inside your application you can call the  **$app->get_config( $key )** function to retrieve a config option.

***

## Routing

There are 2 types of routing in Phial:

1. Custom Routes
2. Automagic Routes ( explained later in this document, loot at the _Scaling to large_ section below )

The custom routes are those you can see in almost every other modern framework. You can define them in your application (both default and custom apps) using a closure syntax like this:

```php
  // define a route
  $app->route('/', function() use($app) {

        // do cool stuff

  });
```

You can also pass parameters to your routes like this:

```php
  // define a route
  $app->route('/hello/(.*)', function($name) use($app) {

        // do cool stuff
        return "Hello {$name}!";

  });
```
In the example above we expect something after the /hello/ path in URL and it will be
passed to the first argument of the clousre function. You can name it as you prefer.


## Templating

## Database

## Scaling to large

to be continued....
