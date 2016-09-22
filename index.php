<?php

// require a Phial
require_once __DIR__.'/lib/Phial.php';

// create a new instance
$app = new Phial(__DIR__);

// route something cool
$app->route('/', function() use($app) {

	// render a template with data
	return $app->render('welcome');

});

// and done
$app->run();