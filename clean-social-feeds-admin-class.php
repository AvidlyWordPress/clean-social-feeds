<?php
/**
 * Clean Social Feeds Admin.
 *
 * Contains admin -related functionality
 * for saving application details and
 * requesting access tokens.
 *
 * @since 1.2
 */
class Clean_Social_Feeds_Admin {

	/**
	 * Constructor.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function __construct() {
		$this->actions();
		$this->filters();
	}

	/**
	 * Admin actions for the plugin.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function actions() {
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'admin_init', array( $this, 'instagram_request_access_token' ) );
	}

	/**
	 * Admin filters for the plugin.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function filters() {
		add_filter( 'pre_update_option_clean_social_feeds_settings', array( $this, 'twitter_request_access_token' ), 10, 2 );
	}

	/**
	 * Add submenu for plugin settings.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'options-general.php',
			'Clean Social Feeds',
			'Clean Social Feeds',
			'manage_options',
			'clean_social_feeds',
			array( $this, 'add_submenu_page_callback' )
		);
	}

	/**
	 * Plugin settings page constructor.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function add_submenu_page_callback() {
		?>
		<div class='wrap'>
			<h2>Clean Social Feeds</h2>
				<form method='post' action='options.php'>
				<?php
					settings_fields( 'facebook_app_id' );
					settings_fields( 'facebook_app_secret' );
					settings_fields( 'twitter_consumer_key' );
					settings_fields( 'twitter_consumer_secret' );
					settings_fields( 'twitter_access_token' );
					settings_fields( 'instagram_client_id' );
					settings_fields( 'instagram_client_secret' );
					settings_fields( 'instagram_access_token' );
					do_settings_sections( 'clean_social_feeds' );
					submit_button();
				?>
				</form>
			</div>
		<?php
	}

	/**
	 * Individual settings for the plugin.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function add_settings() {

		// Facebook
		add_settings_section(
			'clean_social_feeds_facebook',
			'',
			array( $this, 'facebook_callback' ),
			'clean_social_feeds'
		);

		add_settings_field(
			'facebook_app_id',
			__( 'App ID' ),
			array( $this, 'facebook_app_id_callback' ),
			'clean_social_feeds',
			'clean_social_feeds_facebook'
		);

		register_setting( 'facebook_app_id', 'clean_social_feeds_settings' );

		add_settings_field(
			'facebook_app_secret',
			__( 'App Secret' ),
			array( $this, 'facebook_app_secret_callback' ),
			'clean_social_feeds',
			'clean_social_feeds_facebook'
		);

		register_setting( 'facebook_app_secret', 'clean_social_feeds_settings' );

		// Twitter
		add_settings_section(
			'clean_social_feeds_twitter',
			'',
			array( $this, 'twitter_callback' ),
			'clean_social_feeds'
		);

		add_settings_field(
			'twitter_consumer_key',
			__( 'Consumer Key' ),
			array( $this, 'twitter_consumer_key_callback' ),
			'clean_social_feeds',
			'clean_social_feeds_twitter'
		);

		register_setting( 'twitter_consumer_key', 'clean_social_feeds_settings' );

		add_settings_field(
			'twitter_consumer_secret',
			__( 'Consumer Secret' ),
			array( $this, 'twitter_consumer_secret_callback' ),
			'clean_social_feeds',
			'clean_social_feeds_twitter'
		);

		register_setting( 'twitter_consumer_secret', 'clean_social_feeds_settings' );

		add_settings_field(
			'twitter_access_token',
			__( 'Access Token' ),
			array( $this, 'twitter_access_token_callback' ),
			'clean_social_feeds',
			'clean_social_feeds_twitter'
		);

		register_setting( 'twitter_access_token', 'clean_social_feeds_settings' );

		// Instagram
		add_settings_section(
			'clean_social_feeds_instagram',
			'',
			array( $this, 'instagram_callback' ),
			'clean_social_feeds'
		);

		add_settings_field(
			'instagram_client_id',
			__( 'Client ID' ),
			array( $this, 'instagram_client_id_callback' ),
			'clean_social_feeds',
			'clean_social_feeds_instagram'
		);

		register_setting( 'instagram_client_id', 'clean_social_feeds_settings' );

		add_settings_field(
			'instagram_client_secret',
			__( 'Client Secret' ),
			array( $this, 'instagram_client_secret_callback' ),
			'clean_social_feeds',
			'clean_social_feeds_instagram'
		);

		register_setting( 'instagram_client_secret', 'clean_social_feeds_settings' );

		add_settings_field(
			'instagram_access_token',
			__( 'Access Token' ),
			array( $this, 'instagram_access_token_callback' ),
			'clean_social_feeds',
			'clean_social_feeds_instagram'
		);

		register_setting( 'instagram_access_token', 'clean_social_feeds_settings' );
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function facebook_callback() {
		?>
		<h3>Facebook</h3>
		<p><?php printf( __( 'Register an app at %1$s and fill the fields below.', 'clean-social-feeds' ), '<a href="https://developers.facebook.com/apps/">developers.facebook.com/apps/</a>' ); ?></p>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function facebook_app_id_callback() {
		$settings = get_option( 'clean_social_feeds_settings' ); ?>
		<label for="clean_social_feeds_settings[facebook_app_id]">
			<input name="clean_social_feeds_settings[facebook_app_id]" id="clean_social_feeds_settings[facebook_app_id]" type="text" placeholder="" value="<?php echo $settings['facebook_app_id']; ?>" class="regular-text" />
		</label>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function facebook_app_secret_callback() {
		$settings = get_option( 'clean_social_feeds_settings' ); ?>
		<label for="clean_social_feeds_settings[facebook_app_secret]">
			<input name="clean_social_feeds_settings[facebook_app_secret]" id="clean_social_feeds_settings[facebook_app_secret]" type="text" placeholder="" value="<?php echo $settings['facebook_app_secret']; ?>" class="regular-text" />
		</label>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function twitter_callback() {
		?>
		<h3>Twitter</h3>
		<p><?php printf( __( 'Register an app at %1$s and fill the fields below.', 'clean-social-feeds' ), '<a href="https://apps.twitter.com/app/new">apps.twitter.com/app/new</a>' ); ?></p>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function twitter_consumer_key_callback() {
		$settings = get_option( 'clean_social_feeds_settings' ); ?>
		<label for="clean_social_feeds_settings[twitter_consumer_key]">
			<input name="clean_social_feeds_settings[twitter_consumer_key]" id="clean_social_feeds_settings[twitter_consumer_key]" type="text" placeholder="" value="<?php echo $settings['twitter_consumer_key']; ?>" class="regular-text" />
		</label>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function twitter_consumer_secret_callback() {
		$settings = get_option( 'clean_social_feeds_settings' ); ?>
		<label for="clean_social_feeds_settings[twitter_consumer_secret]">
			<input name="clean_social_feeds_settings[twitter_consumer_secret]" id="clean_social_feeds_settings[twitter_consumer_secret]" type="text" placeholder="" value="<?php echo $settings['twitter_consumer_secret']; ?>" class="regular-text" />
		</label>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function twitter_access_token_callback() {
		$settings = get_option( 'clean_social_feeds_settings'); ?>
		<label for="clean_social_feeds_settings[twitter_access_token]">
			<input name="clean_social_feeds_settings[twitter_access_token]" id="clean_social_feeds_settings[twitter_access_token]" type="text" placeholder="" value="<?php echo $settings['twitter_access_token']; ?>" class="regular-text" aria-describedby="clean_social_feeds_settings[twitter_access_token]-description" />
		</label>
		<p class="description" id="clean_social_feeds_settings[twitter_access_token]-description"><?php _e( 'Access token is requested automatically when Consumer ID and Consumer Secret are set. Empty this field and save settings to request a new token.', 'clean-social-feeds' ); ?></p>
		<?php
	}

	/**
	 * Request Twitter access token.
	 *
	 * @since 1.2
	 *
	 * @param array $new_value New values for wp_option settings.
	 * @param array $old_value Old values for wp_option settings.
	 * @return array Modified new values for wp_option settings.
	 */
	public function twitter_request_access_token( $new_value, $old_value ) {

		if ( ! empty( $new_value['twitter_access_token'] ) ) {
			return $new_value;
		}

		if ( empty( $new_value['twitter_consumer_key'] ) || empty( $new_value['twitter_consumer_secret'] ) ) {
			return $new_value;
		}

		// Obtain bearer token
		// https://dev.twitter.com/oauth/application-only
		$token_args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $new_value['twitter_consumer_key'] . ':' . $new_value['twitter_consumer_secret'] ),
				'Content-Type'  => 'application/x-www-form-urlencoded;charset=UTF-8',
			),
			'body'    => 'grant_type=client_credentials',
			'timeout' => 15,
		);

		$response = Clean_Social_Feeds::remoteUrl( 'post', 'https://api.twitter.com/oauth2/token', $token_args );

		if ( is_wp_error( $response ) ) {
			add_settings_arror( 'Twitter Access Token', 'twitter-access-token', __( 'Error requesting Instagram access token. Check your settings and try again.', 'clean-social-feeds' ) );
			return;
		}

		$response                          = json_decode( $response, true );
		$new_value['twitter_access_token'] = $response['access_token'];

		add_settings_error( 'Twitter Access Token', 'twitter-access-token', __( 'Twitter access token requested successfully.' ), 'updated' );
		return $new_value;
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function instagram_callback() {
		?>
		<h3>Instagram</h3>
		<p><?php printf( __( 'Register an app at %1$s and fill the fields below.', 'clean-social-feeds' ), '<a href="https://www.instagram.com/developer/clients/manage/">instagram.com/developer/clients/manage/</a>' ); ?></p>
		<p><?php _e( 'Redirect URI for the app:', 'clean-social-feeds' ); ?> <code><?php echo admin_url(); ?>options-general.php?page=clean_social_feeds</code>.</p>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function instagram_client_id_callback() {
		$settings = get_option( 'clean_social_feeds_settings' ); ?>
		<label for="clean_social_feeds_settings[instagram_client_id]">
			<input name="clean_social_feeds_settings[instagram_client_id]" id="clean_social_feeds_settings[instagram_client_id]" type="text" placeholder="" value="<?php echo $settings['instagram_client_id']; ?>" class="regular-text" />
		</label>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function instagram_client_secret_callback() {
		$settings = get_option( 'clean_social_feeds_settings' ); ?>
		<label for="clean_social_feeds_settings[instagram_client_secret]">
			<input name="clean_social_feeds_settings[instagram_client_secret]" id="clean_social_feeds_settings[instagram_client_secret]" type="text" placeholder="" value="<?php echo $settings['instagram_client_secret']; ?>" class="regular-text" />
		</label>
		<?php
	}

	/**
	 * Setting callback.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function instagram_access_token_callback() {
		$settings = get_option( 'clean_social_feeds_settings'); ?>
		<label for="clean_social_feeds_settings[instagram_access_token]">
			<input name="clean_social_feeds_settings[instagram_access_token]" id="clean_social_feeds_settings[instagram_access_token]" type="text" placeholder="" value="<?php echo $settings['instagram_access_token']; ?>" class="regular-text" aria-describedby="clean_social_feeds_settings[instagram_access_token]-description" />
		</label>
		<?php if ( empty( $settings['instagram_client_id'] ) || empty( $settings['instagram_client_secret'] ) ) : ?>
		<p class="description" id="clean_social_feeds_settings[instagram_access_token]-description"><?php _e( 'You can request an access token after filling in Client ID and Client Secret and saving the settings.', 'clean-social-feeds' ); ?></p>
		<?php else : ?>
		<p class="description" id="clean_social_feeds_settings[instagram_access_token]-description"><?php _e( 'Remember to save any changes before requesting an access token, otherwise those changes will be lost.', 'clean-social-feeds' ); ?></p>
		<p><a class="button button-primary" href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo $settings['instagram_client_id']; ?>&redirect_uri=<?php echo admin_url(); ?>options-general.php?page=clean_social_feeds&response_type=code">Request access token</a></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Request Instagram access token.
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function instagram_request_access_token() {

		// Instagram return URL has 'code' parameter
		if ( ! isset( $_GET['code'] ) && empty( $_GET['code'] ) ) {
			return;
		}

		$settings = get_option( 'clean_social_feeds_settings' );

		$response = Clean_Social_Feeds::remoteUrl( 'post', 'https://api.instagram.com/oauth/access_token', array(
			'body' => array(
				'client_id'     => $settings['instagram_client_id'],
				'client_secret' => $settings['instagram_client_secret'],
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => admin_url() . 'options-general.php?page=clean_social_feeds',
				'code'          => $_GET['code']
			),
		) );

		if ( is_wp_error( $response ) ) {
			add_settings_error( 'Instagram Access Token', 'instagram-access-token', __( 'Error requesting Instagram access token. Check your settings and try again.', 'clean-social-feeds' ) );
			return;
		}

		$response                           = json_decode( $response, true );
		$settings['instagram_access_token'] = $response['access_token'];
		update_option( 'clean_social_feeds_settings', $settings );

		add_settings_error( 'Instagram Access Token', 'instagram-access-token', __( 'Instagram access token requested successfully.', 'clean-social-feeds' ), 'updated' );
	}
}