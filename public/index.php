<?php
/**
 * @package AnotherOne
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    02 Dec 2013
 **/

chdir( '../' );
spl_autoload_register( function ( $class ){
	$autoloads = require 'autoload.php';
	if( isset( $autoloads[$class] ) ) {
		include $autoloads[$class];
	}
} );

session_start();
header( 'Content-Type: text/html; charset=utf-8' );
mb_internal_encoding( 'UTF-8' );

date_default_timezone_set( AnotherOne\Utils\Settings::get( 'general/timezone' ) );

$controller = isset( $_GET['module'] ) ? $_GET['module'] : 'index';
$action     = isset( $_GET['action'] ) ? $_GET['action'] : 'index';

$controllerClass = 'AnotherOne\\Controller\\' . ucfirst( $controller );
$actionMethod    = 'do' . ucfirst( $action );

try{
	if( class_exists( $controllerClass ) === false ) {
		throw new Exception( 'Could not find controller PHP class (' . $controllerClass . ')' );
	}
	$controllerObject = new $controllerClass( $action );

	$callback = array( $controllerObject, $actionMethod );
	if( is_callable( $callback ) === false ) {
		throw new Exception( 'Could not find action PHP method (' . $actionMethod . ')' );
	}

	call_user_func( $callback );
} catch( Exception $e ) {
	AnotherOne\Controller\Base::renderView( 'error', array( 'message' => $e->getMessage() ) );
}
