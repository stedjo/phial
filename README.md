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
  $app->route('/hello/:name', function($name) use($app) {

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
  $app->route('/hello/:name', function($name) use($app) {

        // do cool stuff
        return "Hello {$name}!";

  });
```
In the example above we expect something after the /hello/ path in URL and it will be
passed to the first argument of the clousre function. You can name it as you prefer.


## Templating
The template engine Phial uses is called PHP Plates. [Full documentation](http://platesphp.com/)

This help will cover the basics of templating.

The first thing you need to do is to place a **.phtml** file into the **templates/** folder.
Inside this template file you can write both html and php code.
If you want to pass some data to the template you need to specify it using the bind() function in your app.

```php
		
	$app->bind('fullname', $var);
	
```

In this case you are passing a $fullname variable to the template and it's content is $var;
Now you will be able to print the variable in your template like this:

```html

	<h1>Here is the variable contents</h1>
	<p>
		Hello <?=$fullname?>!
	</p>

```


Phial + Plates lets you use templates inheritance, so to say, you can use the layouts.
Define a layout file putting the section('content') somewhere in a template:

```php
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Phial :)</title>
	</head>
	<body>

		<?=$this->section('content')?>
	
	</body>
	</html>
	

```

At this point this template file can be used as a layout. To do so, just call it from the partial template file like this:

```php

	<? $this->layout('base') ?>
	
	<h2>This is a partial template injected into base.phtml layout</h2>

```






## Database
Phial uses the mighty phpactiverecord library as default ORM layer.

### Connecting
To configure the connection you just need to uncomment the database section in **config/default.php** file and change the parameters accordingly to your mysql config.

Phial will automatically connect your app to the socket and expose all the functions of phpactiverecord to you.

### Models
The first thing you need to do is to create a model file in the **models/** directory.
The table on the database should have a plural form name (categories)
while the model file should have a singular form name (category.php).

Follow the instruction on phpactiverecord website on how to write these models
and how to bind relationships between them.

### Query

Now that models are all in place let's see how a query works:

```php

	// finds an author with id = $author_id
	$author = Author::find($author_id);
	
	// fetches all the books related to that author
	$books = $author->books;
	
```



## Scaling to large

to be continued....
