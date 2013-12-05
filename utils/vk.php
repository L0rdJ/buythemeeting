<?php
/**
 * @package AnotherOne
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    02 Dec 2013
 **/

namespace AnotherOne\Utils;

class VK
{
	private static $instance = null;

	private $clientID = null;
	private $token    = null;

	private function __construct() {
	}

	public static function getInstance() {
		if( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function setClientID( $id ) {
		$this->clientID = $id;
	}

	public function setToken( $token ) {
		$this->token = $token;
	}


	public function getAuthURL( array $settings = null ) {
		$settings = array_merge(
			array(
				'client_id'     => $this->clientID,
				'scope'         => 'offline,wall,friends,audio,video,photos,groups',
				'redirect_uri'  => 'http://oauth.vk.com/blank.html',
				'display'       => 'page',
				'v'             => 5.5,
				'response_type' => 'token'
			),
			(array) $settings
		);

		return Settings::get( 'vk/auth_url' ) . '?' . http_build_query( $settings );
	}

	public function request( $method, array $params = null, $cacheTTL = null ) {
		$url = Settings::get( 'vk/api_url' ) . '/' . $method . '.json?';
		if(
			is_array( $params )
			&& count( $params ) > 0
		) {
			$url .= http_build_query( $params ) . '&';
		}
		$filename = Settings::get( 'vk/cache/dir' ) . '/' . md5( $url );

		$url .= 'access_token=' . $this->token;

		$response = null;
		$cacheTTL = ( $cacheTTL === null ) ? Settings::get( 'vk/cache/ttl' ) : $cacheTTL;
		if(
			file_exists( $filename ) == false
			|| ( filemtime( $filename ) + $cacheTTL ) < time()
		) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_URL, $url );
			$data = curl_exec( $ch );
			curl_close( $ch );

			if( $data === false ) {
				throw new Exception( curl_error( $ch ) );
			} else {
				$response = $data;
				file_put_contents( $filename, $data );
			}
		}

		if( $response === null ) {
			$response = json_decode( file_get_contents( $filename ), true );
		}

		return $response;
	}
}
