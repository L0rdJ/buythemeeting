<?php
/**
 * @package AnotherOne
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    08 Dec 2013
 **/

spl_autoload_register( function ( $class ){
	$autoloads = require 'autoload.php';
	if( isset( $autoloads[$class] ) ) {
		include $autoloads[$class];
	}
} );

session_start();
mb_internal_encoding( 'UTF-8' );

use AnotherOne\Utils\Settings as Settings;
use AnotherOne\Controller\Index as Index;

// Force to generate VK cache
Settings::set( 'vk/cache/ttl', -1 );

$controller = new Index();
$controller->cronjob();