<?php
/**
 * Scalar
 * Copyright 2013 The Alliance for Networking Visual Culture.
 * http://scalar.usc.edu/scalar
 * Alliance4NVC@gmail.com
 *
 * Licensed under the Educational Community License, Version 2.0
 * (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 * http://www.osedu.org/licenses/ECL-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an "AS IS"
 * BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

$uri =@ urldecode($_REQUEST['uri']);
$xsl =@ urldecode($_REQUEST['xsl']);
$header =@ urldecode($_REQUEST['header']);

// Validate URI against list of archive URLs
$domain = parse_url($uri, PHP_URL_HOST);
$archives_path = __DIR__.'/xsl/archives.rdf';
$archives_rdf = file_get_contents($archives_path);
$is_valid_domain = ($domain==$_SERVER['HTTP_HOST'] || false!==strstr($archives_rdf,$domain)) ? true : false;
if (!$is_valid_domain) die ('Invalid domain');

header("content-type: text/xml");

if(substr($xsl, 0, 8)=='https://') $xsl = 'http://'.substr($xsl, 8);

$format =@ ($_REQUEST['format']=='json') ? 'json' : 'xml';

if (empty($uri)) die ('Invalid URI');

$uri = str_replace(' ', '+', $uri);

function jsonToXML($json) {
	$return = '<?xml version="1.0" ?>';
	$return .= '<root>';
	foreach ($json as $field => $row) {
		$return .= '<node>';
		$return .= jsonToXMLNodes($row);
		$return .= '</node>';
	}
	$return .= '</root>';
	return $return;
}
function jsonToXMLNodes($row) {
	$return = '';
	if (!is_array($row)) return $return;
	foreach ($row as $field => $value) {
		if (is_numeric($field)) $field = 'node';
		$field = str_replace(' ','_',$field);
		$field = str_replace('@','',$field);
		$field = str_replace('#','',$field);
		$field = str_replace(':','',$field);
		$return .= '<'.$field.'>';
		if ('array'==gettype($value)) {
			$return .= jsonToXMLNodes($value);
		} else {
			if ('null'==$value) $value = '';
			$return .= '<![CDATA['.trim($value).']]>';
		}
		$return .= '</'.$field.'>';
	}
	return $return;
}
function json_clean_line_breaks($str) {
	$str = str_replace("\r\n", "\n", $str);
    $str = str_replace("\r", "\n", $str);
    $str = str_replace("\n", "\\n", $str);
	return $str;
}
function stripInvalidXml($value) {  // http://stackoverflow.com/questions/3466035/how-to-skip-invalid-characters-in-xml-file-using-php
    $ret = "";
    $current;
    if (empty($value)) {
        return $ret;
    }
    $length = strlen($value);
    for ($i=0; $i < $length; $i++) {
        $current = ord($value{$i});
        if (($current == 0x9) ||
            ($current == 0xA) ||
            ($current == 0xD) ||
            (($current >= 0x20) && ($current <= 0xD7FF)) ||
            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
            (($current >= 0x10000) && ($current <= 0x10FFFF))) {
            $ret .= chr($current);
        } else {
            $ret .= " ";
        }
    }
    return $ret;
}

if ('json'==$format) {
	if ('https' == substr($uri, 0, 5)) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($response, true);
   } else {
   		session_start();
		$opts = array('http' => array('header'=> 'Cookie: ' . $_SERVER['HTTP_COOKIE']."\r\n"));  // Send cookies for session_start of target
		$context = stream_context_create($opts);
		session_write_close();
	 	$json = json_decode(json_clean_line_breaks(file_get_contents($uri, false, $context)), true);
   }
   $xml = jsonToXML($json);
   $xml = stripInvalidXml($xml);
} else {
	if (!empty($header)) {
		$opts = array(
		  'http'=>array(
		    'header'=>$header
		  )
		);
		$context = stream_context_create($opts);
		$xml = file_get_contents($uri, false, $context);
	} else {
		$xml = file_get_contents($uri);
	}
}
if (false===$xml) {
	echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"></rdf:RDF>';
	exit;
}

if (empty($xsl)) {
	echo $xml;
	exit;
}

$is_html = (false!==stripos(substr(trim($xml), 0, 200), '<html')) ? true : false;

$XML = new DOMDocument();
if ($is_html) {
	@$XML->loadHTML( $xml );
} else {
	$XML->loadXML( $xml );
}

$xslt = new XSLTProcessor();
$XSL = new DOMDocument();
$XSL->load( $xsl , LIBXML_NOCDATA);
$xslt->importStylesheet( $XSL );

$xml = $xslt->transformToXML( $XML );

echo $xml;
exit;
?>