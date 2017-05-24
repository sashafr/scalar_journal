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
 * @projectDescription		Base controller class to handle database and login tasks useful for all controllers
 * @author					Craig Dietrich
 * @version					2.2
 */

abstract class MY_Controller extends Controller {

	public $data = array();

	public function MY_Controller() {

		parent::__construct();

		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

		// GET vars, note that this requires 'uri_protocol' to be 'PATH_INFO' in config.php
		parse_str($_SERVER['QUERY_STRING'], $_GET);

		$this->load->library( 'session' );
		$this->load->helper( 'url' );
		$this->load->helper( 'html' );
		$this->load->helper( 'string' );
		$this->load->helper( 'language' );
		$this->load->helper( 'array' );
		$this->load->helper( 'directory' );
		$this->config->load( 'rdf' );
		$this->config->load( 'local_settings' );
		$this->data['recaptcha_public_key'] = ($this->config->item('recaptcha_public_key')) ? $this->config->item('recaptcha_public_key') : '';

		// Models
		$this->load->model( 'user_model', 'users' );   // Interact with user database
		$this->load->model( 'login_model', 'login' );  // Handle login session

		// Language (default set in config/config.php)
		$lang = (isset($_REQUEST['lang']) && file_exists(APPPATH.'language/'.$_REQUEST['lang'])) ? $_REQUEST['lang'] : null;
		$this->lang->load('content', $lang);

		// Database
		// TODO: I believe this opens two different connections to the same database
		$this->load->database();
		$this->load->library('RDF_Store', 'rdf_store');

		// Initalize view data
		$this->data['app_root'] = base_url().'system/application/';
		$this->data['ns'] = $this->config->item('namespaces');

		// Authentication
		try {
			if ($this->login->do_logout()) {
				header('Location: '.$this->redirect_url());
				exit;
			} elseif ($this->login->do_login()) {
				header('Location: '.$this->redirect_url());
				exit;
			}
			$this->set_login_params();
		} catch (Exception $e) {
			$this->data['login_error'] =  $e->getMessage();
		}

	}

	/**
	 * Set information about the logged-in user such as the books they are attached to
	 * @requires	$this->login
	 * @requires	$this->data
	 * @return 		null
	 */

	protected function set_login_params() {

		$this->data['login']          = $this->login->get();
		$this->data['login_is_super'] = (isset($this->data['login']->is_super) && $this->data['login']->is_super) ? true : false;
		$this->data['login_books']    = (isset($this->data['login']->user_id)) ? $this->login->get_books($this->data['login']->user_id) : array();
		$this->data['login_book_ids'] = $this->login->get_book_ids($this->data['login_books']);

	}

	/**
	 * Set information about whether a logged-in user can edit a book or open the dashboard
	 * @requires	$this->data
	 * @return 		null
	 */

	protected function set_user_book_perms() {

		$this->data['user_level'] = null;
		// Admin
		if ($this->data['login_is_super']) {
			$this->data['user_level'] = 'Author';
		// Book author
		} elseif (!empty($this->data['book']) && in_array($this->data['book']->book_id, $this->data['login_book_ids'])) {
			$user_level = array_get_node('book_id', $this->data['book']->book_id, $this->data['login_books']);
			$this->data['user_level'] = ucwords($user_level['value']['relationship']);
		}

	}

	/**
	 * Test a user level against logged-in status
	 * @param 	int $book_id
	 * @param	str $level
	 * @return 	bool
	 */

	protected function login_is_book_admin($level='Author') {

		if ($this->users->is_a(strtolower($this->data['user_level']), $level)) return true;
		return false;

	}

	/**
	 * Protect a book against a user level
	 * @param 	int $book_id
	 * @param	str	$level
	 * @return 	null
	 */

	protected function protect_book($level='Author') {

		if (!$this->login_is_book_admin($level)) $this->kickout();

	}

	/**
	 * Redirect the page to the base URL
	 * @return 	null
	 */

	protected function kickout() {

		header('Location: '.base_url());
		exit;

	}

	/**
	 * Redirect the page to login
	 * @return 	null
	 */

	protected function require_login($msg='') {

		$uri = (confirm_slash(base_url())).'system/login?redirect_url='.urlencode($this->redirect_url());
		if (!empty($msg)) $uri .= '&msg='.$msg;
		header('Location: '.$uri);
		exit;

	}

	/**
	 * Return a redirect URL
	 * @return 	str 	URI
	 */

   	protected function redirect_url() {

   		// A specific redirect URL has been sent via GET/POST
   		if (isset($_REQUEST{'redirect_url'}) && !empty($_REQUEST['redirect_url'])) {
   			return urldecode(trim($_REQUEST{'redirect_url'}));
   		}
    	// Book is present and might have a page slug
    	if (isset($this->data['book']) && isset($this->data['book']->slug) && !empty($this->data['book']->slug)) {
   			$segs = $this->uri->segment_array();
    		return confirm_slash(base_url()).implode('/',$segs);
    	}
    	// Dashboard
		$segs = $this->uri->segment_array();
		if ('system'==$segs[1] && 'dashboard'==$segs[2]) {
			$book_id = (isset($_GET['book_id']) && !empty($_GET['book_id'])) ? (int) $_GET['book_id'] : 0;
			$zone = (isset($_GET['zone']) && !empty($_GET['zone'])) ? $_GET['zone'] : 'style';
			return confirm_slash(base_url()).'system/dashboard'.urlencode('?book_id='.$book_id.'&zone='.$zone.'#tabs-'.$zone);
		}
   		// Default to the install index
   		return base_url();

   	}

   	/**
   	 * Determine if a melon (skin) exists
   	 */

   	protected function melon_exists($name='') {

   		$path = confirm_slash(APPPATH).'views/melons/'.$name;
   		if (!file_exists($path)) return false;
   		return true;

   	}

   	/**
   	 * Force a melon to be loaded/used
   	 */

   	protected function force_melon($name='') {

   		$name = strtolower($name);
		$this->data['melon'] = $name;
		if (!file_exists(APPPATH.'views/melons/'.$name.'/config.php')) echo '<p>Warning: '.ucwords($name).' theme does not exist, this page might render oddly.</p>';
		include(APPPATH.'views/melons/'.$name.'/config.php');
		$this->config->set_item('arbor', $config['arbor']);
		$this->data['template'] = $this->template->config['active_template'];

   	}

   	/**
   	 * Load info about a melon (skin)
   	 */

   	protected function load_melon_config($name='') {

   		$this->config->load('../views/melons/'.$name.'/config');

   	}

   	/**
   	 * Get paths to melons
   	 */

   	protected function melon_paths() {

   		$melon_dir = APPPATH.'views/melons';
   		$files = scandir($melon_dir);
   		$paths = array();
   		foreach ($files as $file) {
   			if ($file=='.'||$file=='..') continue;
   			$paths[] = $melon_dir.'/'.$file.'/';
   		}
   		return $paths;

   	}

   	/**
   	 * Determine if paywall page should be presented rather than the protected page content
   	 */
   	
   	protected function can_bypass_paywall() {
   		
   	   	try {
   			if ($this->login_is_book_admin('Reader')) throw new Exception('Reader logged in');
   			$book_slug = $this->data['book']->slug;
   			if (empty($book_slug)) throw new Exception('Invalid book slug');
   			$tinypass_config_path = confirm_slash(FCPATH).$book_slug.'/tinypass.php';
   			if (!file_exists($tinypass_config_path)) throw new Exception('Could not find Tinypass config');
   			require_once($tinypass_config_path);
   			if (empty($tinypass) || !is_array($tinypass)) throw new Exception('No $tinypass in tinypass.php');
   			$this->load->library('Tinypass_Helper', $tinypass);
   			return false;
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
   	}

   	protected function paywall() {

   		try {
   			$msg = $this->can_bypass_paywall();
			if (false!==$msg) throw new Exception($msg);
   			// Load Tinypass
			if (!$this->tinypass_helper->accessGranted()) {
				$this->data['buttonHTML'] = $this->tinypass_helper->getHTML();
				$this->template->set_template('external');
				$this->template->write_view('content', 'melons/'.$this->data['melon'].'/tinypass', $this->data);
				$this->template->render();
				$this->template_has_rendered = true;
			}
		} catch (Exception $e) {}

   	}

}

?>