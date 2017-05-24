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
 * http://www.osedu.org/licenses /ECL-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an "AS IS"
 * BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

/**
 * @projectDescription		System controller for displaying book index and handling admin tasks such as login and dashboard area
 * @author					Craig Dietrich
 * @version					2.2
 */

class System extends MY_Controller {

	private $base_url;

	public function System() {

		parent::__construct();

		$segs = $this->uri->segment_array();
		$this->base_url = confirm_slash(base_url()).implode('/',$segs);

	}

	public function index() {

		$this->load->model('book_model', 'books');
		$book_list_dir = $this->config->item('active_book_list');
		if (empty($book_list_dir)) die('Could not find book list directory.');
		//@Lucas - added config item to set active cover (see local_settings.php)
		$cover_dir = $this->config->item('active_cover');
		if (empty($cover_dir)) die('Could not find cover directory.');

		$this->data['title'] = $this->lang->line('install_name');
		$this->data['cover_title']  = $this->lang->line('install_name');

		$this->data['featured_books'] = $this->books->get_index_books();

		$this->data['other_books'] = array();
		if (isset($_REQUEST['sq']) && !empty($_REQUEST['sq'])) {
			$this->data['other_books'] = $this->books->get_index_books(false, $_REQUEST['sq']);
			if (empty($this->data['other_books'])) $this->data['book_list_search_error'] = 'No books found for "'.trim(htmlspecialchars($_REQUEST['sq'])).'"';
		} elseif(isset($_REQUEST['view_all'])) {
			$this->data['other_books'] = $this->books->get_index_books(false);
		} elseif (isset($_REQUEST['sq'])) {
			$this->data['book_list_search_error'] = 'Please enter a search term';
		}

		$this->data['user_books'] = array();
		if(isset($this->data['login']->user_id)) {
			$this->data['user_books'] = $this->books->get_all($this->data['login']->user_id);
		}

		$this->template->set_template('admin');
		$this->template->write_view('cover', 'modules/'.trim($cover_dir,'/').'/index_cover', $this->data);
		$this->template->write_view('content', 'modules/'.trim($book_list_dir,'/').'/book_list', $this->data);
		$this->template->render();

	}

	public function ontologies() {

		$this->data['content'] = $this->config->item('ontologies');
		$rdf_fields = $this->config->item('rdf_fields');
		// Remove built-in values
		foreach ($this->data['content'] as $prefix => $values) {
			foreach ($values as $key => $value) {
				if (in_array($prefix.':'.$value, $rdf_fields)) {
					unset($this->data['content'][$prefix][$key]);
				}
			}
		}
		// Reset keys to 1..N
		foreach ($this->data['content'] as $prefix => $values) {
			$this->data['content'][$prefix] = array_values($values);
		}

		$this->template->set_template('blank');
		$this->template->write_view('content', 'modules/data/json', $this->data);
		$this->template->render();

	}

	public function image_metadata() {

		$url =@ $_GET['url'];
		$this->data['content'] = array();

		$this->load->library('Image_Metadata', 'image_metadata');
		$this->data['content'][$url] = $this->image_metadata->get($url, Image_Metadata::FORMAT_NS);

		$this->template->set_template('blank');
		$this->template->write_view('content', 'modules/data/json', $this->data);
		$this->template->render();

	}

	public function login() {

		$this->login->do_logout(true);

		$this->data['login'] = $this->login->get();
		$this->data['title'] = $this->lang->line('install_name').': Login';
		$this->data['norobots'] = true;

		$this->template->set_template('admin');
		$this->template->write_view('content', 'modules/login/login_box', $this->data);
		$this->template->render();

	}

	public function logout() {

		// No actual page for logout, but here so logout links have something to point to
		exit;

	}

	public function register() {

		require_once(APPPATH.'libraries/recaptcha/recaptchalib.php');
		$this->login->do_logout(true);
		$this->data['recaptcha_public_key'] = $this->config->item('recaptcha_public_key');
		$this->data['recaptcha_private_key'] = $this->config->item('recaptcha_private_key');
		if (empty($this->data['recaptcha_public_key'])||empty($this->data['recaptcha_private_key'])) $this->data['recaptcha_public_key'] = $this->data['recaptcha_private_key'] = null;
		$this->data['title'] = $this->lang->line('install_name').': Register';
		$register_keys = $this->config->item('register_key');
		$this->data['register_key'] = (!empty($register_keys)) ? true : false;
		$this->data['norobots'] = true;

		try {
			$action = (isset($_POST['action']) && !empty($_POST['action'])) ? $_POST['action'] : null;
			if ('do_register'==$action) {
				// Validate register key
				if (!empty($register_keys) && !empty($this->data['register_key']) && !in_array($_POST['registration_key'], $register_keys)) throw new Exception('Invalid registration key');
				// Register new user
				$this->load->model('book_model', 'books');
				$user_id = $this->users->register($_POST);
				// Create new book
				if (isset($_POST['book_title']) && !empty($_POST['book_title'])) {
					$book_id = $this->books->add(array('title'=>trim($_POST['book_title']), 'user_id'=>$user_id));
				}
				// Login with the newly created user
				$this->login->do_login(true);
				// Head back to the previous page
				header('Location: '.$this->redirect_url());
				exit;
			}
		} catch (Exception $e) {
			$this->data['error'] =  $e->getMessage();
		}

		$this->template->set_template('admin');
		$this->template->write_view('content', 'modules/login/register_box', $this->data);
		$this->template->render();

	}

	public function forgot_password() {

		$this->login->do_logout(true);
		$this->data['title'] = $this->lang->line('install_name').': Forgot Password';
		$this->data['norobots'] = true;

		$this->load->library('SendMail', 'sendmail');
		$this->data['forgot_login_error'] = '';
		$action = (isset($_POST['action']) && !empty($_POST['action'])) ? $_POST['action'] : null;
		if ('do_forgot_password'==$action) {
			$email =@ trim($_POST['email']);
			if (empty($email)) {
				$this->data['forgot_login_error'] = 'Please enter your login email address';
			} elseif (!$this->users->get_by_email($email)) {
				$this->data['forgot_login_error'] = 'Could not find the entered email address';
			} else {
				$reset_string = $this->users->set_reset_string($email);
				$this->sendmail->reset_password($email, $reset_string);
				header('Location: '.confirm_slash(base_url()).'system/forgot_password?action=sent');
				exit;
			}
		}

		$this->template->set_template('admin');
		$this->template->write_view('content', 'modules/login/forgot_password', $this->data);
		$this->template->render();

	}

	public function create_password() {

		$this->login->do_logout(true);
		$this->data['title'] = $this->lang->line('install_name').': Reset Password';
		$this->data['norobots'] = true;

		$reset_string =@ $_REQUEST['key'];
		if (empty($reset_string)) header('Location: '.base_url());

		$action = (isset($_POST['action']) && !empty($_POST['action'])) ? $_POST['action'] : null;
		if ('do_create_password'==$action) {
			$email =@ $_POST['email'];
			$password_1 =@ $_POST['password_1'];
			$password_2 =@ $_POST['password_2'];
			if (empty($email)) {
				$this->data['create_login_error'] = 'Email is a required field';
			} elseif (empty($password_1) || empty($password_2)) {
				$this->data['create_login_error'] = 'Password is a required field';
			} elseif ($password_1 != $password_2) {
				$this->data['create_login_error'] = 'Password and retype password do not match';
			} else {
				$user = $this->users->get_by_email_and_reset_string($email, $reset_string);
				if (!$user) {
					$this->data['create_login_error'] = 'The email address does not match the reset key';
				} else {
					try {
						$this->users->set_password_from_form_fields($user->user_id, $_POST);
						$this->users->save_reset_string($user->user_id, '');
					} catch (Exception $e) {
	    				$this->data['create_login_error'] = $e->getMessage();
					}
					header('Location: '.confirm_slash(base_url()).'system/login?msg=2');
				}
			}
		}

		$this->template->set_template('admin');
		$this->template->write_view('content', 'modules/login/create_password', $this->data);
		$this->template->render();

	}

	public function dashboard() {

		$this->load->model('book_model', 'books');

		$book_id = (isset($_REQUEST['book_id']) && !empty($_REQUEST['book_id'])) ? $_REQUEST['book_id'] : 0;
		$user_id = (isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id'])) ? $_REQUEST['user_id'] : 0;
		$action = (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) ? $_REQUEST['action'] : null;

		// There is more specific validation in each call below, but here run a general check on calls on books and users
		if (!$this->data['login']->is_logged_in) $this->require_login(4);
		if (!empty($book_id)) {
			$this->data['book'] = $this->books->get($book_id);
			$this->set_user_book_perms();
			$this->protect_book();
		}
		if (!$this->data['login_is_super'] && !empty($user_id)) {
			if ($this->data['login']->user_id != $user_id) $this->kickout();
		}

		$this->load->model('page_model', 'pages');
		$this->load->model('version_model', 'versions');
		$this->load->model('path_model', 'paths');
		$this->load->model('tag_model', 'tags');
		$this->load->model('annotation_model', 'annotations');
		$this->load->model('reply_model', 'replies');

		$this->data['zone']    		= (isset($_REQUEST['zone']) && !empty($_REQUEST['zone'])) ? $_REQUEST['zone'] : 'user';
		$this->data['type']    		= (isset($_GET['type']) && !empty($_GET['type'])) ? $_GET['type'] : null;
		$this->data['sq']      		= (isset($_GET['sq']) && !empty($_GET['sq'])) ? trim($_GET['sq']) : null;
		$this->data['book_id'] 		= (isset($_GET['book_id']) && !empty($_GET['book_id'])) ? trim($_GET['book_id']) : 0;
		$this->data['delete']  		= (isset($_GET['delete']) && !empty($_GET['delete'])) ? trim($_GET['delete']) : null;
		$this->data['saved']   		= (isset($_GET['action'])&&'saved'==$_GET['action']) ? true : false;
		$this->data['deleted'] 		= (isset($_GET['action'])&&'deleted'==$_GET['action']) ? true : false;
		$this->data['duplicated']	= (isset($_GET['action'])&&'duplicated'==$_GET['action']) ? true : false;

	 	// Actions
	 	try {
		 	switch ($action) {
		 		case 'do_save_style': // Book Properties (method requires book_id)
		 			$array = $_POST;
		 			unset($array['action']);
		 			unset($array['zone']);
		 			$this->books->save($array);
		 			$this->books->save_versions($array);
					header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&action=book_style_saved');
					exit;
		 		case 'do_save_user':  // My Account (method requires user_id & book_id)
		 			$array = $_POST;
		 			if ($this->users->email_exists_for_different_user($array['email'], $this->data['login']->user_id)) {
			 			header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&error=email_exists');
		 				exit;
		 			}
		 			if (empty($array['fullname'])) {
			 			header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&error=fullname_required');
		 				exit;
		 			}
		 			if (!empty($array['url'])) {
		 				if (!isURL($array['url'])) $array['url'] = 'http://'.$array['url'];
		 			}
		 			if (!empty($array['password'])) {
		 				if (!$this->users->get_by_email_and_password($array['email'], $array['old_password'])) {
		 					header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&error=incorrect_password');
		 					exit;
		 				}
		 				if ($array['password']!=$array['password_2']) {
		 					header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&error=password_match');
		 					exit;
		 				}
		 				$this->users->set_password($this->data['login']->user_id, $array['password']);
		 			}
					// Save profile
					unset($array['password']);
		 			unset($array['old_password']);
		 			unset($array['password_2']);
		 			$this->users->save($array);
		 			$this->set_login_params();
		 			header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&action=user_saved');
		 			exit;
		 		case 'do_save_sharing':
		 			$this->books->save(array('title'=>$_POST['title'],'book_id'=>(int)$_POST['book_id']));
		 			$array = $_POST;
		 			unset($array['action']);
		 			unset($array['zone']);
		 			$this->books->save($array);
					header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&action=book_sharing_saved#tabs-'.$this->data['zone']);
		 			exit;
				case 'do_duplicate_book':   // My Account  TODO
					$user_id =@ (int)$_POST['user_id'];
					$array = $_POST;
					if (empty($user_id) && !$this->data['login_is_super']) $this->kickout();
					$book_id = (int) $this->books->duplicate($array);
					// Option to redirect to page of choice
					if (isset($array['redirect']) && filter_var($array['redirect'],FILTER_VALIDATE_URL)) {
						$url_has_query = parse_url($array['redirect'],PHP_URL_QUERY);
						$redirect_url = $array['redirect'];
						if (!isset($url_has_query)) {
							if (substr($redirect_url, -1)!='?') $redirect_url .= '?';
						} else {
							$redirect_url .= '&';
						}
						$redirect_url .= 'duplicated=1';  // TODO: Change to action=duplicated
						header('Location: '.$redirect_url);
					// Redirect to Dashboard > My Account
					} else {
						header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&action=duplicated');
					}
					exit;
				case 'do_add_book':   // Admin: All Books (requires title) & My Account (user_id & title)
					$user_id =@ (int) $_POST['user_id'];
					$array = $_POST;
					if (empty($user_id) && !$this->data['login_is_super']) $this->kickout();
					$book_id = (int) $this->books->add($array);
					// Option to redirect to page of choice
					if (isset($array['redirect']) && filter_var($array['redirect'],FILTER_VALIDATE_URL)) {
						$url_has_query = parse_url($array['redirect'],PHP_URL_QUERY);
						$redirect_url = $array['redirect'];
						if (!isset($url_has_query)) {
							if(substr($redirect_url, -1)!='?') $redirect_url .= '?';
						} else {
							$redirect_url .= '&';
						}
						$redirect_url .= 'created=1';
						header('Location: '.$redirect_url);
					// Redirect to Dashboard > My Account
					} else {
						header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&action=added');
					}
					exit;
				case 'do_add_user':  // Admin: All Users
					if (!$this->data['login_is_super']) $this->kickout();
					$array = $_POST;
					$book_title = (isset($array['book_title'])  && !empty($array['book_title']) && 'title of first book (optional)'!=$array['book_title']) ? trim($array['book_title']) : null;
					$user_id = (int) $this->users->add($array);
					if (!empty($user_id) && !empty($book_title)) {
						$book_id = $this->books->add(array('title'=>$book_title));
						if (!empty($book_id)) $this->books->save_users($book_id, array($user_id), 'author');
					}
					header('Location: '.$this->base_url.'?book_id='.$book_id.'&zone='.$this->data['zone'].'&action=added');
					exit;
				case 'do_delete':    // Admin: All Users & All Books
					if (!$this->data['login_is_super']) $this->kickout();
					$zone = $this->data['zone'];
					$delete = (int) $_REQUEST['delete'];
					$type = $_REQUEST['type'];
					if (!is_object($this->$type)) show_error('Invalid section');
					if (!$this->$type->delete($delete)) show_error('There was a problem deleting. Please try again');
					header('Location: '.$this->base_url.'?action=deleted&zone='.$zone.'#tabs-'.$zone);
					exit;
				case "get_email_list":  // Admin: Tools
					if (!$this->data['login_is_super']) $this->kickout();
					$users = $this->users->get_all();
					$this->data['email_list'] = array();
		 			foreach ($users as $user) {
						if (empty($user->email)) continue;
						$this->data['email_list'][] = trim($user->email);
					}
					unset($user);
					break;
				case "recreate_book_folders":  // Admin: Tools
					if (!$this->data['login_is_super']) $this->kickout();
					$books = $this->books->get_all();
					$this->data['book_list'] = array();
		 			foreach ($books as $book) {
						$slug = $book->slug;
						$msg = $slug.' ... ';
						if ($this->books->slug_exists($slug)) {
							$msg .= 'already exists';
						} else {
							try {
								$this->books->create_directory_from_slug($slug);
								$msg .= 'RECREATED';
							} catch (Exception $e) {
								$msg .= 'ERROR attempting to recreate: '.$e->getMessage();
							}
						}
						$this->data['book_list'][] = $msg;
					}
					unset($books);
					break;
				case 'acls_join_book': // @Lucas - added function to add a user as a "reader" to a book, as well as email the author

					if (!$this->data['login']->is_logged_in) $this->kickout();

					$this->load->model('book_model', 'books');
					$this->load->library('SendMail', 'sendmail');

					$book_id = @ (int) $_REQUEST['book_to_join'];

					$this->data['book'] = $this->books->get($book_id);

					$list_in_index = 0;
					$this->data['content'] = $this->users->save_books($this->data['login']->user_id, array($this->data['book']->book_id), 'reader', $list_in_index);

					//Send email to current authors. If the current user opted to request to become an author, send that email instead.
					$this->sendmail->acls_join_book($this->data['login'], $this->data['book'], (int)$_REQUEST['request_author'], @$_REQUEST['author_reason']);

					if(isset($_POST['redirect']) && filter_var($_POST['redirect'],FILTER_VALIDATE_URL)){
						$url_has_query = parse_url($_POST['redirect'],PHP_URL_QUERY);

						$redirect_url = $_POST['redirect'];

						if(!isset($url_has_query)){
							if(substr($redirect_url, -1)!='?'){
								$redirect_url .= '?';
							}
						}else{
							$redirect_url .= '&';
						}
						$redirect_url .= 'joined=1';

						header('Location: '.$redirect_url);
					}else{
						header('Location: '.base_url().'?joined=1');
					}

					break;
				case 'acls_elevate_user':
					if (!$this->data['login']->is_logged_in) $this->kickout();

					$this->load->model('book_model', 'books');
					$this->load->library('SendMail', 'sendmail');

					$this->data['book'] = $this->books->get($book_id);
					$user_is_reader = false;
					$selected_user = null;
					foreach($this->data['book']->users as $user){
						if($user->user_id == $user_id){
							$selected_user = $user;
							$user_is_reader = true;
							break;
						}
					}
					if($user_is_reader){
						$this->data['content'] = $this->users->save(array('id'=>$user_id, 'book_id'=>$book_id, 'relationship'=>'author', 'list_in_index'=>1));
						$this->sendmail->acls_elevate_user($selected_user, $this->data['book']);
						header('Location: '.base_url().'?action=elevate&user_id='.$user_id.'&book_id='.$book_id.'&elevated=true');
					}else{
						header('Location: '.base_url().'?action=elevate&user_id='.$user_id.'&book_id='.$book_id.'&elevated=error&error=invalid_user');
					}
					break;
		 	}
	 	} catch (Exception $e) {
			show_error($e->getMessage());
		}

		// Books and current book
		$this->data['my_books'] = $this->books->get_all($this->data['login']->user_id, false);
		$this->data['book'] = ($book_id) ? $this->books->get($book_id) : array();
		$this->data['title'] = (!empty($this->data['book'])) ? $this->data['book']->title.' Dashboard' : $this->config->item('install_name').': Dashboard';
		$this->data['cover_title'] = 'Dashboard';

		// Get general data for each zone; this is useful for displaying red dots for "not live" content in each tab, even though it's a performance hit
		$this->data['current_book_users'] =
		$this->data['current_book_images'] =
		$this->data['current_book_versions'] = array();
		$this->data['current_book_content'] = ($book_id) ? $this->pages->get_all($book_id,'composite',null,false) : array();
		$this->data['current_book_files'] = ($book_id) ? $this->pages->get_all($book_id,'media',null,false) : array();
		$this->data['current_book_replies'] = ($book_id) ? $this->replies->get_all($book_id, null, null, false) : array();  // Get hidden comments to make this clear in the UI
		$this->data['pages_not_live'] = $this->count_not_live($this->data['current_book_content']);
		$this->data['media_not_live'] = $this->count_not_live($this->data['current_book_files']);
		$this->data['replies_not_live'] = $this->count_not_live($this->data['current_book_replies']);

		// Get specific data for each zone (no case for pages or media, since these are handled via the API)
		switch ($this->data['zone']) {
			case '':
			case 'user':
				$this->data['duplicatable_books'] = $this->books->get_duplicatable();
				break;
			case 'style':
				$this->data['current_book_images'] = ($book_id) ? $this->books->get_images($book_id) : array();
				$this->data['current_book_versions'] = $this->books->get_book_versions($book_id);
				$this->data['predefined_css'] = false;
				$this->data['interfaces'] = array();
				$melons = $this->melon_paths();
				foreach ($melons as $melon_path) {
					if (!file_exists($melon_path.'config.php')) continue;
					$this->load_melon_config(basename($melon_path));
					if (!empty($this->data['book']) && basename($melon_path)==$this->data['book']->template) $this->data['predefined_css'] = $this->config->item('predefined_css');
					$this->data['interfaces'][] = array('meta'=>$this->config->item('melon_meta'),'stylesheets'=>$this->config->item('stylesheets'));
				}
				usort($this->data['interfaces'], "sort_interfaces");
				break;
		    case 'users':
		        $this->data['current_book_users'] = ($book_id) ? $this->users->get_book_users($book_id) : array();
		        break;
		    case 'publish':
			    // Do Nothing.  Nothing needs to be done.
		    break;
		    // Page-types follow, purposely at the bottom of the switch so that they fall into 'default'
		    default:
		    	if (!empty($this->data['book'])) {
		    		$this->data['book']->has_paywall = $this->books->has_paywall($this->data['book']);
		    	}
		}

		if ($this->data['login_is_super']) {
			$this->data['total'] = (isset($_REQUEST['total']) && is_numeric($_REQUEST['total']) && $_REQUEST['total'] > 0) ? $_REQUEST['total'] : 20;
	 		$this->data['start'] = (isset($_REQUEST['start']) && is_numeric($_REQUEST['start']) && $_REQUEST['start'] > 0) ? $_REQUEST['start'] : 0;
	 		$query = isset($_REQUEST['sq'])?$_REQUEST['sq']:null;
			switch ($this->data['zone']) {
			 	case 'all-users':
			 		if($this->data['login_is_super']) {
			 			if(isset($query)) {
			 				$this->data['users'] = $this->users->search($query);
			 			}
			 			else {
							$this->data['users'] = $this->users->get_all(0,false,'fullname','asc',$this->data['total'],$this->data['start']);
			 			}
			 		}
			 		else {
			 			$this->data['users'] = array();
			 		}
					for ($j = 0; $j < count($this->data['users']); $j++) {
						$this->data['users'][$j]->books = $this->books->get_all($this->data['users'][$j]->user_id);
					}
					break;
			 	case 'all-books':
			 		if($this->data['login_is_super']) {
			 			if(isset($query)) {
			 				$this->data['books'] = $this->books->search($query);
			 			}
			 			else {
							$this->data['books'] = $this->books->get_all(0,false,'title','asc',$this->data['total'],$this->data['start']);
						}
			 		}
			 		else {
			 			$this->data['books'] = array();
			 		}
					$this->data['users'] = ($this->data['login_is_super']) ? $this->users->get_all() : array();

					break;
			}
		}

		// Load Dashboard plugins
		$this->config->load('plugins');
		$plugins = $this->config->item('plugins');
		$this->data['plugins'] = array();
		if (isset($plugins['dashboard'])) {
			foreach ($plugins['dashboard'] as $value) {
				$this->load->plugin($value);
				$cvalue = ucwords($value);
				$this->data['plugins'][$value] = new $cvalue($this->data);
			}
		}

		// Load dashboard
		$this->template->set_template('admin');
		$this->template->write_view('cover', 'modules/cover/dashboard_cover', $this->data);
		$this->template->write_view('content', 'modules/dashboard/tabs', $this->data);
		$this->template->render();

	}

	public function api() {

		$action = (string) $this->uri->segment(3);
		$this->data['content'] = '';
		if (!$this->data['login']->is_logged_in) $this->kickout();
		$this->load->model('book_model', 'books');

		switch ($action) {

			// Read
			case 'get_versions':
				$this->load->model('version_model', 'versions');
				$content_id =@ (int) $_REQUEST['content_id'];
				$this->data['book'] = $this->books->get_by_content_id($content_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin() && !$this->pages->is_owner($this->data['login']->user_id,$content_id)) die ("{'error':'Invalid permissions'}");
				$this->data['content'] = $this->versions->get_all($content_id, null);
				break;
			case 'get_content':
				$this->load->model('page_model', 'pages');
				$this->load->model('version_model', 'versions');
				$book_id =@ (int) $_REQUEST['book_id'];
				if (empty($book_id)) $this->kickout();
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin()) die ('{"error":"Invalid permissions"}');
				$this->data['content'] = $this->pages->get_all($book_id);
		        foreach ($this->data['content'] as $key => $row) {
		        	$versions = $this->versions->get_single($row->content_id, null, $row->recent_version_id);
					if (empty($versions)) continue;
					$this->data['content'][$key]->versions = array($versions);
		        }
				break;
			case 'get_user_books':
				if (!$this->data['login']->is_logged_in) $this->kickout();
				$this->data['content'] = $this->books->get_all($this->data['login']->user_id, false);
				break;
			case 'get_path_of':
				$this->load->model('path_model', 'paths');
				$this->load->model('page_model', 'pages');
				$this->load->model('version_model', 'versions');
				$version_id =@ (int) $_REQUEST['version_id'];
				$book_id = (int) $this->versions->get_book($version_id);
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin()) die ('{"error":"Invalid permissions"}');
				$relationships = $this->paths->get_children($version_id);
				$this->data['content'] = array();
				foreach ($relationships as $key => $row) {
					$this->data['content'][$key] = $this->pages->get($row->child_content_id);
					$this->data['content'][$key]->sort_number = $row->sort_number;
					$versions = $this->versions->get_single($this->data['content'][$key]->content_id, null, $this->data['content'][$key]->recent_version_id);
					if (empty($versions)) continue;
					$this->data['content'][$key]->versions = array($versions);
				}
				break;
			case 'get_tag_of':
				$this->load->model('tag_model', 'tags');
				$this->load->model('page_model', 'pages');
				$this->load->model('version_model', 'versions');
				$version_id =@ (int) $_REQUEST['version_id'];
				$book_id = (int) $this->versions->get_book($version_id);
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin()) die ('{"error":"Invalid permissions"}');
				$relationships = $this->tags->get_children($version_id);
				$this->data['content'] = array();
				foreach ($relationships as $key => $row) {
					$this->data['content'][$key] = $this->pages->get($row->child_content_id);
					$versions = $this->versions->get_single($this->data['content'][$key]->content_id, null, $this->data['content'][$key]->recent_version_id);
					if (empty($versions)) continue;
					$this->data['content'][$key]->versions = array($versions);
				}
				break;
			case 'get_annotation_of':
				$this->load->model('annotation_model', 'annotations');
				$this->load->model('page_model', 'pages');
				$this->load->model('version_model', 'versions');
				$version_id =@ (int) $_REQUEST['version_id'];
				$book_id = (int) $this->versions->get_book($version_id);
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin()) die ('{"error":"Invalid permissions"}');
				$relationships = $this->annotations->get_children($version_id);
				$this->data['content'] = array();
				foreach ($relationships as $key => $row) {
					$this->data['content'][$key] = $this->pages->get($row->child_content_id);
					$this->data['content'][$key]->start_seconds = $row->start_seconds;
					$this->data['content'][$key]->end_seconds = $row->end_seconds;
					$this->data['content'][$key]->points = $row->points;
					$this->data['content'][$key]->start_line_num = $row->start_line_num;
					$this->data['content'][$key]->end_line_num = $row->end_line_num;
					$versions = $this->versions->get_single($this->data['content'][$key]->content_id, null, $this->data['content'][$key]->recent_version_id);
					if (empty($versions)) continue;
					$this->data['content'][$key]->versions = array($versions);
				}
				break;
			case 'get_reply_of':
				$this->load->model('reply_model', 'replies');
				$this->load->model('page_model', 'pages');
				$this->load->model('version_model', 'versions');
				$version_id =@ (int) $_REQUEST['version_id'];
				$book_id = (int) $this->versions->get_book($version_id);
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin()) die ('{"error":"Invalid permissions"}');
				$relationships = $this->replies->get_children($version_id);
				$this->data['content'] = array();
				foreach ($relationships as $key => $row) {
					$this->data['content'][$key] = $this->pages->get($row->child_content_id);
					$this->data['content'][$key]->rel_created = $row->datetime;
					$versions = $this->versions->get_single($this->data['content'][$key]->content_id, null, $this->data['content'][$key]->recent_version_id);
					if (empty($versions)) continue;
					$this->data['content'][$key]->versions = array($versions);
				}
				break;
			case 'get_system_users':
				if (!$this->data['login_is_super']) $this->kickout();
				$this->data['content'] = $this->users->get_all();
				for ($j = 0; $j < count($this->data['content']); $j++) unset($this->data['content'][$j]->password);
				break;
			case 'get_onomy':
				$result = array();
				if (isset($_REQUEST['slug'])) {
					$slug = $_REQUEST['slug'];
					$file_path = FCPATH.$slug."/onomy/";
					if (file_exists($file_path)) {
						$onomies = scandir($file_path);
						foreach ($onomies as $onomy) {
							if ('json'==pathinfo($onomy,PATHINFO_EXTENSION)) {
								$result[] = json_decode((file_get_contents($file_path.$onomy)));
							}
						}
						$this->data['content'] = json_encode($result);
					} else {
						$this->data['content'] = '{"error":"No such file"}';
					}
				}
				break;
			case 'save_onomy':
				$this->data['book'] = $this->books->get((int) $_REQUEST['book_id']);
	 			$this->set_login_params();
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin()) die ('{"error":"Invalid permissions"}');

				if(isset($_REQUEST['version']) && is_numeric($_REQUEST['version'])) {
					$version = $_REQUEST['version'];
					$file_path = FCPATH.$this->data['book']->slug."/onomy";
					if (!file_exists($file_path)) {
						if(!mkdir($file_path,0775)) {
							die ('{"error":"Failed to create Onomy folder"}');
						}
					}
					$onomy = '';
					if(($onomy = file_get_contents('http://onomy.org/published/'.$version.'/skos')) !== false) {
						if(file_put_contents($file_path.'/version_'.$version.'.json', $onomy)) {
							$this->data['content'] = $onomy;
						} else {
							$this->data['content'] = '{"error":"Failed to save taxonomy"}';
						}
					} else {
						$this->data['content'] = '{"error":"Failed to fetch taxonomy"}';
					}
				} else {
					$this->data['content'] = '{"error":"Invalid Version Number"}';
				}
			break;
			case 'user_search':
				if (!$this->data['login']->is_logged_in) $this->kickout();
				$fullname =@ $_REQUEST['fullname'];
				$result = $this->users->get_by_fullname($fullname);
				$this->data['content'] = array();
				if ($result) {
					for ($j = 0; $j < count($result); $j++) {
						if (!isset($this->data['content'][$j])) $this->data['content'][$j] = array();
						$this->data['content'][$j]['user_id'] = $result[$j]->user_id;
						$this->data['content'][$j]['fullname'] = $result[$j]->fullname;
					}
				}
				break;
			case 'get_books':
				if (!$this->data['login_is_super']) $this->kickout();
				$this->data['content'] = $this->books->get_all();
				break;
			case 'get_login_status':
				if ($this->data['login']->is_logged_in) {
					$this->data['content'] = '{"is_logged_in":1,"user_id":'.$this->data['login']->user_id.',"fullname":"'.htmlspecialchars($this->data['login']->fullname).'"}';
				} else {
					$this->data['content'] = '{"is_logged_in":0}';
				}
				break;
			case 'get_user_contributions':
				$this->load->model('page_model', 'pages');
				$this->load->model('version_model', 'versions');
				$this->load->model('user_model', 'users');
				if (!$this->data['login']->is_logged_in) $this->kickout();
				$user_id =@ (int) $_REQUEST['user_id'];
				$book_id =@ (int) $_REQUEST['book_id'];
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->users->is_a($this->data['user_level'], 'reader')) die ('{"error":"Invalid permissions"}');
				$this->data['content'] = $this->users->get_pages_contributed_to($book_id, $user_id);
				break;

			// Write
			case 'save_row':
				$this->load->model('user_book_model', 'user_books');
				$this->load->model('page_model', 'pages');
				$this->load->model('version_model', 'versions');
				$this->load->model('path_model', 'paths');
				$this->load->model('tag_model', 'tags');
				$this->load->model('annotation_model', 'annotations');
				$this->load->model('reply_model', 'replies');
				$id =@ (int) $_POST['id'];
				$section =@ $_POST['section'];
				if (empty($id) || empty($section)) die('{}');
				$book_id = (isset($_REQUEST['book_id']) && !empty($_REQUEST['book_id'])) ? (int) $_REQUEST['book_id'] : 0;
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if ('users'==$section) {  // All users
					if (!$this->data['login_is_super']) die ('{"error":"Invalid permissions to edit user"}');
				} elseif ('books'==$section) {  // All books
					if (!$this->data['login_is_super']) die ('{"error":"Invalid permissions to edit book"}');
				} elseif ('user_books'==$section) {  // Book users
					if (!$this->login_is_book_admin()) die ('{"error":"Invalid permissions"}');
				} elseif ('pages'==$section) {
					if (!$this->login_is_book_admin() && !$this->pages->is_owner($this->data['login']->user_id,$id)) die ('{"error":"Invalid permissions"}');
				} elseif ('versions'==$section) {
					if (!$this->login_is_book_admin() && !$this->versions->is_owner($this->data['login']->user_id,$id)) die ('{"error":"Invalid permissions"}');
				} else {
					die ('{"error":"Invalid section"}');
				}
				if (isset($_POST['password']) && !empty($_POST['password']) && !$this->data['login_is_super']) die ('{"error":"Invalid permissions to set password"}');
				try {
					if (!$this->data['content'] = $this->$section->save($_POST)) die ('{"error":"Problem saving"}');
				} catch (Exception $e) {
					die ('{"error":"'.$e->getMessage().'"}');
				}
				unset($this->data['content']['password']);
				break;
			case 'save_path_order':
				$this->load->model('version_model', 'versions');
				$version_id =@ (int) $_REQUEST['parent_version_id'];
				$this->data['book'] = $this->books->get_by_version_id($version_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin() && !$this->versions->is_owner($this->data['login']->user_id,$version_id)) die ("{'error':'Invalid permissions'}");
				$child_version_ids = (array) $_REQUEST['child_version_ids'];
				$this->data['content'] = $this->versions->save_order($version_id, $child_version_ids);
				break;
			case 'save_book_users':  // Save many users for one book (e.g., All books tab)
				$book_id =@ (int) $_REQUEST['id'];
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin($book_id)) show_error("{'error':'Invalid permissions'}");
				$user_ids =@ (array) $_REQUEST['selected_ids'];
				$this->books->delete_users($book_id);
				$this->data['content'] = $this->books->save_users($book_id, $user_ids, 'author');
				for ($j = 0; $j < count($this->data['content']); $j++) {
					$this->data['content'][$j]->id = $this->data['content'][$j]->user_id;
					$this->data['content'][$j]->title = $this->data['content'][$j]->fullname;
				}
				break;
			case 'save_user_books':  // Save one user-book relationship (e.g., Book users tab)
				$user_id =@ (int) $_REQUEST['id'];
				$book_ids =@ (array) $_REQUEST['selected_ids'];
				$list_in_index = (isset($_REQUEST['list_in_index']) && !$_REQUEST['list_in_index']) ? 0 : 1;
				$clear_user_books = (isset($_REQUEST['clear_user_books']) && !empty($_REQUEST['clear_user_books'])) ? true : false;
				foreach ($book_ids as $book_id) {
					$this->data['book'] = $this->books->get($book_id);
					$this->set_user_book_perms();
					if (!$this->login_is_book_admin()) die ("{'error':'Invalid permissions'}");
				}
				$this->data['content'] = $this->users->save_books($user_id, $book_ids, 'author', $list_in_index);
				for ($j = 0; $j < count($this->data['content']); $j++) $this->data['content'][$j]->id = $this->data['content'][$j]->book_id;
				break;
			case 'reorder_versions':
				$this->load->model('version_model', 'versions');
				$content_id =@ (int) $_REQUEST['content_id'];
				$this->data['book'] = $this->books->get_by_content_id($content_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin() && !$this->pages->is_owner($this->data['login']->user_id,$content_id)) die ("{'error':'Invalid permissions'}");
				$this->versions->reorder_versions($content_id);
				$this->data['content'] = $this->versions->get_all($content_id);
				break;
			case 'delete_content_path_links':
				$version_ids = (array) $_POST['version_ids'];
				$content_ids = array();
				foreach ($version_ids as $version_id) {
					$book = $this->books->get_by_version_id($version_id);
					$this->set_user_book_perms();
					if (!$this->login_is_book_admin() && !$this->versions->is_owner($this->data['login']->user_id,$version_id)) die ("{'error':'Invalid permissions'}");
					$content_ids[] =$this->page->remove_content_path_links_from_version($version_id);
				}
				$this->data['content'] = array('content_ids'=>$content_ids);
				break;
			case 'delete_content_tag_links':
				$this->load->model('page_model', 'page');
				$version_ids = (array) $_POST['version_ids'];
				$content_ids = array();
				foreach ($version_ids as $version_id) {
					$book = $this->books->get_by_version_id($version_id);
					$this->set_user_book_perms();
					if (!$this->login_is_book_admin() && !$this->versions->is_owner($this->data['login']->user_id,$version_id)) die ("{'error':'Invalid permissions'}");
					$content_ids[] =$this->page->remove_content_tag_links_from_version($version_id);
				}
				$this->data['content'] = array('content_ids'=>$content_ids);
				break;
			case 'delete_book_user':
				$user_id =@ (int) $_REQUEST['user_id'];
				$book_id =@ (int) $_REQUEST['book_id'];
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin()) die ("{'error':'Invalid permissions'}");
				if (!$this->books->delete_user($book_id, $user_id)) die ("{'error':'Could not delete'}");
				$this->data['content'] = array('actioned'=>'deleted');
				break;
			case 'save_page_book':
				$this->load->model('page_model', 'page');
				$content_id =@ (int) $_REQUEST['id'];
				$book_id =@ (int) $_REQUEST['selected_ids'][0];
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin() && !$this->page->is_owner($this->data['login']->user_id,$content_id)) die ("{'error':'Invalid permissions'}");
				$this->data['content'] = $this->page->save_book($content_id, $book_id);
				$this->data['content']->id = $this->data['content']->book_id;
				break;
			case 'save_content_category':
				$this->load->model('page_model', 'page');
				$content_id =@ (int) $_REQUEST['content_id'];
				$valid_cats = array('commentary', 'review', 'term');
				$category =@ trim($_REQUEST['category']);
				if (!in_array($category, $valid_cats)) die ('{"error":"Invalid category"}');
				$this->data['book'] = $this->books->get_by_content_id($content_id);
				$this->set_user_book_perms();
				if (!$this->login_is_book_admin() && !$this->pages->is_owner($this->data['login']->user_id,$content_id)) die ('{"error":"Invalid permissions"}');
				// TODO: Check the enum for term?
				$this->data['content'] = $this->page->save( array('id'=>$content_id,'category'=>$category) );
				break;
			case 'delete_content':
				$this->load->model('page_model', 'pages');
				$this->load->model('version_model', 'versions');
				$book_id =@ (int) $_REQUEST['book_id'];
				$this->data['book'] = $this->books->get($book_id);
				$this->set_user_book_perms();
				$content_ids = explode(',',@$_POST['content_ids']);
				$j = 0;
				// Scrub the array
				foreach ($content_ids as $content_id) {
					if (empty($content_id)) {
						unset($content_ids[$j]);
						continue;
					}
					$content_ids[$j] = (int) $content_ids[$j];
					$j++;
				}
				$version_ids = explode(',',@$_POST['version_ids']);
				$j = 0;
				// Scrub the array
				foreach ($version_ids as $version_id) {
					if (empty($version_id)) {
						unset($version_ids[$j]);
						continue;
					}
					$version_ids[$j] = (int) $version_ids[$j];
					$j++;
				}
				foreach ($content_ids as $content_id) {
					if (!$this->login_is_book_admin() && !$this->pages->is_owner($this->data['login']->user_id,$content_id)) die ("{'error':'Invalid permissions'}");
					$this->pages->delete($content_id);
				}
				foreach ($version_ids as $version_id) {
					if (!$this->login_is_book_admin() && !$this->versions->is_owner($this->data['login']->user_id,$version_id)) die ("{'error':'Invalid permissions'}");
					$this->versions->delete($version_id);
				}
				$this->data['content'] = array();
				$this->data['content']['content'] = (count($content_ids)) ? $content_ids : array();
				$this->data['content']['versions'] = (count($version_ids)) ? $version_ids : array();
				break;

		}

		$this->template->set_template('blank');
		$this->template->write_view('content', 'modules/data/json', $this->data);
		$this->template->render();

	}

	private function count_not_live($array=array()) {

		$count = 0;
		foreach ($array as $row) {
			if (!$row->is_live) $count++;
		}
		return $count;

	}

}

?>