<?php
/**
 * @package AnotherOne
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    02 Dec 2013
 **/

namespace AnotherOne\Controller;
use AnotherOne\Utils\Settings as Settings;

abstract class Base
{
	public $get  = array();
	public $post = array();

	/**
	 * Read data from GET and POST to controller attributes
	 */
	public function __construct() {
		$this->get  = $_GET;
		$this->post = $_POST;
	}

	/**
	 * Render specified view
	 * @param string $tpl
	 * @param array $data Data for passing to template
	 */
	public static function renderView( $tpl, $data = null ) {
		if( $data === null ) {
			$data = array();
		}

		$messages = self::getMessages();
		self::clearMessages();

		$data['base_web_dir'] = self::getWebDir();

		$smarty = self::getSmarty();
		$smarty->assign( 'data', $data );
		$smarty->assign( 'messages', $messages );
		$smarty->assign( 'base_web_dir', self::getWebDir() );
		$smarty->assign( 'current_action', isset( $_GET['action'] ) ? $_GET['action'] : null );
		$smarty->assign( 'current_module', isset( $_GET['module'] ) ? $_GET['module'] : null );
		$smarty->display( $tpl . '.tpl' );
	}

	/**
	 * @return Smarty
	 */
	public static function getSmarty() {
		$smarty            = new \Smarty;
		$smarty->debugging = false;
		$smarty->caching   = false;
		$smarty->setTemplateDir( Settings::get( 'smarty/template_dir' ) );
		$smarty->setCacheDir( Settings::get( 'smarty/cache_dir' ) );
		$smarty->setCompileDir( Settings::get( 'smarty/compile_dir' ) );
		return $smarty;
	}

	public static function getWebDir() {
		return parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	}

	/**
	 * Build URL for specified module/action
	 * @param string $module Module name
	 * @param string|null $action Action name
	 * @param array $params
	 * @return string
	 */
	public static function getURL( $module, $action = null, array $params = null ) {
		$url = self::getWebDir() . '?module=' . $module;

		if( $action !== null ) {
			$url .= '&action=' . $action;
		}

		if( is_array( $params ) ) {
			foreach( $params as $p => $v ) {
				$url .= '&' . rawurlencode( $p ) . '=' . rawurlencode( $v );
			}
		}

		return $url;
	}

	public static function redirectToModule( $module, $action = null, $params = null ) {
		self::redirect( self::getURL( $module, $action, $params ) );
	}

	public static function redirect( $url ) {
		header( 'Location: ' . $url );
		exit();
	}

	/**
	 * Add new message and save to session
	 * @param string $message
	 * @param string
	 */
	public function addMessage( $message, $type = 'error' ) {
		$messages   = self::getMessages();
		$messages[] = array(
			'message' => $message,
			'type'    => $type
		);

		$_SESSION['messages'] = $messages;
	}

	/**
	 * Messages list
	 * @return array[]
	 */
	public static function getMessages() {
		return isset( $_SESSION['messages'] ) ? (array) $_SESSION['messages'] : array();
	}

	public static function clearMessages() {
		$_SESSION['messages'] = array();
	}

	public static function isProductionEnv() {
		return Settings::get( 'general/environment' ) == 'production';
	}
}