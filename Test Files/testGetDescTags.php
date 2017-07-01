<?php
	function isolateDescTags($description) {
		preg_match('/DescTags: (.*)/', $description, $matches);
		return $matches[1];
	}

	function getDescTags($isolatedTagSection) {
		$isolatedTagSection = strtolower($isolatedTagSection);
		$tagArray = explode(", ", $isolatedTagSection);
		return $tagArray;
	}

	$testVal = "To be, or not to be, that is the question: Whether 'tis nobler in the mind to suffer The slings and arrows of outrageous fortune, Or to take Arms against a Sea of troubles, And by opposing end them: to die, to sleep No more; and by a sleep, to say we end the heart-ache, and the thousand natural shocks that Flesh is heir to? DescTags: this, is, a, tag";

	$isolatedVal = isolateDescTags($testVal);
	$testTagArray = getDescTags($isolatedVal);

?>