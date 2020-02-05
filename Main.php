<?php

require_once('./Invoice.php');

$invoicesJson = file_get_contents('invoices.json');
$invoices = json_decode($invoicesJson, true);

$playsJson = file_get_contents('plays.json');
$plays = json_decode($playsJson, true);

$invoice = new Invoice();
echo $invoice->statement($invoices[0], $plays);
