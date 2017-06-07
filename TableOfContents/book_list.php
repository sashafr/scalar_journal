<!-- Edit for the table of contents -->

<?if (!defined('BASEPATH')) exit('No direct script access allowed')?>
<?$this->template->add_css(path_from_file(__FILE__).'book_list.css')?>

<?
/* Filter out the Table Of Contents Page.
 * This will alloy my link to be shown
 * JP
 */
function filterFunction($a) {
	$trimmedTitle = trim($a->title);
	$trimmedTitle = strtolower($trimmedTitle);
	return ($trimmedTitle == "table of contents");
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
		}
	}
	return $returnVal;
}


function print_books($books, $is_large=false) {
	echo '<ul class="book_icons">';
	foreach ($books as $row) {
		$uri 		   = confirm_slash(base_url()).$row->slug;
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
			echo implode(', ',$authors);
			echo "<br />";
		}
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
if (count($featured_books) > 0) {
	echo '<h3>'.lang('welcome.featured_books').'</h3>';
	print_books($featured_books);
	echo '<br clear="both" />';
}
?>
<h3><?=lang('welcome.other_books')?></h3>
<form action="<?=base_url()?>" id="book_list_search">
<div>
<div><input type="text" name="sq" class="generic_text_input" value="<?=(isset($_REQUEST['sq'])?trim(htmlspecialchars($_REQUEST['sq'])):'')?>" /></div>
<div><input type="submit" class="generic_button" value="Search" /></div>
<div><button type="submit" class="generic_button" value="1" name="view_all" >View All</button></div>
</div>
</form>
<?
if (isset($book_list_search_error)) {
	echo '<p class="error">'.$book_list_search_error.'</p>';
}
?>
<br clear="both" />
<? if (count($other_books) > 0) print_books($other_books) ?>
</div>

<?
if ($login->is_logged_in) {
	echo '<div id="user_books"><h3>Your Books</h3>';
	if (count($user_books) > 0) {
		//print_r($user_books);
		echo '<ul class="book_icons">';
		$user_books = filterTableOfContents($user_books);
		print_books($user_books, true);
		/* Generate the link to the Table of Contents
		 * Tested. If there is no Table of Contents 
		 * book created, it won't show (so no one will)
		 * accidently click a link to a nonexistent page
		 * JP
		 */
		if (checkIfThere($user_books, "Table Of Contents")) {
			echo '<li><a href="http://dev.upenndigitalscholarship.org/scalar/table-of-contents"><img class="book_icon" src="http://dev.upenndigitalscholarship.org/scalar/system/application/views/modules/book_list/default_book_logo.png"></a>';
			echo '<h4><a href="http://dev.upenndigitalscholarship.org/scalar/table-of-contents"><span data-hypothesis="true" data-auto-approve="true" data-email-authors="true" data-joinable="true">Table Of Contents</span></a></h4></li>';
		}
	} else {
		echo '<p>You haven\'t created any books yet.</p>';
	}
	echo '</div>';
}
?>
<br clear="both" />