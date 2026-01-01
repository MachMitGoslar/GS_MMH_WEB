<?php

/**
 * Test script for debugging with Xdebug
 */

$name = "Kirby Application";
$version = "4.7.2";

echo "Welcome to $name version $version\n";

function addNumbers($a, $b)
{
    $result = $a + $b;

    return $result;
}

$x = 10;
$y = 20;
$sum = addNumbers($x, $y);

echo "The sum of $x + $y = $sum\n";

// Test with Kirby
if (file_exists('kirby/bootstrap.php')) {
    echo "Kirby framework detected\n";
    require 'kirby/bootstrap.php';

    // This would be where you'd debug your Kirby application
    echo "Kirby loaded successfully\n";
} else {
    echo "Kirby not found in this location\n";
}

echo "Debug test complete\n";
