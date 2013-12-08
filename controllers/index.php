<?php
/**
 * @package AnotherOne
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    02 Dec 2013
 **/

namespace AnotherOne\Controller;

use AnotherOne\Utils\VK as VK;
use AnotherOne\Utils\Settings as Settings;

class Index extends Base
{
	const TOKEN_VAR = 'vk_token';

	private $API = null;

	public function __construct() {
		parent::__construct();

		if( isset( $this->get['action'] ) && $this->get['action'] == 'updateToken' ) {
			return null;
		}

		$this->API = VK::getInstance();
		$this->API->setClientID( Settings::get( 'vk/app_id' ) );

		$token = $this->getVKToken();
		// Setting default token
		if( $token === null ) {
			$this->setVKToken(
				Settings::get( 'vk/token/token' ),
				Settings::get( 'vk/token/user_id' ),
				0
			);
			$token = $this->getVKToken();
		}
		$isTokenValid = true;
		if( is_array( $token ) && isset( $token['access_token'] ) ) {
			// validate token
			$this->API->setToken( $token['access_token'] );

			try{
				$r = $this->API->request( 'users.get', array(), -1 );
				if(
					is_array( $r ) === false
					|| (int) $r['response'][0]['uid'] !== $token['user_id']
				) {
					$isTokenValid = false;
				}
			} catch( Exception $e ) {
				$isTokenValid = false;
			}
		} else {
			$isTokenValid = false;
		}

		if( $isTokenValid === false ) {
			$options = array(
				'redirect_uri' => 'http://' . $_SERVER['HTTP_HOST'] . '/blank.html',
			);
			$this->redirect( $this->API->getAuthURL( $options ) );
		}
	}

	public function doIndex() {
		$this->redirect( $this->getURL( 'index', 'top' ) );
	}

	public function doUpdateToken() {
		if(
			isset( $this->get['access_token'] )
			&& isset( $this->get['expires_in'] )
			&& isset( $this->get['user_id'] )
		) {
			$this->setVKToken(
				$this->get['access_token'],
				(int) $this->get['user_id'],
				(int) $this->get['expires_in']
			);
			$this->addMessage( 'Your VK account is connected', 'success' );
			$this->redirect( $this->getURL( 'index' ) );
		} else {
			throw new \Exception( 'Required params are missing' );
		}
	}

	public function doTop() {
		$albums = $this->getAlbums();
		if( count( $albums ) === 0 ) {
			throw new \Exception( 'No albums were fethed. Are you member of the group?' );
		}

		$processedAlbums = array();
		foreach( $albums as $album ) {
			$processedAlbums[] = $this->processAlbum( $album );
		}

		self::renderView(
			'top',
			array(
				'albums'    => $processedAlbums,
				'top_limit' => Settings::get( 'vk/options/top_limit' )
			)
		);
	}

	private function getAlbums( $withPhotos = true ) {
		$r = $this->API->request( 'photos.getAlbums', array( 'gid' => Settings::get( 'vk/options/gid' ) ) );
		if( isset( $r['error'] ) ) {
			throw new \Exception( $r['error']['error_msg'] );
		}

		$albums = array();
		foreach( $r['response'] as $album ) {
			if( in_array( $album['aid'], Settings::get( 'vk/options/albums' ) ) ) {
				$albums[ $album['aid'] ] = $album;
			}
		}
		$albums = array_reverse( $albums );

		if( $withPhotos ) {
			foreach( $albums as $i => $album ) {
				$albums[ $i ]['photos'] = $this->getAlbumPhotos( $album );
			}
		}

		return $albums;
	}

	private function processAlbum( array $album ) {
		$album['photos'] = array();


		$photos = $this->getAlbumPhotos( $album );
		foreach( $photos as $photo ) {
			if( mb_strpos( mb_strtolower( $photo['text'] ), 'лот' ) === false ) {
				continue;
			}

			$processedPhoto = $this->processPhoto( $photo );
			$album['photos'][] = $processedPhoto;
		}

		usort( $album['photos'], function( $a, $b ) {
			return $b['current_bid'] - $a['current_bid'];
		} );

		return $album;
	}

	private function getAlbumPhotos( array $album ) {
		$r = $this->API->request(
			'photos.get',
			array(
				'gid'      => Settings::get( 'vk/options/gid' ),
				'aid'      => $album['aid'],
				'extended' => 1
			)
		);
		if( isset( $r['error'] ) ) {
			throw new \Exception( $r['error']['error_msg'] );
		}

		$photos = array();
		foreach( $r['response'] as $photo ) {
			if( is_array( $photo ) ) {
				$photos[] = $photo;
			}
		}

		return $photos;
	}

	private function processPhoto( array $photo ) {
		$photo['current_bid']        = (int) Settings::get( 'vk/options/start_bid' );
		$photo['current_comment']    = null;
		$photo['processed_comments'] = array();

		$info = explode( '<br>', $photo['text'] );
		$photo['info'] = array(
			'number'    => $info[0],
			'name'      => isset( $info[1] ) ? $info[1] : null,
			'url'       => isset( $info[2] ) ? $info[2] : null,
			'desc'      => count( $info ) > 3 ? implode( '<br>', array_slice( $info, 3 ) ) : null,
			'full_desc' => count( $info ) > 2 ? implode( '<br>', array_slice( $info, 2 ) ) : null,
		);

		if(
			$photo['info']['url'] !== null
			&& strpos( $photo['info']['url'], 'http' ) !== 0
		) {
			$photo['info']['url'] = 'http://' . $photo['info']['url'];
		}
		if( (int) $photo['comments']['count'] === 0 ) {
			return $photo;
		}

		$comments   = $this->getPhotoComments( $photo );
		$currentBid = $photo['current_bid'];
		foreach( $comments as $commentKey => $comment ) {
			$procssedComment = $comment;
			$procssedComment['bid']       = 0;
			$procssedComment['bid_error'] = null;

			$text     = $comment['message'];
			$bidsText = preg_replace( '^\[.*]\]*^', '', strip_tags( $text ) );

			preg_match_all( '/(\d+)/', $bidsText, $bids );
			$bids = $bids[1];
			if( count( $bids ) > 0 ) {
				$isCommentValidBid = false;
				$expectedBid       = $photo['current_bid'] + (int) Settings::get( 'vk/options/bid_step' );
				foreach( $bids as $bid ) {
					if(
						$bid == $expectedBid
						|| (
							(int) $bid !== (int) Settings::get( 'vk/options/bid_step' )
							&& in_array( $comment['from_id'], Settings::get( 'vk/options/bid_admins' ) )
						)
					) {
						$isCommentValidBid = true;
						$photo['current_bid']     = (int) $bid;
						$photo['current_comment'] = $comment;
						break;
					}
				}

				if( $isCommentValidBid ) {
					$procssedComment['bid'] = (int) $bid;
				} else {
					$procssedComment['bid_error'] = 'Invalid bid: ' . $bid . '. Expected bid: ' . $expectedBid;
				}
			} else {
				$procssedComment['bid_error'] = 'No bid can be extracted';
			}

			$photo['processed_comments'][] = $procssedComment;
		}

		return $photo;
	}

	private function getPhotoComments( array $photo ) {
		$r = $this->API->request(
			'photos.getComments',
			array(
				'pid'      => $photo['pid'],
				'owner_id' => '-' . Settings::get( 'vk/options/gid' ),
				'offset'   => 0,
				'count'    => 100,
				'sort'     => 'asc'
			)
		);
		if( isset( $r['error'] ) ) {
			$this->addMessage( $r['error']['error_msg'] );
			return array();
		}

		$comments = array();
		foreach( $r['response'] as $comment ) {
			if(is_array( $comment ) ) {
				$comments[] = $comment;
			}
		}

		return $comments;
	}

	private function getVKToken() {
		return isset( $_SESSION[ self::TOKEN_VAR ] ) ? (array) $_SESSION[ self::TOKEN_VAR ] : null;
	}

	private function setVKToken( $token, $userID, $expiresIn ) {
		$_SESSION[ self::TOKEN_VAR ] = array(
			'access_token' => $token,
			'user_id'      => $userID,
			'ttl'          => $expiresIn,
			'created_on'   => time()
		);
	}

	public function cronjob() {
		$albums = $this->getAlbums();
		foreach( $albums as $album ) {
			$this->processAlbum( $album );
		}	
	}
}
