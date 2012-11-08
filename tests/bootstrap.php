<?php
require 'PHPUnit/TestMore.php';
require 'Universal/ClassLoader/BasePathClassLoader.php';
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array( 
    'src', 'vendor/pear',
));
$classLoader->useIncludePath(false);
$classLoader->register();
