<?php
function changeLookDateTime($dateTimeString) {
	// As of right now, just get rid of the timestamp
	// If more needs to be done, I would like a Day-Month-Year
	// Date Structure
	$newDateTimeString = preg_replace('/[0-9]{2}:[0-9]{2}:[0-9]{2}/', '', $dateTimeString);
	print("HELLO\n");
	return $newDateTimeString;
}

$dateString = "2017-02-08 13:06:20";
$newString = changeLookDateTime($dateString);

print($newString);
?>