<?php
	/* Way to extract and sort published content
	 * Just make sure that all published material was published
	 * After December 31, 999
	 */

	// For the extraction, set up some sql queries

	/* TODO - 30 May
	 * - Test the query and make sure it's correct
	 * Look through the php pages to see how the database is accessed
	 * Find a good template (index.php might be a good place to look through)
	 */
	/*function query() {
        $query = ("SELECT book_id, title, subtitle, url_is_public, publisher, user, created, is_featured, description FROM scalar_db_books;");
        $result = mysql_query($query);
    }*/

	// As a general note, all bools are represented as 0 or 1
	class Content {
		public $bookID;
		public $title;
		public $subtitle;
		public $urlIsPublic;
		public $publisher;
		public $user;
		public $created;
		public $isFeatured;
		public $description;

		public function __construct($bookID, $title, $subtitle, $urlIsPublic, $publisher, $user, $created, $isFeatured, $description) {
			$this->bookID = $bookID; // int 
			$this->title = $title; // string
			$this->subtitle = $subtitle; // string
			$this->urlIsPublic = $urlIsPublic; // bool
			$this->publisher = $publisher; // string
			$this->user = $user; // string
			$this->created = $created; // dateTime/string
			$this->isFeatured = $isFeatured; // bool
			$this->description = $description; // string
		}

		public function getMessage() {
			return "This is bookID " . $this->bookID;
		}
	}
	// Filter out the unpublished ones
	function filterFunction($a) {
		return ($a->isFeatured == 1);
	}

	function filterArr($contentsArray) {
		return array_filter($contentsArray, 'filterFunction');
	}

	// Compare The Contents
	function cmp($a, $b) {
		return strcmp($a->created, $b->created);
	} 

	// Test Cases
	$testTitle = "Test Title";
	$testSubtitle = "Subtitle";
	$testURLIsPublic = 1;
	$testPublisher = "Jim Sterling";
	$testUser = "Jim Sterling";
	$testDescription = "Description";

	$testObj1 = new Content(1, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "2017-09-11", 1, $testDescription);
	$testObj2 = new Content(2, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "1821-11-11", 0, $testDescription);
	$testObj3 = new Content(3, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "1921-09-11", 1, $testDescription);
	$testObj4 = new Content(4, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "1491-09-11", 0, $testDescription);
	$testObj5 = new Content(5, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "2000-09-11", 1, $testDescription);
	$testObj6 = new Content(6, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "2001-09-11", 0, $testDescription);
	$testObj7 = new Content(7, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "1888-09-11", 1, $testDescription);
	$testObj8 = new Content(8, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "1209-09-11", 0, $testDescription);
	$testObj9 = new Content(9, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "1919-09-11", 1, $testDescription);
	$testObj10 = new Content(10, $testTitle, $testSubtitle, $testURLIsPublic, $testPublisher, $testUser, "1789-09-11", 0, $testDescription);

	$testArray = array($testObj1, $testObj2, $testObj3, $testObj4, $testObj5, $testObj6, $testObj7, $testObj8, $testObj9, $testObj10);

	$filteredArray = filterArr($testArray);

	print_r($filteredArray);
	print_r("\n");

	usort($filteredArray, "cmp");

	print_r($filteredArray);
	print_r("\n");
?>