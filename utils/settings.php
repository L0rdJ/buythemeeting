<?php
/**
 * @package AnotherOne
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    02 Dec 2013
 **/

namespace AnotherOne\Utils;

abstract class Settings
{
	private static $storage = array(
		'general' => array(
			'timezone'    => 'Europe/Kiev',
			'environment' => 'development'
		),
		'smarty' => array(
			'template_dir' => 'views',
			'cache_dir'    => 'cache/templates',
			'compile_dir'  => 'cache/compile'
		),
		'vk' => array(
			'auth_url'       => 'https://oauth.vk.com/authorize',
			'api_url'        => 'https://api.vk.com/method',
			'app_id'         => 4032632,
			'options'        => array(
				'gid'        => 21350713,
				'albums'     => array( 182166054, 182166033 ),
				'start_bid'  => 0,
				'bid_step'   => 10,
				'bid_admins' => array( 101 ),
				'top_limit'  => 1000
			),
			'requests_delay' => 350000,
			'cache'          => array(
				'ttl' => 10800,
				'dir' => 'cache/vk'
			),
			'token'          => array(
				'token'   => 'c00adbe1b9234f3220b01da8d9787f10e5668bcc7528f455553a9e1e8c20a306499352ce651639010dc8a',
				'user_id' => 2249078
			),
			'export_file'    => 'cache/vk/export.xlsx'
		)
	);

	/**
	 * Returns the requested setting
	 * @param string $name setting name
	 * @return string setting value
	 */
	public static function get( $name ) {
		$parts = explode( '/', $name );

		$node = self::$storage;
		foreach( $parts as $part ) {
			if( in_array( $part, array_keys( $node ) ) === false ) {
				throw new Exception( '"' . $part . '" is not available setting/group (' . $name . ')' );
			}

			$node = $node[$part];
		}

		return $node;
	}

	/**
	 * Sets the setting value
	 * @param string $name setting name
	 * @param mixed $value setting value
	 * @return null
	 */
	public static function set( $name, $value ) {
		$parts = explode( '/', $name );

		$node =& self::$storage;
		foreach( $parts as $part ) {
			if( in_array( $part, array_keys( $node ) ) === false ) {
				throw new Exception( '"' . $part . '" is not available setting/group (' . $name . ')' );
			}

			$node =& $node[$part];
		}

		$node = $value;
	}
}