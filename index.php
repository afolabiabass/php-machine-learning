<?php

require_once __DIR__ . '/vendor/autoload.php';

use AfolabiAbass\App\SentimentAnalysis;

define( 'ABSPATH', dirname(__FILE__) . '/' );

(new SentimentAnalysis)
    ->process()
    ->load()
    ->prepare()
    ->split()
    ->train()
    ->predict()
    ->getAccuracy();
