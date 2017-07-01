<?php 
function getVersionNumber($htmlVal) {
	$checkVal = preg_match('/<base [^>]*href=\"(.*?)\"/', $htmlVal)
	print($checkVal);
	return "A";
}

$htmlTestVal = "<ul class=\"book_icons\">
				<!--?xml version=\"1.0\"-->
				<title>Intro to whatever</title>
				<base href=\"http://dev.upenndigitalscholarship.org/scalar/digital-archaeology-tutorials/index.20\">";


?>