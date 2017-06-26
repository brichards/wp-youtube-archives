<?php

/**
 * YTA Settings.
 *
 * @version 1.0.0
 */
class YTA_Admin {

	/**
 	 * Option key and page slug.
 	 *
 	 * @var string
 	 */
	private $key = 'yta_settings';

	/**
 	 * Options page metabox ID.
 	 *
 	 * @var string
 	 */
	private $metabox_id = 'yta_metabox';

	/**
	 * Options Page Title.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page Hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

	public function __construct() {
		// Set our title
		$this->title = __( 'YouTube Archive', 'yta' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'cmb2_init', array( $this, 'register_metabox' ) );
		add_filter( 'plugin_action_links_youtube-archives/youtube-archives.php', array( $this, 'plugin_action_settings_link' ) );
	}

	/**
	 * Add "Settings" link to plugin actions.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $links Plugin Action links.
	 * @return array        Plugin Action links.
	 */
	public function plugin_action_settings_link( $links = array() ) {
		$links[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'admin.php?page=' . $this->key ),
			__( 'Settings', 'yta' )
		);

		return $links;
	}

	/**
	 * Register setting with WP.
	 *
	 * @since 1.0.0
	 */
	public function register_setting() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu item.
	 *
	 * @since 1.0.0
	 */
	public function add_menu_page() {
		$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'display_menu_page' ), 'dashicons-video-alt3' );
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Admin page markup.
	 *
	 * @since 1.0.0
	 */
	public function display_menu_page() {
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php if ( isset( $_POST['api_key'] ) ) { $this->display_success_message(); } ?>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key, array( 'cmb_styles' => false ) ); ?>
		</div>
		<?php
	}

	public function display_success_message() {
		?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
		<p><strong><?php _e( 'Settings saved.' ); ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.' ); ?></span></button></div>
		<?php
	}

	/**
	 * Register metabox with CMB2.
	 *
	 * @since 1.0.0
	 */
	function register_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		$cmb->add_field( array(
			'name'       => __( 'API Key', 'itm' ),
			'id'         => 'api_key',
			'type'       => 'text',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Playlist ID', 'itm' ),
			'id'         => 'playlist_id',
			'type'       => 'text',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Featured Video ID', 'itm' ),
			'id'         => 'featured_video_id',
			'type'       => 'text',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Videos per page', 'itm' ),
			'desc'       => __( 'Maximum number of videos to load per page request. API Maximum is 50.', 'itm' ),
			'id'         => 'per_page',
			'default'    => '48',
			'type'       => 'text',
		) );

	}

	/**
	 * Allow retrieval of private variables.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $field Field to retrieve.
	 * @return mixed          Field value or exception.
	 */
	public function __get( $field ) {

		// Allowed fields
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

/**
 * Helper function to get the YTA_Admin object.
 *
 * @since  1.0.0
 *
 * @return YTA_Admin object
 */
function yta_admin() {
	static $object = null;

	if ( is_null( $object ) ) {
		$object = new yta_Admin();
		$object->hooks();
	}

	return $object;
}
yta_admin();

/**
 * Wrapper function for cmb2_get_option.
 *
 * @since  1.0.0
 *
 * @param  string  $key Options array key.
 * @return mixed        Option value.
 */
function yta_get_option( $key = '' ) {
	return cmb2_get_option( yta_admin()->key, $key );
}
