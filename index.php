<?php

require_once __DIR__ . '/vendor/autoload.php';

use AfolabiAbass\App\SentimentAnalysis;

define( 'ABSPATH', dirname(__FILE__) . '/' );

// Increase execution time and memory limit
ini_set('memory_limit','96550M');
ini_set('max_execution_time', 18000);

// Run on application load through web
(new SentimentAnalysis)
    ->process()
    ->load()
    ->prepare()
    ->split()
    ->train()
    ->predict()
    ->getAccuracy();
