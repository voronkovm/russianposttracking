<?php
require "russianposttracking.inc.php";

$client = new RussianPostTracking('login', 'pass');

$rows = $client->getOperationHistory('tracknumber');
foreach($rows as $r)
	print_r($r);
	
$rows = $client->PostalOrderEventsForMail('tracknumber');
foreach($rows as $r)
	print_r($r);