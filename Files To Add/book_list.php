<?if (!defined('BASEPATH')) exit('No direct script access allowed')?>
<?$this->template->add_css(path_from_file(__FILE__).'book_list.css')?>
<?

/* Filter out the description tags, so that it doesn't get shown in the index page
 * JP
 */
function filterOutDescTags($description) {

	if (strpos($description, "DescTags:")) {
        $description = preg_replace('/DescTags: .*/', '', $description);
    } else if (strpos($description, "Desc Tags:")) {
        $description = preg_replace('/Desc Tags: .*/', '', $description);
    } else if (strpos($description, "Description Tags: ")) {
        $description = preg_replace('/Description Tags: .*/', '', $description);
    } else if (strpos($description, "Tags: ")) {
        $description = preg_replace('/Tags: .*/', '', $description);
    } else {
	}
	return $description;
}

/* Make the date and time prettier in the table of contents
 * JP
 */
function changeLookDateTime($dateTimeString) {
	// As of right now, just get rid of the timestamp
	// If more needs to be done, I would like a Day-Month-Year
	// Date Structure (talk to Sasha about this)
	$newDateTimeString = preg_replace('/[0-9]{2}:[0-9]{2}:[0-9]{2}/', '', $dateTimeString);
	return $newDateTimeString;
}

/* Filter the html to get the description
 * JP
 */
function getDescription($htmlVal) {
	$descArray = array();
	preg_match_all('/\<meta.name="(\w*)".content="(.*)"/', $htmlVal, $descArray);
	$arrayValA = $descArray[0];
	$arrayValB = $arrayValA[0];
	$finalResult = preg_replace('/<meta name="description" content="/', '', $arrayValB);
	$finalResult = substr($finalResult, 0, strlen($finalResult)-1);
	return $finalResult;
}


/* Filter out the Table Of Contents Page.
 * This will allow my link for the Table of Contents to be shown
 * However, since we're not doing a book version of the table
 * of contents, we'll just leave this
 * JP
 */
function filterFunction($a) {
	$trimmedTitle = trim($a->title);
	$trimmedTitle = strtolower($trimmedTitle);
	return ($trimmedTitle != "table of contents");
}
function filterTableOfContents($books) {
	return array_filter($books, 'filterFunction');
}

/* Simple, linear time algorithm to search for
 * an array object with the correct title
 * JP
 */
function checkIfThere($books, $titleToFind) {
	$returnVal = False;
	$titleToFind = trim($titleToFind);
	foreach($books as $bookVal) {
		$trimmedTitle = trim($bookVal->title);
		if (strcmp($titleToFind, $trimmedTitle) == 0) {
			$returnVal = True;
			break;
		}
	}
	return $returnVal;
}


function print_books($books, $is_large=false, $public=false) {
	echo '<ul class="book_icons">';
	foreach ($books as $row) {
		$created       = $row->created;
		$uri 		   = confirm_slash(base_url()).$row->slug;
		// Go to the article page to get the description JP
		$content = file_get_contents($uri);
		// Get description, and filter JP
		$description = getDescription($content);
		$description = filterOutDescTags($description);
		$title		   = trim($row->title);
		$book_id       = (int) $row->book_id;
		$thumbnail     = (!empty($row->thumbnail)) ? confirm_slash($row->slug).$row->thumbnail : null;
		$is_live       = ($row->display_in_index) ? true : false;
		if (empty($thumbnail) || !file_exists($thumbnail)) $thumbnail = path_from_file(__FILE__).'default_book_logo.png';
		$authors = array();
		foreach ($row->users as $user) {
			if ($user->relationship!=strtolower('author')) continue;
			if (!$user->list_in_index) continue;
			$authors[] = $user->fullname;
		}
		echo '<li><a href="'.$uri.'"><img class="book_icon'.(($is_large)?'':' small').'" src="'.confirm_base($thumbnail).'" /></a><h4><a href="'.$uri.'">'.$title.'</a></h4>';
		if (count($authors)) {
			$printString = '';
			foreach($authors as $authorVal) {
				if(count($authors) == 1) {
					$printString = $printString.$authorVal;
				} else {
					$printString = $printString.$authorVal.', ';
				}
			}
			$printString = '<strong>'.$printString.'</strong>';
			echo $printString;
			
		}
		// Add the data appropriately JP
		if ($public) {
			if (count($authors)) {
				echo "<br />";
			}
			echo "<strong>Description:</strong> ";
			echo $description;
		}
		if (count($authors) || $public) {
			echo '<br />';
		}
		$dateVal = changeLookDateTime($created);
		$dateVal = '<strong>'.$dateVal.'</strong>';
		echo $dateVal;
		echo '</li>';
	}
	echo '</ul>';
}

?>

<?if (isset($_REQUEST['user_created']) && '1'==$_REQUEST['user_created']): ?>
<div class="saved">
  Thank you for registering your <?=$cover_title?> account
  <a href="<?=$uri?>" style="float:right;">clear</a>
</div>
<? endif ?>
<? if ($this->config->item('index_msg')): ?>
<div class="saved msg"><?=$this->config->item('index_msg')?></div>
<? endif ?>
<?
if(!$login_is_super) {
	foreach ($other_books as $key => $row) {
		$is_live =@ ($row->display_in_index) ? true : false;
	    if(!$is_live){
    	    unset($other_books[$key]);
	    }
	}
}
?>
<div id="other_books"<?=(($login->is_logged_in)?'':' class="wide"')?>>
<?
// Generate the table of contents here
// Just make featured books impossible for now
// JP
if (count($featured_books) < 0) {
	echo '<h3>'.lang('welcome.featured_books').'</h3>';
	print_books($featured_books);
	echo '<br clear="both" />';
}
?>
<!-- Remove The View All Button, So It Can Be Done Automatically 
	 JP -->

<h3><?=lang('welcome.other_books')?></h3>
<form action="<?=base_url()?>" id="book_list_search">
<div>
<div><input type="text" name="sq" class="generic_text_input" value="<?=(isset($_REQUEST['sq'])?trim(htmlspecialchars($_REQUEST['sq'])):'')?>" /></div>
<div><input type="submit" class="generic_button" value="Search" /></div>
<!--<div><button type="submit" class="generic_button" value="1" name="view_all" >View All</button></div>-->
</div>
</form>
<?
if (isset($book_list_search_error)) {
	echo '<p class="error">'.$book_list_search_error.'</p>';
}
?>
<br clear="both" />
<? if (count($other_books) > 0) print_books($other_books, true, true) ?>
</div>

<?
if ($login->is_logged_in) {
	echo '<div id="user_books"><h3>Your Books</h3>';
	$newUserBooks = filterTableOfContents($user_books);
	if (count($newUserBooks) > 0) {
		echo '<ul class="book_icons">';
		print_books($newUserBooks, true);
	} else {
		echo '<p>You haven\'t created any books yet.</p>';
	}
	echo '</div>';
}
?>
<br clear="both" />