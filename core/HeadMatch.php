<?php
namespace core;


require_once 'facebook/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;

class HeadMatch {
	protected $fbSdk = null;
	protected $fbCallbackUrl;
	protected $fbLoginUrl;
	protected $fbAppId;
	protected $fbAppSecret;
	protected $initialized = false;
	protected $fbScopes = null;
	protected $fbSession = null;
	
	// Constructor
	/**
	 *
	 * @param string $fb_callback_url        	
	 * @param string $fb_app_id        	
	 * @param string $fb_app_secret        	
	 * @param array $fb_scopes        	
	 */
	public function __construct($fb_callback_url, $fb_app_id, $fb_app_secret, $fb_scopes) {
		$this->fbCallbackUrl = $fb_callback_url;
		$this->fbAppId = $fb_app_id;
		$this->fbAppSecret = $fb_app_secret;
		$this->fbScopes = $fb_scopes;
		$this->fbInit ();
	}
	
	/**
	 * get fb session fromredirect
	 */
	public function saveFbSession() {
		$ses = $this->fbSdk->getSessionFromRedirect ();
		if (! null == $ses)
			$this->setFbSession ( $ses );
	}
	
	public function setFbSession($session) {
		$this->fbSession = $session;
	}
	public function getFbSession() {
		return $this->fbSession;
	}
	
	/*
	 * initialized the FB SDK
	 */
	private function fbInit() {
		FacebookSession::setDefaultApplication ( $this->fbAppId, $this->fbAppSecret );
		$this->fbSdk = new FacebookRedirectLoginHelper ( $this->fbCallbackUrl, $this->fbAppId, $this->fbAppSecret );
		$this->initialized = true;
	}
	
	/**
	 * get the URL containing the FB login form
	 * this can be use in window.open() javascript
	 *
	 * @throws NewException
	 */
	public function getUrlForPopUpLogin() {
		$this->checkSdk ();
		// check scopes if set
		if (null == $this->fbScopes) {
			throw new \Exception ( 'Fb scopes not  initialized...' );
		}
		return $this->fbSdk->getLoginUrl ( $this->getFbScopes (), "", true );
	}
	
	/*
	 * get the URL containign the FB login form for redirect
	 *
	 */
	public function getFbLoginRedirect() {
		$this->checkSdk ();
		
		// check scopes if set
		if (null == $this->getFbScopes ()) {
			throw new \Exception ( 'Fb scopes not  initialized...' );
		}
		
		return $this->fbSdk->getLoginUrl ( $this->getFbScopes (), "", false );
	}
	
	/*
	 * always return true if sdk was initialized otherwires throw error
	 */
	private function checkSdk() {
		if (! $this->initialized) {
			throw new \Exception ( 'Fb SDK not properly initialized...' );
		}
		return true;
	}
	
	/**
	 * Upload Picture to wall
	 * return id of picture.
	 * 
	 * @param unknown $message        	
	 * @param unknown $path_to_image        	
	 */
	public function uploadPictureToWall($message, $path_to_image) {
		return (new FacebookRequest ( $this->getFbSession (), 'POST', '/me/photos', array (
				'source' => new \CURLFile ( $path_to_image, $this->getImageMIME ( $path_to_image ) ),
				'message' => $message 
		) ))->execute ()->getGraphObject ()->getProperty ( 'id' );
	}
	
	/**
	 * get image Mime
	 * 
	 * @param unknown $path_to_image        	
	 * @throws \Exception
	 * @return unknown
	 */
	public function getImageMIME($path_to_image) {
		if (! file_exists ( $path_to_image )) {
			throw new \Exception ( 'Unable to get image size. File not exist ' . $path_to_image );
		}
		
		$size = getimagesize($path_to_image);
		
		$mime = $size["mime"];
					
	return $mime;
		
	}
	
	
	/**Setters and getters**/

	public function setFbScopes($scopes){
		$this->fbScopes = $scopes;
	}
	
	public function getFbScopes(){
		return $this->fbScopes;
	}
}