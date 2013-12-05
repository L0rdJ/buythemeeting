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
			'auth_url' => 'https://oauth.vk.com/authorize',
			'api_url'  => 'https://api.vk.com/method',
			'app_id'   => 4032632,
			'options'  => array(
				'gid'        => 21350713,
				'albums'     => array( 182166054, 182166033 ),
				'start_bid'  => 0,
				'bid_step'   => 10,
				'bid_admins' => array( 101 ),
				'top_limit'  => 10
			),
			'cache'    => array(
				'ttl' => 3600,
				'dir' => 'cache/vk'
			)
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
}