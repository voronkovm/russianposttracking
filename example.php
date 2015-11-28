<?php
require "russianposttracking.inc.php";

$client = new RussianPostTracking('login', 'pass');
$rows = $client->getOpeartionHistory('tracknumber');
foreach($rows as $r)
	print_r($r);