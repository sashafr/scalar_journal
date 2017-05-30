<?php
	// For the extraction, set up some sql queries
	function query() {
        $query = ("SELECT book_id, title, subtitle, url_is_public, publisher, user, created, is_featured, description FROM scalar_db_books;");
        $result = mysql_query($query);
    }

	// As a general note, all bools are represented as 0 or 1
	class Content{
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
			this->$bookID = $bookID; // int 
			this->$title = $title; // string
			this->$subtitle = $subtitle; // string
			this->$urlIsPublic = $urlIsPublic; // bool
			this->$publisher = $publisher; // string
			this->$user = $user; // string
			this->$created = $created; // dateTime/string
			this->$isFeatured = $isFeatured; // bool
			this->$description = $description; // string
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
	function compare($a, $b) {
		return strcmp($a->created, $b->created);
	} 

	function sortContents($contentsArray) {
		return usort($contentsArray, "cmp");
	}

?>