<?php
require('vendor/autoload.php');
require('config/config.php');


// Get the transactions from the file
if(count($argv)<2)
	// throw exception
	die("You must give a file of Transactions");

$transactions = new \App\Transactions;

$transactions->setFile($argv[1]);
$result = $transactions->handle();

foreach ($result as $value) {
	echo $value."\n";
}