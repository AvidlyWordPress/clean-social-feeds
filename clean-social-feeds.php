<?php
/**
 * Clean Social Feeds.
 *
 * Helper class made specifically for WordPress, gets pure
 * data from different social media platforms without imposing
 * any limits to HTML-structure or CSS.
 *
 * @author  Tomi Mäenpää <tomimaen@gmail.com>
 * @url     https://github.com/tomimaen/clean-social-feeds/
 * @license GPLv2 or later.
 *
 * @since 1.0
 */
class Clean_Social_Feeds {

	public $facebook_app_id;
	public $facebook_app_secret;
	public $facebook_access_token;
	public $facebook_posts;

	public $twitter_consumer_id;
	public $twitter_consumer_secret;
	public $twitter_authorization_key;
	public $twitter_access_token;
	public $twitter_posts;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function __construct( $args ) {
		$this->_setSocialProperties( $args );
	}

	/**
	 * Set social app values.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private function _setSocialProperties( $args ) {

		// Facebook
		$this->facebook_app_id       = ( ! empty( $args['facebook_app_id']     ) ) ? $args['facebook_app_id']     : '';
		$this->facebook_app_secret   = ( ! empty( $args['facebook_app_secret'] ) ) ? $args['facebook_app_secret'] : '';
		$this->facebook_access_token = $this->facebook_app_id . '|' . $this->facebook_app_secret;

		// Twitter
		$this->twitter_consumer_key      = ( ! empty( $args['twitter_consumer_key']    ) ) ? $args['twitter_consumer_key']    : '';
		$this->twitter_consumer_secret   = ( ! empty( $args['twitter_consumer_secret'] ) ) ? $args['twitter_consumer_secret'] : '';
		$this->twitter_authorization_key = base64_encode( $this->twitter_consumer_key . ':' . $this->twitter_consumer_secret );
	}

	/**
	 * Get Facebook Page posts.
	 *
	 * @see API Explorer: https://developers.facebook.com/tools/explorer/
	 * @see Single post fields: https://developers.facebook.com/docs/graph-api/reference/v2.5/post
	 *
	 * @since 1.0
	 *
	 * @param $array $args {
	 * 		Arguments for loading the posts.
	 *
	 * 		@param bool   cache      If we shoud cache the results.
	 * 		@param int    cache_time Time to cache the results.
	 * 		@param int    limit      Number of posts to get.
	 * 		@param int    page_id    Page ID to get posts from.
	 * }
	 * @return string|array Error message or array of posts.
	 */
	public function getFacebookPagePosts( $args = null ) {

		// Set sensible defauls
		$args = wp_parse_args( $args, array(
			'cache'        => true,
			'cache_time'   => 60*60,
			'limit'        => 10,
			'page_id'      => '',
		) );

		// Check transient for existing results
		$transient = $this->_maybeGetTransient( $args['cache'], 'facebook_' . $args['page_id'] );

		if ( false !== $transient ) {
			return json_decode( $transient, true );
		}

		if ( empty( $this->facebook_app_id ) || empty( $this->facebook_app_secret ) || empty( $args['page_id'] ) ) {
			return new WP_Error( 'clean-social-feeds', 'Missing Facebook App information, check your settings.' );
		}

		// Load posts
		$url      = 'https://graph.facebook.com/' . $args['page_id'] . '?fields=about,name,picture,posts.limit(' . $args['limit'] . '){message,picture,full_picture,actions,caption,description,link,name,source,type}' . '&access_token=' . $this->facebook_access_token;
		$response = $this->_remoteUrl( 'get', $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Set transient
		$this->_maybeSetTransient( $args['cache'], 'facebook_' . $args['page_id'], $args['cache_time'], $response );

		$this->facebook_posts = json_decode( $response, true );
		return $this->facebook_posts;
	}

	/**
	 * Get Twitter User posts.
	 *
	 * @see Twitter timeline params: https://dev.twitter.com/rest/reference/get/statuses/user_timeline
	 *
	 * @since 1.0
	 *
	 * @param $array $args {
	 * 		Arguments for loading the posts.
	 *
	 * 		@param bool   cache      If we shoud cache the results.
	 * 		@param int    cache_time Time to cache the results.
	 * 		@param int    limit      Number of posts to get.
	 * 		@param string username   Twitter username to get posts from.
	 * }
	 * @return WP_Error|Array Error message or array of posts.
	 */
	public function getTwitterUserPosts( $args = null ) {

		// Set sensible defaults
		$args = wp_parse_args( $args, array(
			'cache'        => true,
			'cache_time'   => 60*60,
			'limit'        => 10,
			'username'     => '',
		) );

		// Check transient for existing results
		$transient = $this->_maybeGetTransient( $args['cache'], 'twitter_' . $args['username'] );

		if ( false !== $transient ) {
			return json_decode( $transient, true );
		}

		if ( empty( $this->twitter_consumer_key ) || empty( $this->twitter_consumer_secret ) || empty( $args['username'] ) ) {
			return new WP_Error( 'clean-social-feeds', 'Missing Twitter App information, check your settings.' );
		}

		if ( empty( $this->twitter_access_token ) ) {
			// Obtain bearer token
			// https://dev.twitter.com/oauth/application-only
			$token_args = array(
				'headers' => array(
					'Authorization' => 'Basic ' . $this->twitter_authorization_key,
					'Content-Type'  => 'application/x-www-form-urlencoded;charset=UTF-8',
				),
				'body'    => 'grant_type=client_credentials',
				'timeout' => 15,
			);

			$response = $this->_remoteUrl( 'post', 'https://api.twitter.com/oauth2/token', $token_args );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			// Get access token
			$response                   = json_decode( $response, true );
			$this->twitter_access_token = $response['access_token'];
		}

		$url        = 'https://api.twitter.com/1.1/statuses/user_timeline.json?count=' . $args['limit'] . '&screen_name=' . $args['username'] ;
		$posts_args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->twitter_access_token,
			),
		);

		$response = $this->_remoteUrl( 'get', $url, $posts_args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Set transient
		$this->_maybeSetTransient( $args['cache'], 'twitter_' . $args['username'], $args['cache_time'], $response );

		$this->twitter_posts = json_decode( $response, true );
		return $this->twitter_posts;
	}

	/**
	 * Maybe get transient.
	 *
	 * @since 1.0
	 *
	 * @param bool   $cache    Is caching enabled or not.
	 * @param string $cache_id Unique cache identifier.
	 * @return bool|string False if cache is disabled or transient
	 * 					   has expired, JSON string if success.
	 */
	private function _maybeGetTransient( $cache, $cache_id ) {
		if ( false === $cache ) {
			return false;
		}
		return get_transient( 'clean_social_feeds_' . $cache_id );
	}

	/**
	 * Maybe set transient.
	 *
	 * @since 1.0
	 *
	 * @param bool   $cache      Is caching enabled or not.
	 * @param string $cache_id   Unique cache identifier.
	 * @param int    $cache_time Time to cache the results.
	 * @param string $data       JSON string containing the data.
	 * @return false|void Returns false if cache is disabled.
	 */
	private function _maybeSetTransient( $cache, $cache_id, $cache_time, $data ) {
		if ( false === $cache ) {
			return false;
		}
		set_transient( 'clean_social_feeds_' . $cache_id, $data, $cache_time );
	}

	/**
	 * Remote URL.
	 *
	 * @since 1.0
	 *
	 * @see wp_remote_get()
	 *
	 * @param string $url  URL to remote to.
	 * @param array  $args (Optional) arguments for wp_remote_get().
	 * @return string|WP_Error
	 */
	private function _remoteUrl( $type, $url, $args = null ) {

		if ( 'get' === $type ) {
			$response = wp_remote_get( $url, $args );
		} elseif ( 'post' === $type ) {
			$response = wp_remote_post( $url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );

		if ( 200 != $response_code && ! empty( $response_message ) ) {
			return new WP_Error( $response_code, $response_message );
		} elseif ( 200 != $response_code ) {
			return new WP_Error( $response_code, 'Unknown error occurred.' );
		} else {
			return wp_remote_retrieve_body( $response );
		}
	}
}