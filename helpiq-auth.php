<?php
	session_start();
class Helpiq_SSO_Support {
	
	//Replace the API key with your HelpIQ API Key
	private $helpiq_api_key = '9d1e2693fe4fc477cf26bc0df3372985';

	// your local login page
	private $default_login_url = 'login.php';

	//This is the remote authenication URL to call helpIQ. Do not change.
	private $helpiq_remote_url = 'http://www.helpdocsonline.com/access/remote/';

	public function __construct() {
		$current_url = explode('?', $_SERVER['REQUEST_URI']);
		$current_url = explode('/', $current_url[0]);
		array_pop($current_url);
		$current_url = 'http' . ( !empty($_SERVER['HTTPS']) ? 's' : '') .'://' . $_SERVER['HTTP_HOST'].implode('/', $current_url);
		$this->default_login_url = $current_url.'/'.$this->default_login_url;
		$this->do_helpiq_authorization();
	}

	// Upon log in of your application or website a session is established for the user.
	// This code will check the users session to determine if they are logged in. 
	// You can replace 'user_id' with whatever you want such as username, email, etc.
	// All the system is doing here is checking to see it there is a value. If there is no value require user to log in. 
	// If there is a value pass the site parameters to  http://www.helpdocsonline.com/access/remote/ and establish a session on HelpIQ. 
	public function helpiq_check_local_session() {
		return isset($_SESSION['username']) && !empty($_SESSION['username']);
	}

	//please destroy your local session data here
	public function helpiq_destroy_local_session() {
		unset($_SESSION['username']);
	}

	public function do_helpiq_authorization() {
		//If Remote logout URL is entered in HelpIQ the 'log-out' link can destroy the end-users session in HelpIQ and the session on your web application. 
		$action = isset($_REQUEST['action']) ? (string)$_REQUEST['action'] : 'login';
		$redirect_url = $this->default_login_url;
		if ('logout' == $action) {
			$this->helpiq_destroy_local_session();
			$redirect_url = $this->default_login_url;
		} else {
			//your helpIQ site URL
			$site = (string)$_REQUEST['site'];
			//return_page is passed by helpIQ, it will redirect the end-user to a specific page HelpIQ
			$return_page = (string)$_REQUEST['return_page'];
			// please check your end-user has logged in here
			$url_params = 'site='.$site.'&return_page='.$return_page;
			if ($this->helpiq_check_local_session()) {
				// if the end-user has logged in the customer's website/web application, call HelpIQ to estbalish a session
				$redirect_url = $this->helpiq_remote_url.'?hash='.md5($this->helpiq_api_key).'&'.$url_params;
			} else {
				// the end-user does not log in, redirect to error/log in page
				if (isset($_REQUEST['contextual']) && $_REQUEST['contextual']) {
					//if the refer page is a contextual help(lightbox/tooltip), redirect to show permission limit					
					$redirect_url = $this->helpiq_remote_url.'permission_limit/?login=false&'.$url_params;
				} else {
					//redirect to your local application login page
					$redirect_url = $this->default_login_url.'?'.$url_params;
				}
			}
		}
		header('location:'.$redirect_url);
	}
}

$helpiq_sso_support = new Helpiq_SSO_Support();
?>