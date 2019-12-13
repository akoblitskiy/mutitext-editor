<?php
ini_set('display_errors', 'On');
include 'src/autoload.php';
use Multitext\Core;
use Multitext\Request;

$config = require 'config/load.php';
$request = Request::generateFromGlobals();
$core = new Core($config);
$core->handle($request);