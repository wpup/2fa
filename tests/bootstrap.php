<?php

// Load Composer autoload.
require __DIR__ . '/../vendor/autoload.php';

// Load Papi loader file as plugin.
WP_Test_Suite::load_plugins( __DIR__ . '/../plugin.php' );

// Run the WordPress test suite.
WP_Test_Suite::run();
