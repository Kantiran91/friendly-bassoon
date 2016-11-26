<?php

require( 'vendor/autoload.php' );
require_once('FileSystem.php');
require_once ('src/Extractor.php');

use Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

// Load environment variables from the .env file
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$fileStorage = new FileSystem();

$extractor = new Extractor(getenv('MAIL_USER'),getenv('MAIL_PASS'),getenv('MAIL_HOST'),getenv('MAIL_PORT'));

// Read filters
$filters = Yaml::parse($fileStorage->readFile(__DIR__ . '/filters.yaml')) ? : [];
var_dump($filters);
$extractor->setFilters($filters);

// Use a general Filesystem
$extractor->setFileStorage($fileStorage);

$extractor->downloadAttachments();