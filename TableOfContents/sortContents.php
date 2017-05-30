<?php
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

	function compare($a, $b) {
		return strcmp($a->created, $b->created);
	} 

	function sortContents($contentsArray) {
		return usort($contentsArray, "cmp");
	}

?>