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

/**
 * @projectDescription		Book controller for outputting the HTML for Scalar's front-end
 * @author					Craig Dietrich
 * @version					2.5
 */

function sortSearchResults($a, $b) {
	$x = strtolower($a->versions[key($a->versions)]->title);
	$y = strtolower($b->versions[key($a->versions)]->title);
    return strcmp($x, $y);
}

class Book extends MY_Controller {

	protected $template_has_rendered = false;
	private $models = array('annotations', 'paths', 'tags', 'replies', 'references');
	private $rel_fields = array('start_seconds','end_seconds','start_line_num','end_line_num','points','datetime','paragraph_num');
	private $vis_views = array('vis', 'visindex', 'vispath', 'vismedia', 'vistag');
	private $fallback_melon = 'honeydew';  // This is independant of the default melon set in the config, which is used for new book creation
	private $fallback_page = 'index';  // The default home page for a book (/index)
	private $max_recursions = 2;  // Get relationships of the current page, and the relationships of those relationships (e.g., get this pages tags, and the pages those tags tag)

	/**
	 * Load the current book
	 */

	public function __construct() {

		parent::__construct();
		$this->load->model('book_model', 'books');
		$this->load->model('page_model', 'pages');
		$this->load->model('version_model', 'versions');
		$this->load->model('annotation_model', 'annotations');
		$this->load->model('path_model', 'paths');
		$this->load->model('tag_model', 'tags');
		$this->load->model('reply_model', 'replies');
		$this->load->model('reference_model', 'references');
		$this->load->library('SendMail', 'sendmail');
		$this->load->library('RDF_Object', 'rdf_object');
		$this->load->library('File_Upload', 'file_upload');
		$this->load->library('statusCodes');
		$this->load->helper('inflector');

		$this->data['book'] = $this->data['page'] = null;
		// Book being asked for
		$this->scope = strtolower($this->uri->segment('1'));
		$this->data['book'] = (!empty($this->scope)) ? $this->books->get_by_slug($this->scope) : null;
		if (empty($this->data['book'])) show_404();	// Book couldn't be found
		$this->set_user_book_perms();
		if (!$this->data['book']->url_is_public && !$this->login_is_book_admin('reader')) $this->require_login(1); // Protect book
		$this->data['book']->contributors = $this->books->get_users($this->data['book']->book_id);
		$this->data['book']->versions = $this->books->get_book_versions($this->data['book']->book_id, true); // TOC
		$this->data['base_uri'] = confirm_slash(base_url()).confirm_slash($this->data['book']->slug);
		// Melon (skin)
		$this->data['melon'] = $this->config->item('active_melon');
		if (!$this->melon_exists($this->data['melon'])) $this->data['melon'] = null;
		if (isset($_GET['m']) && $this->melon_exists($_GET['m'])) {
			$this->data['melon'] = $_GET['m'];
		} elseif ($this->melon_exists($this->data['book']->template)) {
			$this->data['melon'] = $this->data['book']->template;
		}
		if (empty($this->data['melon'])) $this->data['melon'] = $this->fallback_melon;
		$this->load_melon_config($this->data['melon']);
		// Init
		$this->data['views'] = $this->config->item('views');
		$this->data['media_views'] = $this->config->item('media_views');
		$this->data['view'] = key($this->data['views']);
		$this->data['models'] = $this->models;
		$this->data['mode'] = null; // e.g., "editing"
		$this->data['can_edit'] = $this->login_is_book_admin('reviewer');

	}

	/**
	 * Load the current page
	 */

	public function _remap() {

		try {
			// URI segment
			$uri = explode('.',implode('/',array_slice($this->uri->segments, 1)));
			$slug = $uri[0];
			$slug_first_segment = (strpos($slug,'/')) ? substr($slug, 0, strpos($slug,'/')) : $slug;
			if (empty($slug)) {
				header('Location: '.$this->data['base_uri'].$this->fallback_page);
				exit;
			}
			// Ajax login check
			if ('login_status'==$slug_first_segment) return $this->login_status();
			// Load page based on slug
			$page = $this->pages->get_by_slug($this->data['book']->book_id, $slug);
			if (!empty($page)) {
				// Protect
				if (!$page->is_live) $this->protect_book('Reader');
				// Version being asked for
				$version_num = (int) get_version($this->uri->uri_string());
				$this->data['version_datetime'] = null;
				if (!empty($version_num)) {
					$version = $this->versions->get_by_version_num($page->content_id, $version_num);
					if (!empty($version)) $this->data['version_datetime'] = $version->created;
				}
				// Build (hierarchical) RDF object for the page's version(s)
				$settings = array(
								 	'book'         => $this->data['book'],
									'content'      => $page,
									'base_uri'     => $this->data['base_uri'],
									'versions'     => ((!empty($this->data['version_datetime']))?$this->data['version_datetime']:RDF_Object::VERSIONS_MOST_RECENT),
									'ref'          => RDF_Object::REFERENCES_ALL,
									'prov'		   => RDF_Object::PROVENANCE_ALL,
							  		'max_recurses' => $this->max_recursions
								 );
				$index = $this->rdf_object->index($settings);
			    if (!count($index)) throw new Exception('Problem getting page index');
			    $this->data['page'] = $index[0];
			    unset($index);
				// Paywall
				if (isset($page->paywall) && $page->paywall) $this->paywall();
				// If a media page, overwrite the views with the media_views if applicable
				if ('media'==$this->data['page']->type && !empty($this->data['media_views'])) $this->data['views'] = $this->data['media_views'];
				// Set the view based on the page's default view
				$default_view = $this->data['page']->versions[$this->data['page']->version_index]->default_view;
				if (array_key_exists($default_view, $this->data['views'])) $this->data['view'] = $default_view;
			} else {
				$this->data['slug'] = $slug; // Can visit a page even if it hasn't been created yet
			}
			// View and view-specific method (outside of the if/page context above, in case the page hasn't been created yet
			if (array_key_exists(get_ext($this->uri->uri_string()), $this->data['views'])) $this->data['view'] = get_ext($this->uri->uri_string());
			if (in_array($this->data['view'], $this->vis_views)) {
				$this->data['viz_view'] = $this->data['view'];  // Keep a record of the specific viz view being asked for
				$this->data['view'] = $this->vis_views[0];  // There's only one viz page (Javascript handles the specific viz types)
			}
			// View-specific method
			$method_name = $this->data['view'];
			if (method_exists($this, $method_name)) $this->$method_name();
			// URI segment method
			if (method_exists($this, $slug_first_segment) && !array_key_exists($slug_first_segment, $this->data['views'])) $this->$slug_first_segment();

		} catch (Exception $e) {
			header($e->getMessage());
			exit;
		}

		if ($this->template_has_rendered) return;  // Template might be rendered in one of the methods below
		$this->template->set_template($this->config->item('arbor'));
		foreach ($this->template->template['regions'] as $region) {
			$this->template->write_view($region, 'melons/'.$this->data['melon'].'/'.$region, $this->data);
		}
		$this->template->render();

	}

	// Return logged in status in JSON format
	private function login_status() {

		header('Content-type: application/json');
		if ($this->data['login']->is_logged_in) {
			echo '{"is_logged_in":1,"is_author":'.(($this->login_is_book_admin())?'1':'0').',"user_id":'.$this->data['login']->user_id.',"fullname":"'.htmlspecialchars($this->data['login']->fullname).'"}';
			exit;
		} else {
			die('{"is_logged_in":0}');
		}

	}

	// Save a comment (an anonymous new page) with ReCAPTCHA check (not logged in) or authentication check (logged in)
	// This is a special case; we didn't want to corrupt the security of the Save API and its native (session) vs non-native (api_key) authentication
	private function save_anonymous_comment() {

		header('Content-type: application/json');
		$return = array('error'=>'');

		// Validate
		try {
			require_once(APPPATH.'libraries/recaptcha/recaptchalib.php');
			if (!isset($_POST['action'])||'add'!=strtolower($_POST['action'])) throw new Exception('Invalid action');

			// Either logged in or not
			$child_urn   =@ trim($_POST['scalar:child_urn']);
			$title       =@ trim($_POST['dcterms:title']);
			$description =@ trim($_POST['dcterms:description']);
			$content     =@ trim($_POST['sioc:content']);
			$user_id     =@ (int) trim($_POST['user']);

			if (empty($child_urn)) throw new Exception('Could not determine child URN');
			if (empty($title)) throw new Exception('Comment title is a required field');
			if (empty($content)) throw new Exception('Content is a required field');

			// Not logged in
			if (empty($user_id)) {
				$fullname  =@ trim($_POST['fullname']);
				if (empty($fullname)) throw new Exception('Your name is a required field');
				$privatekey = $this->config->item('recaptcha_private_key');
				if (empty($privatekey)) throw new Exception('ReCAPTCHA has not been activated');
  				$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
				if (!$resp->is_valid) throw new Exception('Invalid CAPTCHA answer, please try again');

			// Logged in
			// Note that we're not saving the user as the creator of the page but rather fullname to the attribution field
			} else {
 				$user = $this->users->get_by_user_id($user_id);
 				if (!$user) throw new Exception('Could not find user');
 				if ($user->user_id != $this->data['login']->user_id) throw new Exception('Could not match your user ID with your login session.  You could be logged out.');
 				$fullname = $user->fullname;
 				if (empty($fullname)) throw new Exception('Logged in user does not have a name');
			}

			// Save page
			$save = array();
			$save['book_id'] = $this->data['book']->book_id;
			$save['user_id'] = $user_id;
			$save['title'] = $title;  // for creating slug
			$save['type'] = 'composite';
			$save['is_live'] = $this->books->is_auto_approve($this->data['book']);
			$content_id = $this->pages->create($save);
			if (empty($content_id)) throw new Exception('Could not save the new content');

			// Save version
			$save = array();
			$save['user_id'] = $user_id;
			$save['title'] = $title;
			$save['description'] = '';
			$save['content'] = $content;
			$save['attribution'] = $this->versions->build_attribution($fullname, $this->input->server('REMOTE_ADDR'));
			$version_id = $this->versions->create($content_id, $save);
			if (empty($version_id)) throw new Exception('Could not save the new version');  // TODO: delete prev made content

			// Save relation
			if (!$this->replies->save_children($version_id, array($child_urn), array(0))) throw new Exception('Could not save relation');  // TODO: delete prev made content and version
			// I suppose we could get the newly created node and output as RDF-JSON to sync with the save API return, but since this is a special case anyways...

			// Email authors
			if ($this->books->is_email_authors($this->data['book'])) {
				$this->sendmail->new_comment($this->data['book'], $save, $this->books->is_auto_approve($this->data['book']));
			}

		} catch (Exception $e) {
			$return['error'] =  $e->getMessage();
		}

		$return['moderated'] = ($this->books->is_auto_approve($this->data['book'])) ? 0 : 1;
		echo json_encode($return);
		exit;

	}

	// Tags (list all tags in cloud)
	private function tags() {

		if (strlen($this->uri->segment(3))) return;
		if ($this->data['mode'] == 'editing') return;
		$this->data['book_tags'] = $this->tags->get_all($this->data['book']->book_id, null, null, true);
		for ($j = 0; $j < count($this->data['book_tags']); $j++) {
			$this->data['book_tags'][$j]->versions = array();
			$this->data['book_tags'][$j]->versions[0] = $this->versions->get_single($this->data['book_tags'][$j]->content_id, null, $this->data['book_tags'][$j]->recent_version_id);
			$this->data['book_tags'][$j]->versions[0]->tag_of = $this->tags->get_children($this->data['book_tags'][$j]->versions[0]->version_id);
		}
		$this->data['login_is_author'] = $this->login_is_book_admin();
		$this->data['view'] = __FUNCTION__;

	}

	// Resources (list of all pages|media)
	private function resources() {

		if ('vis'==$this->data['view']) return;
		if ($this->data['mode'] == 'editing') return;
		$this->data['book_content'] = $this->pages->get_all($this->data['book']->book_id, null, null, true);
		for ($j = 0; $j < count($this->data['book_content']); $j++) {
			$this->data['book_content'][$j]->versions = array();
			$this->data['book_content'][$j]->versions[0] = $this->versions->get_single($this->data['book_content'][$j]->content_id, null, $this->data['book_content'][$j]->recent_version_id);
		}
		$this->data['login_is_author'] = $this->login_is_book_admin();
		$this->data['view'] = __FUNCTION__;

	}

	// Table of contents (designed by each books' authors)
	private function toc() {

		if ($this->data['mode'] == 'editing') return;
		$this->data['book_versions'] = $this->books->get_book_versions($this->data['book']->book_id, true);
		$this->data['login_is_author'] = $this->login_is_book_admin();
		$this->data['view'] = __FUNCTION__;

	}

	// Search page
	private function search() {

		$this->load->helper('text');
		$this->data['can_edit'] = false;
		$this->data['sq'] =@ $_GET['sq'];;
		$this->data['terms'] = search_split_terms($this->data['sq']);
		$this->data['result'] = $this->pages->search($this->data['book']->book_id, $this->data['terms']);
		usort($this->data['result'], "sortSearchResults");
		$this->data['view'] = __FUNCTION__;

	}

	// Place an external page in an iframe with Scalar header
	private function external() {

		$this->data['link'] = (@!empty($_GET['link'])) ? $_GET['link'] : null;
		$this->data['prev'] = (@!empty($_GET['prev'])) ? $_GET['prev'] : null;

		if (empty($this->data['link']) || empty($this->data['prev'])) $this->kickout();
		if (!stristr($this->data['prev'], base_url())) $this->kickout();

		// Special case known domains that don't allow iframes
		foreach ($this->config->item('iframe_redlist') as $forbidden) {
			if (stristr($this->data['link'], $forbidden)) {
				header('Location: '.$this->data['link']);
				exit;
			}
		}

		$this->template->set_template('external');
		$this->template->write_view('content', 'melons/'.$this->data['melon'].'/external', $this->data);
		$this->template->render();
		$this->template_has_rendered = true;

	}

	// Import from an external archive
	private function import() {

		if (!$this->login_is_book_admin('Commentator')) $this->require_login(4);

		// Set params
		$archive = no_ext($this->uri->segment(3));
		$this->data['hide_edit_bar'] = true;

		switch ($archive) {

			case false:

				$this->data['view'] = 'tensor';
				break;

			case 'system':

				// Import from another Scalar book on the same install
				$this->data['view'] = 'import_system';
				break;

			default:

				// Translate the import URL to information about the archive
				$archive_title = str_replace('_',' ',$archive);
				$archives_rdf_url = confirm_slash(APPPATH).'rdf/xsl/archives.rdf';
				$archives_rdf = file_get_contents($archives_rdf_url);
				$archives_rdf = str_replace('{$base_url}', confirm_slash($this->data['app_root']), $archives_rdf);
				$archives =  $this->rdf_store->parse($archives_rdf);
				$found = array();
				foreach ($archives as $archive_uri => $archive) {
					$title = $archive['http://purl.org/dc/elements/1.1/title'][0]['value'];
					$identifier =@ $archive['http://purl.org/dc/terms/identifier'][0]['value'];
					if (strtolower($title) == strtolower($archive_title)) $found[$archive_uri] = $archive;
					if (!isset($found[$archive_uri]) && strtolower($identifier) == strtolower($archive_title)) $found[$archive_uri] = $archive;
				}
				if (!$found) die('Could not find archive');
				$this->data['external'] = $this->rdf_store->helper($found);

				// API key from config if applicable
				$id = $this->data['external']->getPropValue('http://purl.org/dc/terms/identifier');
				if (empty($id)) $id = $this->data['external']->getPropValue('http://purl.org/dc/elements/1.1/title');
				$id = str_replace(' ', '_', strtolower($id));
				$archive_api_key = $this->config->item($id.'_key');
				if (empty($archive_api_key)) $archive_api_key = $this->config->item($id.'_id');
				$this->data['archive_api_key'] = (!empty($archive_api_key)) ? trim($archive_api_key) : null;
				$this->data['view'] = __FUNCTION__;

		}

	}

	// Upload a file
	// This uploads a file only and returns its URL; all other operations to create a media page are through the Save API
	private function upload() {

		$action = (isset($_POST['action'])) ? strtolower($_POST['action']) : null;
		$chmod_mode = $this->config->item('chmod_mode');
		if (empty($chmod_mode)) $chmod_mode = 0777;

		if (!$this->login_is_book_admin('Commentator')) {
			if ($action == 'add') {
				echo json_encode( array('error'=>'Not logged in or not an author') );
			} else {
				$this->require_login(4);
			}
			exit;
		};

		$this->data['view'] = __FUNCTION__;

		if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && 'post'==strtolower($_SERVER['REQUEST_METHOD'])) {

			echo json_encode( array('error'=>'The file is larger than the server\'s max upload size') );
			exit;

		} elseif ($action == 'add' || $action == 'replace') {

			$return = array();

			try {
	            $slug = confirm_slash($this->data['book']->slug);
				$url = $this->file_upload->uploadMedia($slug, $chmod_mode, $this->versions);
				$path = confirm_slash(FCPATH).confirm_slash($this->data['book']->slug).$url;
				$thumbUrl = $this->file_upload->createMediaThumb($slug, $url, $chmod_mode);
			} catch (Exception $e) {
				$return['error'] =  $e->getMessage();
				echo json_encode($return);
				exit;
			}

			try {
				$this->load->library('Image_Metadata', 'image_metadata');
				$return[$url] = $this->image_metadata->get($path, Image_Metadata::FORMAT_NS);
			} catch (Exception $e) {
				// Don't throw exception since this isn't critical
			}

			if (false!==$thumbUrl) {
				$return[$url]['scalar:thumbnail'] = confirm_slash(base_url()).$slug.$thumbUrl;
			}
			echo json_encode($return);
			exit;

		} // if

		// List of media pages
		$this->data['book_media'] = $this->pages->get_all($this->data['book']->book_id, 'media', null, false);
		$to_remove = array();
		for ($j = 0; $j < count($this->data['book_media']); $j++) {
			$this->data['book_media'][$j]->versions = array();
			$this->data['book_media'][$j]->versions[0] = $this->versions->get_single($this->data['book_media'][$j]->content_id, null, $this->data['book_media'][$j]->recent_version_id);
		}

	}

	// List versions of the current page
	private function versions() {

		$action = (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) ? $_REQUEST['action'] : null;
		if ($action == 'do_delete_versions') {
			$this->load->model('version_model', 'versions');
			// Check persmissions
			if (!$this->data['login_is_super'] && !$this->login_is_book_admin('Reviewer')) $this->require_login(4);
			// Delete versions
			$versions = (array) $_POST['delete_version'];
			if (empty($versions)) die('Could not find versions to delete');
			foreach ($versions as $version_id) {
				$this->versions->delete($version_id);
			}
			$redirect_to = $this->data['base_uri'].$this->data['page']->slug.'.versions?action=deleted_versions';
			header('Location: '.$redirect_to);
			exit;
		} elseif ($action == 'do_reorder_versions') {
			if (!$this->data['login_is_super'] && !$this->login_is_book_admin('Reviewer')) $this->require_login(4);
			$content_id = (int) $this->data['page']->content_id;
			if (empty($content_id)) die('Could not resolve content ID');
			$this->versions->reorder_versions($content_id);
			$redirect_to = $this->data['base_uri'].$this->data['page']->slug.'.versions?action=versions_reordered';
			header('Location: '.$redirect_to);
			exit;
		}

		// Overwrite previous page array (which only has the most recent version)
		$this->data['page']->user = (int) $this->data['page']->user->user_id;
		unset($this->data['page']->versions);
		$settings = array(
							'book'         => $this->data['book'],
							'content'      => array($this->data['page']),
							'base_uri'     => $this->data['base_uri'],
							'versions'     => RDF_Object::VERSIONS_ALL,
							'ref'          => RDF_Object::REFERENCES_NONE,
							'prov'		   => RDF_Object::PROVENANCE_ALL,
							'max_recurses' => 0
								 );
		$index = $this->rdf_object->index($settings);
		if (!count($index)) throw new Exception('Problem getting page index');
		$this->data['page'] = $index[0];
		unset($index);

		$key = 0;
		$version_num = get_version($this->uri->uri_string());
		// If version_num is 0 then the version number was not in the uri (i.e. it is the current index)
		if($version_num != 0) {
			foreach ($this->data['page']->versions as $key => $version) {
				if($version->version_num == $version_num) {
					break;
				}
			}
		}
		$this->data['page']->version_index = $key;

		$this->data['hide_edit_bar'] = true;

	}

	// List versions of the current page in a digest format
	private function history() {

		// Overwrite previous page array (which only has the most recent version)
		$this->data['page']->user = (int) $this->data['page']->user->user_id;
		unset($this->data['page']->versions);
		$settings = array(
							'book'         => $this->data['book'],
							'content'      => array($this->data['page']),
							'base_uri'     => $this->data['base_uri'],
							'versions'     => RDF_Object::VERSIONS_ALL,
							'ref'          => RDF_Object::REFERENCES_NONE,
							'prov'		   => RDF_Object::PROVENANCE_ALL,
							'max_recurses' => 0
								 );
		$index = $this->rdf_object->index($settings);
		if (!count($index)) throw new Exception('Problem getting page index');
		$this->data['page'] = $index[0];
		unset($index);
		reset($this->data['page']->versions);
		$this->data['page']->version_index = key($this->data['page']->versions);
		$this->data['hide_edit_bar'] = true;

	}

	// List metadata in a human-readable way
	private function meta() {

		$all = (isset($_GET['versions']) && 1==$_GET['versions']) ? true : false;
		if ($all || 'honeydew'==$this->data['melon']) {  // Overwrite previous page's versions array (which only has the most recent version)
			unset($this->data['page']->versions);
			$this->data['page']->user = $this->data['page']->user->user_id;
			$settings = array(
								'book'         => $this->data['book'],
								'content'      => array($this->data['page']),
								'base_uri'     => $this->data['base_uri'],
								'versions'     => RDF_Object::VERSIONS_ALL,
								'ref'          => RDF_Object::REFERENCES_NONE,
								'prov'		   => RDF_Object::PROVENANCE_ALL,
								'max_recurses' => 0
									 );
			$index = $this->rdf_object->index($settings);
			if (!count($index)) throw new Exception('Problem getting page index');
			$this->data['page'] = $index[0];
			unset($index);
			reset($this->data['page']->versions);
			$this->data['page']->version_index = key($this->data['page']->versions);
		}

		foreach ($this->data['page']->versions as $key => $version) {
			$this->data['page']->versions[$key]->meta = $this->versions->rdf($this->data['page']->versions[$key]);
		}

		$this->data['page']->meta = $this->pages->rdf($this->data['page']);
		$this->data['hide_edit_bar'] = true;

	}

	// Edit page
	private function edit() {

		// User
		$user_id = @$this->data['login']->user_id;
		if (empty($user_id)) $this->require_login(3);

		// Book
		$book_id =@ (int) $this->data['book']->book_id;
		$book_slug = $this->data['book']->slug;
		if (empty($book_id)) show_404();
		if (empty($book_slug)) show_404();

		// Content
		$content_id = (!empty($this->data['page'])) ? (int) $this->data['page']->content_id : null;
		$is_new = (!empty($content_id)) ? false : true;

		// Protect
		if ($is_new) {
			$this->protect_book('commentator');
		} elseif (!$this->pages->is_owner($user_id, $content_id)) {
			$this->protect_book('reviewer');
		}

		$this->data['mode'] = 'editing';
		$this->data['is_new'] = $is_new;

		// Page or media file, continue to
		$this->data['is_file'] = false;
		$this->data['file_url'] = null;
		$this->data['continue_to'] = null;
		if (!empty($this->data['page']) && !empty($this->data['page']->versions) && isset($this->data['page']->versions[$this->data['page']->version_index])) {
			if ($this->data['page']->type=='media') {
				$this->data['is_file'] = true;
				$this->data['file_url'] = $this->data['page']->versions[$this->data['page']->version_index]->url;
			}
			if (!empty($this->data['page']->versions[$this->data['page']->version_index]->continue_to_content_id)) {
				$this->data['continue_to'] = $this->pages->get($this->data['page']->versions[$this->data['page']->version_index]->continue_to_content_id);
				if (!empty($this->data['continue_to'])) {
					$this->data['continue_to']->versions = array();
					$this->data['continue_to']->versions[0] = $this->versions->get_single($this->data['continue_to']->content_id, null, $this->data['continue_to']->recent_version_id);
					$this->data['continue_to']->version_index = 0;
				}
			}
		}

		// Page URI segment
		if (!empty($this->data['page']) && !empty($this->data['page']->slug)) {
			$this->data['page_url'] = $this->data['page']->slug;
		} elseif (substr($this->uri->uri_string(),-9,9)=='/new.edit') {
			$this->data['page_url'] = '';
		} else {
			$this->data['page_url'] = ltrim($this->uri->uri_string(),'/');
			if (substr($this->data['page_url'], 0, strlen($this->data['book']->slug))==$this->data['book']->slug) $this->data['page_url'] = substr($this->data['page_url'], strlen($this->data['book']->slug));
			$this->data['page_url'] = ltrim($this->data['page_url'], '/');
			// Don't use rtrim(..., '.edit'.), seems to have a bug with "workscited.edit" => "worksc"
			if ('.edit'==substr($this->data['page_url'], -5, 5)) $this->data['page_url'] = substr($this->data['page_url'], 0, -5);
		}

		// Enum types
		$this->data['categories'] = $this->books->get_enum_values('content','category');

		// Metadata terms
		$this->data['ontologies'] = $this->config->item('ontologies');
		$this->data['rdf_fields'] = $this->versions->rdf_fields;

		// List of images/audio
		$this->data['book_images'] = $this->books->get_images($book_id);
		$this->data['book_audio'] = $this->books->get_audio($book_id);

	}

	// Annotation editor page
	private function annotation_editor() {

		if (!$this->login_is_book_admin('Commentator')) $this->require_login(4);

	}

}
?>
