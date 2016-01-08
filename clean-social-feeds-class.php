<?php
/**
 * Clean Social Feeds.
 *
 * Handle the retrieval and caching
 * of the data from social media feeds.
 *
 * @since 1.0
 */
class Clean_Social_Feeds {

	public $facebook_app_id;
	public $facebook_app_secret;
	public $facebook_access_token;
	public $facebook_posts;

	public $twitter_consumer_key;
	public $twitter_consumer_secret;
	public $twitter_access_token;
	public $twitter_posts;

	public $instagram_client_id;
	public $instagram_client_secret;
	public $instagram_access_token;
	public $instagram_posts;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function __construct() {
		$this->_setSocialProperties();
	}

	/**
	 * Set social app values.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private function _setSocialProperties() {

		$settings = get_option( 'clean_social_feeds_settings' );

		// Facebook
		$this->facebook_app_id       = $settings['facebook_app_id'];
		$this->facebook_app_secret   = $settings['facebook_app_secret'];
		$this->facebook_access_token = $this->facebook_app_id . '|' . $this->facebook_app_secret;

		// Twitter
		$this->twitter_consumer_key    = $settings['twitter_consumer_key'];
		$this->twitter_consumer_secret = $settings['twitter_consumer_secret'];
		$this->twitter_access_token    = $settings['twitter_access_token'];

		// Instagram
		$this->instagram_client_id     = $settings['instagram_client_id'];
		$this->instagram_client_secret = $settings['instagram_client_secret'];
		$this->instagram_access_token  = $settings['instagram_access_token'];
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

		$response = Clean_Social_Feeds::remoteUrl(
			'get',
			'https://graph.facebook.com/' . $args['page_id'] . '?fields=about,name,picture,posts.limit(' . $args['limit'] . '){message,picture,full_picture,actions,caption,description,link,name,source,type}' . '&access_token=' . $this->facebook_access_token
		);

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

		if ( empty( $this->twitter_consumer_key ) || empty( $this->twitter_consumer_secret ) || empty( $this->twitter_access_token ) || empty( $args['username'] ) ) {
			return new WP_Error( 'clean-social-feeds', 'Missing Twitter App information, check your settings.' );
		}

		$response = Clean_Social_Feeds::remoteUrl(
			'get',
			'https://api.twitter.com/1.1/statuses/user_timeline.json?count=' . $args['limit'] . '&screen_name=' . $args['username'],
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->twitter_access_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Set transient
		$this->_maybeSetTransient( $args['cache'], 'twitter_' . $args['username'], $args['cache_time'], $response );

		$this->twitter_posts = json_decode( $response, true );
		return $this->twitter_posts;
	}

	/**
	 * Get Instagram User posts.
	 *
	 * @since 1.2
	 *
	 * @param $array $args {
	 * 		Arguments for loading the posts.
	 *
	 * 		@param bool   cache      If we shoud cache the results.
	 * 		@param int    cache_time Time to cache the results.
	 * 		@param int    limit      Number of posts to get.
	 * 		@param string user_id    Instagram user ID to get posts from. Defaults to 'self'.
	 * }
	 * @return WP_Error|Array Error message or array of posts.
	 */
	function getInstagramUserPosts( $args = null ) {

		// Set sensible defaults
		$args = wp_parse_args( $args, array(
			'cache'        => true,
			'cache_time'   => 60*60,
			'limit'        => 10,
			'user_id'     => 'self',
		) );

		// Check transient for existing results
		$transient = $this->_maybeGetTransient( $args['cache'], 'instagram_' . $args['user_id'] );

		if ( false !== $transient ) {
			return json_decode( $transient, true );
		}

		if ( empty( $this->instagram_client_id ) || empty( $this->instagram_client_secret ) || empty( $this->instagram_access_token ) || empty( $args['user_id'] ) ) {
			return new WP_Error( 'clean-social-feeds', 'Missing Instagram App information, check your settings.' );
		}

		$response = Clean_Social_Feeds::remoteUrl(
			'get',
			'https://api.instagram.com/v1/users/' . $args['user_id'] . '/media/recent/?access_token=' . $this->instagram_access_token . '&count=' . $args['limit']
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Set transient
		$this->_maybeSetTransient( $args['cache'], 'instagram_' . $args['user_id'], $args['cache_time'], $response );

		$this->instagram_posts = json_decode( $response, true );
		return $this->instagram_posts;
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
	 * @see wp_remote_get() and wp_remote_post()
	 *
	 * @param string $url  URL to remote to.
	 * @param array  $args (Optional) arguments for wp_remote_get().
	 * @return string|WP_Error
	 */
	public static function remoteUrl( $type, $url, $args = null ) {

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