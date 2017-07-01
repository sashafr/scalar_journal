<?
function getDescription($htmlVal) {
	$descArray = array();
	preg_match_all('/\<meta.name="(\w*)".content="(.*)"/', $htmlVal, $descArray);
	$arrayValA = $descArray[0];
	$arrayValB = $arrayValA[0];
	//print_r($arrayValB);
	$finalResult = preg_replace('/<meta name="description" content="/', '', $arrayValB);
	$finalResult = substr($finalResult, 0, strlen($finalResult)-1);
	//print_r($finalResult);
	return $finalResult;
}

$htmlTestString = "<head>
<title>Test</title>
<link href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/mediaelement/css/annotorious.css\" rel=\"stylesheet\" type=\"text/css\"><link href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/mediaelement/mediaelement.css\" rel=\"stylesheet\" type=\"text/css\"><base href=\"http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index.8\">
<meta name=\"description\" content=\"To be, or not to be, that is the question: Whether 'tis nobler in the mind to suffer The slings and arrows of outrageous fortune, Or to take Arms against a Sea of troubles, And by opposing end them: to die, to sleep No more; and by a sleep, to say we end the heart-ache, and the thousand natural shocks that Flesh is heir to?\">
<meta name=\"viewport\" content=\"initial-scale=1, maximum-scale=1\">
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<meta property=\"og:title\" content=\"Test - Joe Pires: Test\">
<meta property=\"og:site_name\" content=\"Test - Joe Pires\">
<meta property=\"og:url\" content=\"http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index\">
<meta property=\"og:description\" content=\"Lorem ipsum dolor sit amet, modo noster aliquid an mea. Eu vim nominati democritum, has quando iisque cu. Id eos falli corrumpit disputando, mea ex vide delectus. Postea vocibus at vix, nullam forensibus est ...\">
<meta property=\"og:image\" content=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/scalar_logo_300x300.png\">
<meta property=\"og:type\" content=\"article\">
<link rel=\"canonical\" href=\"http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/index\">
<link rel=\"shortcut icon\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/favicon_16.gif\">
<link rel=\"apple-touch-icon\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/favicon_114.jpg\">
<link id=\"urn\" rel=\"scalar:urn\" href=\"urn:scalar:version:141\">
<link id=\"view\" href=\"plain\">
<link id=\"default_view\" href=\"plain\">
<link id=\"primary_role\" rel=\"scalar:primary_role\" href=\"http://scalar.usc.edu/2012/01/scalar-ns#Tag\">
<link id=\"book_id\" href=\"5\">
<link id=\"parent\" href=\"http://dev.upenndigitalscholarship.org/scalar/test---joe-pires/\">
<link id=\"approot\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/\">
<link id=\"flowplayer_key\" href=\"\">
<link id=\"soundcloud_id\" href=\"\">
<link id=\"recaptcha_public_key\" href=\"\">
<link id=\"CI_elapsed_time\" href=\"0.1085\">
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/css/reset.css\">
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/css/bootstrap.min.css\">
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/css/bootstrap-accessibility.css\">
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/css/common.css\">
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/css/responsive.css\">
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/css/scalarvis.css\">
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/css/header.css\">
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/css/screen_print.css\" media=\"screen,print\">
<script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/js/jquery.rdfquery.rules-1.0.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/js/jquery.RDFa.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/js/form-validation.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/nav/jquery.scalarrecent.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/cookie/jquery.cookie.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/api/scalarapi.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/spinner/spin.min.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/d3/d3.min.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/mediaelement/froogaloop.min.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/mediaelement/annotorious.debug.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/mediaelement/jquery.mediaelement.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/mediaelement/jquery.jplayer.min.js\"></script><script src=\"//www.google.com/recaptcha/api/js/recaptcha_ajax.js\"></script><script src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/widgets/replies/replies.js\"></script><script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/js/jquery-1.7.min.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/js/yepnope.1.5.3-min.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/js/yepnope.css.js\"></script>
<script type=\"text/javascript\" src=\"https://maps.googleapis.com/maps/api/js?key=\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/arbors/html5_RDFa/js/html5shiv/dist/html5shiv.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/bootstrap.min.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/jquery.bootstrap-modal.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/jquery.bootstrap-accessibility.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/main.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/jquery.dotdotdot.min.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/jquery.scrollTo.min.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarheader.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarpage.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarmedia.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarmediadetails.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarindex.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarhelp.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarcomments.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarsearch.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarvisualizations.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarstructuredgallery.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/scalarwidgets.jquery.js\"></script>
<script type=\"text/javascript\" src=\"http://dev.upenndigitalscholarship.org/scalar/system/application/views/melons/cantaloupe/js/jquery.tabbing.js\"></script>
<style></style><script type=\"text/javascript\" charset=\"UTF-8\" src=\"https://maps.googleapis.com/maps-api-v3/api/js/29/2/common.js\"></script><script type=\"text/javascript\" charset=\"UTF-8\" src=\"https://maps.googleapis.com/maps-api-v3/api/js/29/2/util.js\"></script><script type=\"text/javascript\" charset=\"UTF-8\" src=\"https://maps.googleapis.com/maps-api-v3/api/js/29/2/stats.js\"></script><script type=\"text/javascript\" charset=\"UTF-8\" src=\"https://maps.googleapis.com/maps/api/js/AuthenticationService.Authenticate?1shttp%3A%2F%2Fdev.upenndigitalscholarship.org%2Fscalar%2Ftest---joe-pires%2Findex&amp;callback=_xdc_._3wulof&amp;token=26851\"></script></head>";

getDescription($htmlTestString);

?>