<?php
/*
Plugin Name: WP YouTube Archives
Version: 0.1-alpha
Description: Display an entire, interactive YouTube Archive locally on-site.
Author: Brian Richards
Author URI: http://rzen.net
Text Domain: youtube-archives
Domain Path: /languages
*/

// Include settings page.
require_once( plugin_dir_path( __FILE__ ) . 'includes/settings.php' );

/**
 * Register new URL endpoint.
 *
 * @since  1.0.0
 */
function yta_add_url_endpoint() {
	add_rewrite_endpoint( 'id', EP_ALL );
}
add_action( 'init', 'yta_add_url_endpoint' );

/**
 * Register custom JS.
 *
 * @since  1.0.0
 */
function yta_register_scripts() {
	wp_register_script( 'yta-videos', plugin_dir_url( __FILE__ ) . '/js/yta-videos.js', array( 'jquery' ), '0.1-alpha', true );
}
add_action( 'wp_enqueue_scripts', 'yta_register_scripts' );

/**
 * Get playlist items from YouTube.
 *
 * See https://developers.google.com/youtube/v3/docs/playlistItems/list.
 *
 * @since  1.0.0
 *
 * @param  array  $args API Query Parameters.
 * @return array        API Response.
 */
function yta_get_videos( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'key'        => yta_get_option( 'api_key' ),
		'part'       => 'snippet',
		'playlistId' => yta_get_option( 'playlist_id' ),
		'maxResults' => max( 1, min( absint( yta_get_option( 'per_page' ) ), 50 ) ),
		'pageToken'  => '',
	) );

	$url = add_query_arg(
		$args,
		'https://www.googleapis.com/youtube/v3/playlistItems'
	);

	$response = wp_remote_retrieve_body( wp_remote_request( $url ) );

	return yta_normalize_api_response( $response );
}

/**
 * Normalize YouTube API response for internal use.
 *
 * See https://developers.google.com/youtube/v3/docs/playlistItems/list.
 *
 * @since  1.0.0
 *
 * @param  string $response JSON-encoded API Response.
 * @return array            Reponse data for prev/next pages and video item snippets.
 */
function yta_normalize_api_response( $response = null ) {
	$decoded = json_decode( $response );

	return array(
		'previous' => isset( $decoded->prevPageToken ) ? $decoded->prevPageToken : '',
		'next'     => isset( $decoded->nextPageToken ) ? $decoded->nextPageToken : '',
		'videos'   => isset( $decoded->items ) ? wp_list_pluck( $decoded->items, 'snippet' ) : array(),
	);
}

/**
 * Output loop of videos.
 *
 * @since 1.0.0
 *
 * @param array  $args API Query Parameters.
 */
function yta_video_loop( $videos = array() ) {

	if ( empty( $videos['videos'] ) || ! is_array( $videos['videos'] ) ) {
		return;
	}

	foreach ( $videos['videos'] as $video ) {
		yta_video_thumbnail_markup( $video );
	}

	wp_enqueue_script( 'yta-videos' );
	wp_localize_script( 'yta-videos', 'ytaVideos', array(
		'ajaxUrl'  => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
		'previous' => $videos['previous'],
		'next'     => $videos['next'],
	) );

}

/**
 * Generate markup for single video thumbnail.
 *
 * @since 1.0.0
 *
 * @param object $video YouTube Video Snippet object.
 */
function yta_video_thumbnail_markup( $video = null ) {

	$video_id  = $video->resourceId->videoId;
	$thumbnail = $video->thumbnails->medium;
	$title     = $video->title;

	?>
	<div class="video">
		<a class="video-link" data-video-id="<?php echo esc_attr( $video_id ); ?>" href="https://www.youtube.com/watch?v=<?php echo esc_attr( $video_id ); ?>" target="_blank">
			<span class="screenshot">
				<i class="fa fa-play"></i>
				<img src="<?php echo esc_url( $thumbnail->url ); ?>" width="<?php echo absint( $thumbnail->width ); ?>" height="<?php echo absint( $thumbnail->height ); ?>">
			</span>
			<span class="title"><?php echo esc_html( $title ); ?></span>
		</a>
	</div><!-- .video -->
	<?php
}

/**
 * Generate markup for YouTube iFrame Player.
 *
 * @since  1.0.0
 *
 * @param  string $video_id YouTube Video ID to pre-load.
 */
function yta_embed_video_player( $video_id = null ) {

	if ( empty( $video_id ) ) {
		$video_id = yta_get_featured_video_id();
	}

	echo '<div class="video-wrap"><div id="ytplayer" data-video-id="' . $video_id . '"></div></div>';

	wp_enqueue_script( 'yta-videos' );
}

/**
 * Get featured video ID.
 *
 * @since  1.0.0
 *
 * @return string Video ID.
 */
function yta_get_featured_video_id() {

	$video_id = yta_get_option( 'featured_video_id' );

	if ( empty( $video_id ) ) {
		$newest_video = yta_get_videos( array( 'maxResults' => 1 ) );
		$video_id = isset( $newest_video['videos'][0] ) ? $newest_video['videos'][0]->resourceId->videoId : null;
	}

	return $video_id;
}

/**
 * Get video loop via AJAX.
 *
 * @since 1.0.0
 */
function yta_ajax_video_loop() {

	$pageToken = isset( $_REQUEST['pageToken'] ) ? $_REQUEST['pageToken'] : '';

	$videos = yta_get_videos( array(
		'pageToken' => $pageToken,
	) );

	ob_start();
	foreach ( $videos['videos'] as $video ) {
		yta_video_thumbnail_markup( $video );
	}
	$output = ob_get_clean();

	wp_send_json_success( array(
		'previous' => $videos['previous'],
		'next'     => $videos['next'],
		'videos'   => $output,
	) );
}
add_action( 'wp_ajax_yta-video-loop', 'yta_ajax_video_loop' );
add_action( 'wp_ajax_nopriv_yta-video-loop', 'yta_ajax_video_loop' );

function yta_sharing_permalink( $permalink ) {
	$video_id = get_query_var( 'id' );

	if ( ! empty( $video_id ) ) {
		$permalink = home_url( "/videos/id/{$video_id}/" );
	}

	return $permalink;
}
add_filter( 'sharing_permalink', 'yta_sharing_permalink', 10, 2 );
