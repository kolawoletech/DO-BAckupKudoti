<?php
namespace um_ext\um_groups\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Discussion
 * @package um_ext\um_groups\core
 */
class Groups_Discussion {


	/**
	 * Global actions
	 * @var array
	 */
	var $global_actions;


	/**
	 * Init __construct
	 */
	function __construct() {

		$this->global_actions['status'] = __( 'New wall post', 'um-groups' );
		$this->global_actions['new-user'] = __( 'New user', 'um-groups' );
		$this->global_actions['new-post'] = __( 'New blog post', 'um-groups' );
		$this->global_actions['new-product'] = __( 'New product', 'um-groups' );
		$this->global_actions['new-group'] = __('New Group','um-groups');
		$this->global_actions['new-gform'] = __('New Gravity From','um-groups');
		$this->global_actions['new-gform-submission'] = __('New Gravity From Answer','um-groups');
		$this->global_actions['new-follow'] = __( 'New follow', 'um-groups' );
		$this->global_actions['new-topic'] = __( 'New forum topic', 'um-groups' );

	}


	/**
	 * Save activity post
	 * @param  array   $array
	 * @param  boolean $update_post
	 * @param  integer $update_post_id
	 * @return integer $post_id
	 */
	function save( $array = array(), $update_post = FALSE, $update_post_id = NULL ) {
		extract( $array );

		$args = array(
			'post_title'  => '',
			'post_type'   => 'um_groups_discussion',
			'post_status' => 'publish',
			'post_author' => $array['author'],
		);

		$file = empty( $array['custom_path'] ) ? 'discussion/html/' . $array['template'] . '.php' : $array['custom_path'];
		$t_args = compact( 'args' );
		$args['post_content'] = UM()->get_template( $file, um_groups_plugin, $t_args );

		$search = array(
			'{author_name}',
			'{author_profile}',
			'{group_name}',
			'{group_permalink}',
			'{group_author_name}',
			'{group_author_profile}',
			'{user_name}',
			'{user_profile}',
			'{user_photo}',
			'{post_title}',
			'{post_url}',
			'{post_excerpt}',
			'{post_image}',
			'{price}',
		);
		$search = apply_filters( 'um_groups_search_tpl', $search );

		$replace = array(
			isset( $array['author_name'] ) ? $array['author_name'] : '',
			isset( $array['author_profile'] ) ? $array['author_profile'] : '',
			isset( $array['group_name'] ) ? $array['group_name'] : '',
			isset( $array['group_permalink'] ) ? $array['group_permalink'] : '',
			isset( $array['group_author_name'] ) ? $array['group_author_name'] : '',
			isset( $array['group_author_profile'] ) ? $array['group_author_profile'] : '',
			isset( $array['user_name'] ) ? $array['user_name'] : '',
			isset( $array['user_profile'] ) ? $array['user_profile'] : '',
			isset( $array['user_photo'] ) ? $array['user_photo'] : '',
			isset( $array['post_title'] ) ? $array['post_title'] : '',
			isset( $array['post_url'] ) ? $array['post_url'] : '',
			isset( $array['post_excerpt'] ) ? $array['post_excerpt'] : '',
			isset( $array['post_image'] ) ? $array['post_image'] : '',
			isset( $array['price'] ) ? $array['price'] : '',
		);
		$replace = apply_filters( 'um_groups_replace_tpl', $replace, $array );

		if (in_array( $array['template'], array( 'new-user' ) )) {

		} else {
			$args['post_content'] = str_replace( $search, $replace, $args['post_content'] );
		}

		$args['post_content'] = html_entity_decode(trim( $args['post_content'] ));


		// Update post content
		if ( $update_post ) {

			$args['ID'] = $update_post_id;
			$args['post_title'] = $array['post_title'];
			wp_update_post( $args );

			return $update_post_id;
		}

		$post_id = wp_insert_post( $args );

		$group_id = absint(  $array['group_id'] );

		wp_update_post( array( 'ID' => $post_id, 'post_title' => $post_id, 'post_name' => $post_id ) );

		$_permalink = add_query_arg( 'group_post', $post_id, get_permalink( $group_id ) );

		update_post_meta( $post_id, '_wall_id', $array['wall_id'] );
		update_post_meta( $post_id, '_action', $array['template'] );
		update_post_meta( $post_id, '_user_id', $array['author'] );
		update_post_meta( $post_id, '_likes', 0 );
		update_post_meta( $post_id, '_comments', 0 );
		update_post_meta( $post_id, '_group_id', $group_id );

		$group_moderation = get_post_meta( $group_id,  '_um_groups_posts_moderation', true );

		// Administrators/Moderators posts are automatically approved
		if( UM()->Groups()->api()->can_moderate_posts( $group_id ) ){

			update_post_meta( $post_id, '_group_moderation', 'approved' );

			UM()->Groups()->api()->set_group_last_activity( $group_id );

		}else{ // Members
			if( 'auto-published' == $group_moderation ){
				update_post_meta( $post_id, '_group_moderation', 'approved' );

				UM()->Groups()->api()->set_group_last_activity( $group_id );

			}else{
				update_post_meta( $post_id, '_group_moderation', 'pending_review' );
				$output['pending_review'] = true;
			}
		}

		if ( isset( $array['related_id'] ) ) {
			update_post_meta( $post_id, '_related_id', absint( $array['related_id'] ) );
		}


		return $post_id;
	}


	/**
	 * Grab followed user IDs
	 * @return  array or null
	 */
	function followed_ids(){
		$array = array();

		if ( ! $this->followed_activity() )
			return NULL;

		if ( ! is_user_logged_in() )
			return array( 0 );

		$array[] = get_current_user_id();

		$following = UM()->Followers_API()->api()->following( get_current_user_id() );
		if ($following) {
			foreach ($following as $k => $arr) {
				$array[] = $arr['user_id1'];
			}
		}

		if (isset( $array ))
			return $array;

		return NULL;
	}


	/**
	 * Check if enabled followed activity only
	 * @return boolean
	 */
	function followed_activity() {
		if ( class_exists( 'UM_Followers_API' ) && UM()->options()->get( 'groups_followed_users' ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Return to activity post after login
	 * Usage: UM()->Groups()->discussion()->login_to_interact( $group_id );
	 * @param  integer $group_id
	 * @param  integer $post_id
	 * @return string
	 */
	function login_to_interact( $group_id = null, $post_id = null ){

		if ( empty( $group_id ) ) {
			$group_id = get_the_ID();
		}
		$curr_page = get_permalink( $group_id );
		if ( !empty( $post_id ) ) {
			$curr_page = add_query_arg( 'group_post', $post_id, $curr_page );
		}

		$pattern = stripslashes( UM()->options()->get( 'groups_need_to_login' ) );

		$text = str_replace( array(
				'{current_page}',
				'{login_page}',
				'{register_page}',
			), array(
				$curr_page,
				add_query_arg( 'redirect_to', $curr_page, um_get_core_page('login') ),
				add_query_arg( 'redirect_to', $curr_page, um_get_core_page('register') ),
			), $pattern );

		return $text;
	}


	/**
	 * Get comment content
	 * @param  string $content
	 * @return string
	 */
	function commentcontent( $content ) {
		$content = convert_smilies( $content );
		$content = $this->make_links_clickable( $content );
		$content = $this->hashtag_links( $content );

		return $content;
	}


	/**
	 * Shorten any string based on word count
	 * @param  string $string
	 * @return string
	 */
	function shorten_string( $string ){
		$retval = $string;
		$wordsreturned = UM()->options()->get( 'groups_post_truncate' );
		if (!$wordsreturned) return $string;
		$array = explode( " ", $string );
		if (count( $array ) <= $wordsreturned) {
			$retval = $string;
		} else {
			$res = array_splice( $array, $wordsreturned );
			$retval = implode( " ", $array ) . " <span class='um-groups-seemore'>(<a href='' class='um-link'>" . __( 'See more', 'um-groups' ) . "</a>)</span>" . " <span class='um-groups-hiddentext'>" . implode( " ", $res ) . "</span>";
		}

		return $retval;
	}


	/**
	 * Can edit a user comment
	 * @param  integer $comment_id
	 * @param  integer $user_id
	 * @return boolean
	 */
	function can_edit_comment( $comment_id, $user_id ){
		if (!$user_id)
			return FALSE;
		$comment = get_comment( $comment_id );
		if ($comment->user_id == $user_id)
			return TRUE;

		return FALSE;
	}


	/**
	 *  Get a summarized content length
	 * @param  integer $post_id
	 * @param  string  $has_video
	 * @return string
	 */
	function get_content( $post_id = 0, $has_video = '' ){
		global $post;

		if ($post_id) {
			$post = get_post( $post_id );
			$content = $post->post_content;
		} else {
			$post_id = get_the_ID();
			$content = get_the_content();
		}

		$has_attached_photo = get_post_meta( $post_id, '_photo', TRUE );
		$has_oembed = get_post_meta( $post_id, '_oembed', TRUE );

		if (empty( $has_attached_photo ) || empty( $has_video )) {
			$video_content = $this->setup_video( $content, $post_id );
			if ($video_content['has_video'] == TRUE) {
				$content = $video_content['content'];
			}
		}

		if (trim( $content ) != '') {

			if ($this->get_action_type( $post_id ) == 'status') {
				$content = $this->shorten_string( $content );
			}
			$content = convert_smilies( $content );
			$content = $this->make_links_clickable( $content );
			$content = trim( $content );
			$content = $this->hashtag_links( $content );

			// strip avatars
			if (preg_match( '/\<img src=\"([^\"]+)\" class="(gr)?avatar/', $content, $matches )) {
				$src = $matches[1];
				$found = @getimagesize( $src );
				if (!$found) {
					$content = str_replace( $src, um_get_default_avatar_uri(), $content );
				}
			}

			$content = $this->remove_vc_from_excerpt( $content );

			if ($has_oembed) {
				$content .= $has_oembed;
			}

			$search = array(
				'{author_name}',
				'{author_profile}',
			);

			$replace = array(
				um_user( 'display_name' ),
				um_user_profile_url(),
			);


			$content = str_replace( $search, $replace, $content );

			return nl2br( $content );
		}

		return '';
	}


	/**
	 * Get content link
	 * @param  string $content
	 * @return string or null
	 */
	function get_content_link( $content ){

		$arr_urls = wp_extract_urls( $content );
		if (isset( $arr_urls ) && !empty( $arr_urls )) {
			foreach ($arr_urls as $key => $url) {
				if (
					!strstr( $url, 'vimeo' ) &&
					!strstr( $url, 'youtube' ) &&
					!strstr( $url, 'youtu.be' )
				) {

					return $url;
				}
			}
		}

		return NULL;
	}


	/**
	 * Check if URL is oEmbed supported
	 * @param  string  $url
	 * @return boolean
	 */
	function is_oEmbed( $url ){

		$providers = array(
			'mixcloud.com'   => array( 'height' => 200 ),
			'soundcloud.com' => array( 'height' => 200 ),
			'instagram.com'  => array( 'height' => 500, 'width' => 500 ),
			'twitter.com'    => array( 'height' => 500, 'width' => 700 ),
			't.co'           => array( 'height' => 500, 'width' => 700 ),
		);

		$providers = apply_filters( 'um_groups_oembed_providers', $providers );
		foreach ($providers as $provider => $size) {
			if (strstr( $url, $provider )) {
				return wp_oembed_get( $url, $size );
			}
		}

		return FALSE;
	}


	/**
	 * Set URL meta
	 * @param string $url
	 * @param integer $post_id
	 */
	function set_url_meta( $url, $post_id ){

		$request = wp_remote_get( $url );
		$response = wp_remote_retrieve_body( $request );

		$html = new \DOMDocument();
		@$html->loadHTML( mb_convert_encoding( $response, 'HTML-ENTITIES', 'UTF-8' ) );
		$tags = NULL;

		$title = $html->getElementsByTagName( 'title' );
		$tags['title'] = $title->item( 0 )->nodeValue;

		foreach ($html->getElementsByTagName( 'meta' ) as $meta) {
			if ($meta->getAttribute( 'property' ) == 'og:image') {
				$src = trim( str_replace( '\\', '/', $meta->getAttribute( 'content' ) ) );
				$data = $this->is_image( $src );
				if (is_array( $data )) {
					$tags['image'] = $src;
					$tags['image_width'] = $data[0];
					$tags['image_height'] = $data[1];
				}
			}
			if ($meta->getAttribute( 'name' ) == 'description') {
				$tags['description'] = trim( str_replace( '\\', '/', $meta->getAttribute( 'content' ) ) );
			}
		}

		if (!isset( $tags['image'] )) {
			$stop = FALSE;
			foreach ($html->getElementsByTagName( 'img' ) as $img) {
				if ($stop == TRUE) continue;
				$src = trim( str_replace( '\\', '/', $img->getAttribute( 'src' ) ) );
				$data = $this->is_image( $src );
				if (is_array( $data )) {
					$tags['image'] = $src;
					$tags['image_width'] = $data[0];
					$tags['image_height'] = $data[1];
					$stop = TRUE;
				}
			}
		}

		/* Display the meta now */

		if (isset( $tags['image_width'] ) && $tags['image_width'] <= 400) {
			$content = '<span class="post-meta" style="position:relative;min-height: ' . ( absint( $tags['image_height'] / 2 ) - 10 ) . 'px;padding-left:' . $tags['image_width'] / 2 . 'px;"><a href="{post_url}" target="_blank">{post_image} {post_title} {post_excerpt} {post_domain}</a></span>';
		} else {
			$content = '<span class="post-meta"><a href="{post_url}" target="_blank">{post_image} {post_title} {post_excerpt} {post_domain}</a></span>';
		}

		if (isset( $tags['description'] )) {
			if (isset( $tags['image_width'] ) && $tags['image_width'] <= 400) {
				$content = str_replace( '{post_excerpt}', '', $content );
			} else {
				$content = str_replace( '{post_excerpt}', '<span class="post-excerpt">' . $tags['description'] . '</span>', $content );
			}
		} else {
			$content = str_replace( '{post_excerpt}', '', $content );
		}

		if (isset( $tags['title'] )) {
			$content = str_replace( '{post_title}', '<span class="post-title">' . mb_convert_encoding( $tags['title'], 'HTML-ENTITIES', 'UTF-8' ) . '</span>', $content );
		} else {
			$content = str_replace( '{post_title}', '<span class="post-title">' . __( 'Untitled', 'um-groups' ) . '</span>', $content );
		}

		if (isset( $tags['image'] )) {
			if (isset( $tags['image_width'] ) && $tags['image_width'] <= 400) {
				$content = str_replace( '{post_image}', '<span class="post-image" style="position:absolute;left:0;top:0;width:' . $tags['image_width'] / 2 . 'px;"><img src="' . $tags['image'] . '" alt="" title="" class="um-activity-featured-img" /></span>', $content );
			} else {
				$content = str_replace( '{post_image}', '<span class="post-image"><img src="' . $tags['image'] . '" alt="" title="" class="um-activity-featured-img" /></span>', $content );
			}
		} else {
			$content = str_replace( '{post_image}', '', $content );
		}

		$parse = parse_url( $url );

		$content = str_replace( '{post_url}', $url, $content );

		$content = str_replace( '{post_domain}', '<span class="post-domain">' . strtoupper( $parse['host'] ) . '</span>', $content );


		update_post_meta( $post_id, '_shared_link', trim( $content ) );

		return trim( $content );
	}


	/**
	 * Checks if image is valid
	 * @param  string  $url
	 * @return boolean
	 */
	function is_image( $url ){
		$size = @getimagesize( $url );
		if (isset( $size['mime'] ) && strstr( $size['mime'], 'image' ) && !strstr( $size['mime'], 'gif' ) && isset( $size[0] ) && absint( $size[0] ) > 100 && isset( $size[1] ) && ( $size[0] / $size[1] >= 1 ) && ( $size[0] / $size[1] <= 3 ))
			return $size;

		return 0;
	}


	/**
	 * Convert hashtags
	 * @param  string $content
	 * @return string
	 */
	function hashtag_links( $content ) {
		preg_match_all( '/>[^<]*#([^\s<]+)/', $content, $matches );

		if ( isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( $matches[1] as $match ) {
				$link = '<a href="' . add_query_arg( 'hashtag', $match, um_get_core_page( 'activity' ) ) . '" class="um-link">#' . $match . '</a>';
				$content = str_replace( '#' . $match, $link, $content );
			}
		}

		return $content;
	}


	/**
	 * Add hashtag
	 * @param  integer  $post_id
	 * @param  string  $content
	 * @param  boolean $append
	 */
	function hashtagit( $post_id, $content, $append = FALSE ){
		preg_match_all( '/(?<!\&)#([^\s]+)/', $content, $matches );
		if (isset( $matches[1] ) && is_array( $matches[1] )) {
			wp_set_post_terms( $post_id, $matches[1], 'um_hashtag', $append );
		}
	}


	/**
	 * Get a possible photo
	 * @param  integer $post_id
	 * @param  string  $class
	 * @return string html
	 */
	function get_photo( $post_id = 0, $class = '', $author_id = null ) {

		$uri = get_post_meta( $post_id, '_photo', true );

		if ( ! $uri )
			return '';

		$uri = wp_basename( $uri );
		$user_base_url = UM()->uploader()->get_upload_user_base_url( $author_id );

		if ( 'backend' == $class ) {
			$content = "<a href='{$uri}' target='_blank'><img src='{$user_base_url}/{$uri}' alt='' /></a>";
		} else {
			$content = "<a href='#' class='um-photo-modal' data-src='{$user_base_url}/{$uri}'><img src='{$user_base_url}/{$uri}' alt='' /></a>";
		}

		return $content;
	}


	/**
	 * Get a possible video
	 * @param  integer $post_id
	 * @param  array   $args
	 * @return string html
	 */
	function get_video( $post_id = 0, $args = array() ){
		$uri = get_post_meta( $post_id, '_video_url', TRUE );
		if (!$uri)
			return '';
		$content = wp_oembed_get( $uri, $args );

		return $content;
	}


	/**
	 * Strip video URLs and prepare for convertion
	 * @param  string $content
	 * @param  integer $post_id
	 * @return string
	 */
	function setup_video( $content, $post_id ){
		preg_match_all( "#(https?://vimeo.com)/([0-9]+)#i", $content, $matches1 );
		preg_match_all( '/https?:\/\/(?:www\.)?youtu(?:\.be|be\.com)\/watch(?:\?(.*?)&|\?)v=([a-zA-Z0-9_\-]+)(\S*)/i', $content, $matches2 );
		$has_video = FALSE;
		if (isset( $matches1 ) && isset( $matches1[0] )) {
			foreach ($matches1[0] as $key => $val) {
				$videos[] = trim( $val );
			}
		}
		if (isset( $matches2[0] )) {
			foreach ($matches2[0] as $key => $val) {
				$videos[] = trim( $val );
			}
		}
		if (isset( $videos )) {
			$content = str_replace( $videos[0], '', $content );
			update_post_meta( $post_id, '_video_url', $videos[0] );
			$has_video = TRUE;
		} else {
			delete_post_meta( $post_id, '_video_url' );
		}

		return array( 'has_video' => $has_video, 'content' => $content );
	}


	/**
	 * Can post on that wall
	 * @return integer
	 */
	function can_write(){
		$res = 1;

		if (UM()->roles()->um_user_can( 'groups_posts_off' ))
			$res = 0;

		if (!is_user_logged_in())
			$res = 0;

		$res = apply_filters( 'um_groups_can_post_on_wall', $res );

		return $res;
	}


	/**
	 * Can comment on wall
	 * @return integer
	 */
	function can_comment(){
		$res = 1;

		if (UM()->roles()->um_user_can( 'groups_comments_off' ))
			$res = 0;

		if (!is_user_logged_in())
			$res = 0;

		$res = apply_filters( 'um_groups_can_post_comment_on_wall', $res );

		return $res;
	}


	/**
	 * Show wall
	 */
	function show_wall(){
		wp_enqueue_script( 'um_scrollto' );
		wp_enqueue_script( 'um_groups_discussion' );
		wp_enqueue_style( 'um_groups_discussion' );

		$can_view = apply_filters( 'um_wall_can_view', -1, um_profile_id() );
		if ($can_view == -1) {

			echo do_shortcode( '[ultimatemember_wall user_id=' . um_profile_id() . ']' );

		} else {

			echo '<div class="um-profile-note"><span><i class="um-faicon-lock"></i>' . $can_view . '</span></div>';

		}
	}


	/**
	 * Time difference
	 * @param  string $from
	 * @param  string $to
	 * @return string
	 */
	function human_time_diff( $from, $to = '' ){
		if (empty( $to )) {
			$to = time();
		}
		$diff = (int)abs( $to - $from );
		if ($diff < 60) {

			$since = __( 'Just now', 'um-groups' );

		} else if ($diff < HOUR_IN_SECONDS) {

			$mins = round( $diff / MINUTE_IN_SECONDS );
			if ($mins <= 1)
				$mins = 1;
			if ($mins == 1) {
				$since = sprintf( __( '%s min', 'um-groups' ), $mins );
			} else {
				$since = sprintf( __( '%s mins', 'um-groups' ), $mins );
			}

		} else if ($diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS) {

			$hours = round( $diff / HOUR_IN_SECONDS );
			if ($hours <= 1)
				$hours = 1;
			if ($hours == 1) {
				$since = sprintf( __( '%s hr', 'um-groups' ), $hours );
			} else {
				$since = sprintf( __( '%s hrs', 'um-groups' ), $hours );
			}

		} else if ($diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS) {

			$days = round( $diff / DAY_IN_SECONDS );
			if ($days <= 1)
				$days = 1;
			if ($days == 1) {
				$since = sprintf( __( 'Yesterday at %s', 'um-groups' ), date_i18n( __( 'g:ia', 'um-groups' ), $from ) );
			} else {
				$since = sprintf( __( '%s at %s', 'um-groups' ), date_i18n( __( 'F d', 'um-groups' ), $from ), date_i18n( __( 'g:ia', 'um-groups' ), $from ) );
			}

		} else if ($diff < 30 * DAY_IN_SECONDS && $diff >= WEEK_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-groups' ), date_i18n( __( 'F d', 'um-groups' ), $from ), date_i18n( __( 'g:ia', 'um-groups' ), $from ) );

		} else if ($diff < YEAR_IN_SECONDS && $diff >= 30 * DAY_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-groups' ), date_i18n( __( 'F d', 'um-groups' ), $from ), date_i18n( __( 'g:ia', 'um-groups' ), $from ) );

		} else if ($diff >= YEAR_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-groups' ), date_i18n( __( 'F d, Y', 'um-groups' ), $from ), date_i18n( __( 'g:ia', 'um-groups' ), $from ) );

		}

		return apply_filters( 'um_groups_human_time_diff', $since, $diff, $from, $to );
	}


	/**
	 * Get faces of people who liked a post
	 * @param  integer  $post_id
	 * @param  integer $num
	 * @return string html
	 */
	function get_faces( $post_id, $num = 10 ) {
		$res = '';
		$users = get_post_meta( $post_id, '_liked', true );
		if ( $users && is_array( $users ) ) {
			$users = array_reverse( $users );
			$users = array_unique( $users );
			$users = array_slice( $users, 0, $num );
			foreach ( $users as $user_id ) {
				if ( absint( $user_id ) && $user_id ) {
					$res .= get_avatar( $user_id, 80 );
				}
			}
		}

		return '<a href="#" data-post_id="' . $post_id . '" class="um-activity-show-likes um-tip-s" title="' . __( 'People who like this', 'um-groups' ) . '" data-post_id="' . $post_id . '">' . $res . '</a>';
	}


	/**
	 * Hide a comment for a user
	 * @param  integer $comment_id
	 */
	function user_hide_comment( $comment_id ){
		$users = get_comment_meta( $comment_id, '_hidden_from', TRUE );
		$users[get_current_user_id()] = current_time( 'timestamp' );
		update_comment_meta( $comment_id, '_hidden_from', $users );
	}


	/**
	 * Unhide a comment for a user
	 * @param  integer $comment_id
	 */
	function user_unhide_comment( $comment_id ){
		$users = get_comment_meta( $comment_id, '_hidden_from', TRUE );
		$current_id = get_current_user_id();
		if ( $users && is_array( $users ) && isset( $users[ $current_id ] ) ) {
			unset( $users[$current_id] );
		}
		if (!$users) {
			delete_comment_meta( $comment_id, '_hidden_from' );
		} else {
			update_comment_meta( $comment_id, '_hidden_from', $users );
		}
	}


	/**
	 * Checks if user hidden comment
	 * @param  integer $comment_id
	 * @return integer
	 */
	function user_hidden_comment( $comment_id ){
		$users = get_comment_meta( $comment_id, '_hidden_from', TRUE );
		$current_id = get_current_user_id();
		if( $users && is_array( $users ) && isset( $users[ $current_id ] ) ) {
			return 1;
		}
		return 0;
	}


	/**
	 * Checks if user liked specific wall comment
	 * @param  integer $comment_id
	 * @return boolean
	 */
	function user_liked_comment( $comment_id ){
		$users = get_comment_meta( $comment_id, '_liked', TRUE );
		$current_id = get_current_user_id();
		if( $users && is_array( $users ) && in_array( $current_id, $users ) ) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * Checks if user liked specific wall post
	 * @param  integer $post_id
	 * @return boolean
	 */
	function user_liked( $post_id ){
		$users = get_post_meta( $post_id, '_liked', TRUE );
		$current_id = get_current_user_id();
		if( $users && is_array( $users ) && in_array( $current_id, $users ) ) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * Check if post is reported
	 * @param integer $post_id
	 * @return integer
	 */
	function reported( $post_id ){
		$reported = get_post_meta( $post_id, '_reported', TRUE );

		return ( $reported ) ? 1 : 0;
	}


	/**
	 * Get action name
	 * @param  integer $post_id
	 * @return string
	 */
	function get_action( $post_id ){
		$action = (string)get_post_meta( $post_id, '_action', TRUE );
		$action = ( $action ) ? $action : 'status';

		return isset( $this->global_actions[$action] ) ? $this->global_actions[$action] : '';
	}


	/**
	 * Get action type
	 * @param  integer $post_id
	 * @return string
	 */
	function get_action_type( $post_id ){
		$action = (string)get_post_meta( $post_id, '_action', TRUE );
		$action = ( $action ) ? $action : 'status';

		return $action;
	}


	/**
	 * Get comment time
	 * @param  string $time
	 * @return string
	 */
	function get_comment_time( $time ){
		$timestamp = strtotime( $time );
		$time = $this->human_time_diff( $timestamp, current_time( 'timestamp' ) );

		return $time;
	}


	/**
	 * Get comment link
	 * @param  string $post_link
	 * @param  integer $comment_id
	 * @return string
	 */
	function get_comment_link( $post_link, $comment_id ){
		$link = add_query_arg( 'wall_comment_id', $comment_id, $post_link );

		return $link;
	}


	/**
	 * Get post time
	 * @param  integer $post_id
	 * @return string
	 */
	function get_post_time( $post_id ){
		$time = $this->human_time_diff( get_the_time( 'U', $post_id ), current_time( 'timestamp' ) );

		return apply_filters( 'um_groups_human_post_time', $time, $post_id );
	}


	/**
	 * Gets post permalink
	 * @param  integer $post_id
	 * @return string url
	 */
	function get_permalink( $post_id ){

		$group_id = get_post_meta( $post_id, '_group_id', true );

		$url = get_the_permalink( $group_id );

		return add_query_arg( 'group_post', $post_id, $url );
	}


	/**
	 * Gets post author
	 * @param  integer $post_id
	 * @return integer
	 */
	function get_author( $post_id ){
		$author = (int)get_post_meta( $post_id, '_user_id', TRUE );

		return ( $author ) ? $author : 0;
	}


	/**
	 * Gets post wall ID
	 * @param  integer $post_id
	 * @return integer
	 */
	function get_wall( $post_id ){
		$wall = (int)get_post_meta( $post_id, '_wall_id', TRUE );

		return ( $wall ) ? $wall : 0;
	}


	/**
	 * Get likes count
	 * @param  integer $post_id
	 * @return integer
	 */
	function get_likes_number( $post_id ) {
		return (int)get_post_meta( $post_id, '_likes', true );
	}


	/**
	 * Get comment count
	 * @param  integer $post_id
	 * @return integer
	 */
	function get_comments_number( $post_id ) {
		$comments_all = get_comments( array(
				'post_id' => $post_id,
				'parent' => 0,
				'count' => true
		) );

		return (int) $comments_all;
	}


	/**
	 * Make links clickable
	 * @param  string $content
	 * @return string
	 */
	function make_links_clickable( $content ){

		$has_iframe = preg_match( '/<iframe.*src=\"(.*)\".*><\/iframe>/isU', $content, $matches );

		if ($has_iframe) {
			$content = preg_replace( '/<iframe.*?\/iframe>/i', '[um_groups_iframe]', $content );
		}

		$content = preg_replace( '/(<a\b[^><]*)>/i', '$1 class="um-link" target="_blank">', make_clickable( $content ) );

		if ($has_iframe && isset( $matches[0] )) {
			$content = str_replace( '[um_groups_iframe]', $matches[0], $content );
		}

		return $content;

	}


	/**
	 * Removes Visual Composer's shortcodes
	 * @param  string $excerpt
	 * @return string
	 */
	function remove_vc_from_excerpt( $excerpt ){
		$patterns = "/\[[\/]?vc_[^\]]*\]/";
		$replacements = "";

		return preg_replace( $patterns, $replacements, $excerpt );
	}


	/**
	 * Check if enabled friends activity only
	 * @return boolean
	 */
	function friends_activity() {
		if ( class_exists( 'UM_Friends_API' ) && UM()->options()->get( 'groups_friends_users' ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Grab friends user IDs
	 * @return array or null
	 */
	function friends_ids(){
		$array = '';

		if (!$this->friends_activity())
			return NULL;

		if (!is_user_logged_in())
			return array( 0 );

		$array[] = get_current_user_id();

		$friends = UM()->Friends_API()->api()->friends( get_current_user_id() );
		if ($friends) {
			foreach ($friends as $k => $arr) {
				if ($arr['user_id1'] == get_current_user_id()) {
					$array[] = $arr['user_id2'];
				} else {
					$array[] = $arr['user_id1'];
				}
			}
		}

		if (isset( $array ))
			return $array;

		return NULL;
	}


	/**
	 * Load wall posts
	 */
	function ajax_load_wall() {
		global $um_group, $um_group_id;

		UM()->check_ajax_nonce();

		if( empty( $um_group_id ) ){
			$um_group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
			$um_group = get_post( $um_group_id );
		}

		$slug = UM()->Groups()->api()->get_privacy_slug( $um_group_id );

		if( in_array( $slug, array('private','hidden') ) && ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $um_group_id  ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		// Get arguments
		extract( array_merge( array(
				'offset'			 => 0,
				'user_id'			 => 0,
				'user_wall'		 => '',
				'hashtag'			 => '',
				'core_page'		 => '',
				'show_pending' => '',
		), $_POST ) );

		// Specific user only
		if ( $user_wall ) {

			$args = array(
					'user_id'			 => $user_id,
					'user_wall'		 => 1,
					'offset'			 => $offset,
					'core_page'		 => $core_page,
					'group_id'		 => $um_group_id,
					'show_pending' => $show_pending
			);

		// Global feed
		} else {

			$args = array(
					'user_id'			 => 0,
					'template'		 => 'activity',
					'mode'				 => 'activity',
					'form_id'			 => 'um_groups_id',
					'user_wall'		 => 0,
					'offset'			 => $offset,
					'core_page'		 => $core_page,
					'group_id'		 => $um_group_id,
					'show_pending' => $show_pending
			);

			if (isset( $hashtag ) && $hashtag) {

				$args['tax_query'] = array(
					array(
						'taxonomy' => 'um_hashtag',
						'field'    => 'slug',
						'terms'    => array( $hashtag )
					)
				);

				$args['hashtag'] = $hashtag;

			} else if ($this->followed_ids()) {

				$args['meta_query'][] = array( 'key' => '_user_id', 'value' => $this->followed_ids(), 'compare' => 'IN' );

			} else if ($this->friends_ids()) {

				$args['meta_query'][] = array( 'key' => '_user_id', 'value' => $this->friends_ids(), 'compare' => 'IN' );

			}

		}

		UM()->get_template( 'discussion/user-wall.php', um_groups_plugin, $args, true );

		die();
	}


	/**
	 * Get user suggestions
	 */
	function ajax_get_user_suggestions() {
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}
		if( !is_user_logged_in() ) {
			die();
		}
		if( !UM()->options()->get( 'groups_followers_mention' ) ) {
			die();
		}

		do_action( 'um_groups_ajax_get_user_suggestions' );
	}


	/**
	 * Removes a wall post
	 */
	function ajax_remove_post() {
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}
		if( !$group_id || $group_id <= 0 ) {
			die();
		}

		$author_id = $this->get_author( $post_id );

		if (current_user_can( 'edit_users' )) {
			wp_delete_post( $post_id, TRUE );
		} else if ($author_id == get_current_user_id() && is_user_logged_in()) {
			wp_delete_post( $post_id, TRUE );
		}

		die();
	}


	/**
	 * Removews a wall comment
	 */
	function ajax_remove_comment(){
		global $wpdb;

		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$comment_id = filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), absint( $group_id ) ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}
		if( !$comment_id || $comment_id <= 0 ) {
			die();
		}

		$comment = get_comment( $comment_id );

		if ($this->can_edit_comment( $comment_id, get_current_user_id() )) {
			// remove comment
			wp_delete_comment( $comment_id, TRUE );

			// remove hashtag(s) from the trending list if it's
			// totally remove from posts / comments
			$content = $comment->comment_content;
			$post_id = $comment->comment_post_ID;
			preg_match_all( '/(?<!\&)#([^\s\<]+)/', $content, $matches );
			if (isset( $matches[1] ) && is_array( $matches[1] )) {
				foreach ($matches[1] as $hashtag) {
					$post_count = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE ID = '{$post_id}' AND post_content LIKE '%>#{$hashtag}<%'" ) );
					$comment_count = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_post_ID = '{$post_id}' AND comment_content LIKE '%>#{$hashtag}<%'" ) );

					if (!$post_count && !$comment_count) {
						$term = get_term_by( 'name', $hashtag, 'um_hashtag' );
						wp_remove_object_terms( $post_id, $term->term_id, 'um_hashtag' );
					}
				}
			}
		}

		die();
	}


	/**
	 * Load post likes
	 */
	function ajax_get_post_likes() {
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$item_id = $post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ) {
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}
		if ( !$post_id || $post_id <= 0 ) {
			wp_send_json_error();
		}

		$users = get_post_meta( $item_id, '_liked', true );
		if ( ! $users || ! is_array( $users ) ) {
			wp_send_json_error();
			exit;
		} else {
			$users = array_unique( array_reverse( $users ) );
		}

		$t_args = compact( 'args', 'item_id', 'users' );
		$output = UM()->get_template( 'discussion/likes.php', um_groups_plugin, $t_args );

		die( $output );
	}


	/**
	 * Load comment likes
	 */
	function ajax_get_comment_likes(){
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$item_id = $comment_id = filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}
		if ( !$comment_id || $comment_id <= 0 ) {
			wp_send_json_error();
		}

		$users = get_comment_meta( $item_id, '_liked', TRUE );
		if ( ! $users || ! is_array( $users ) ) {
			wp_send_json_error();
			exit;
		} else {
			$users = array_unique( array_reverse( $users ) );
		}

		$t_args = compact( 'args', 'item_id', 'users' );
		$output = UM()->get_template( 'discussion/likes.php', um_groups_plugin, $t_args );

		die( $output );
	}


	/**
	 * Hide a comment
	 */
	function ajax_hide_comment(){
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$comment_id = filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}
		if ( !is_user_logged_in() ) {
			die();
		}
		if ( $comment_id <= 0 ) {
			die();
		}

		$this->user_hide_comment( $comment_id );
		die();
	}


	/**
	 * Unhide a comment
	 */
	function ajax_unhide_comment(){
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$comment_id = filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}
		if( !is_user_logged_in() ) {
			die();
		}
		if( $comment_id <= 0 ) {
			die();
		}

		$this->user_unhide_comment( $comment_id );
		die();
	}


	/**
	 * Report a post
	 */
	function ajax_report_post() {
		UM()->check_ajax_nonce();

		$post_id = filter_input( INPUT_POST, 'postid', FILTER_SANITIZE_NUMBER_INT );
		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		if ( !is_user_logged_in() ) {
			die();
		}
		if ( $post_id <= 0 ) {
			die();
		}

		$users_reported = get_post_meta( $post_id, '_reported_by', TRUE );
		$users_reported[get_current_user_id()] = current_time( 'timestamp' );
		update_post_meta( $post_id, '_reported_by', $users_reported );

		if (!get_post_meta( $post_id, '_reported', TRUE )) {
			$count = (int)get_option( 'um_groups_flagged' );
			update_option( 'um_groups_flagged', $count + 1 );
		}

		$new_r = (int)get_post_meta( $post_id, '_reported', TRUE );
		update_post_meta( $post_id, '_reported', $new_r + 1 );

		die();
	}


	/**
	 * Un-report a post
	 */
	function ajax_unreport_post() {
		UM()->check_ajax_nonce();

		$post_id = filter_input( INPUT_POST, 'postid', FILTER_SANITIZE_NUMBER_INT );
		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		if ( !is_user_logged_in() ) {
			die();
		}
		if ( $post_id <= 0 ) {
			die();
		}

		$users_reported = get_post_meta( $post_id, '_reported_by', TRUE );
		if (isset( $users_reported[get_current_user_id()] )) {
			unset( $users_reported[get_current_user_id()] );
		}
		if (!$users_reported) {
			$user_reported = '';
		}
		update_post_meta( $post_id, '_reported_by', $users_reported );

		if (get_post_meta( $post_id, '_reported', TRUE )) {

			$new_r = (int)get_post_meta( $post_id, '_reported', TRUE );
			$new_r = $new_r - 1;
			if ($new_r < 0) $new_r = 0;
			update_post_meta( $post_id, '_reported', $new_r );

			if ($new_r == 0) {
				$count = (int)get_option( 'um_groups_flagged' );
				update_option( 'um_groups_flagged', absint( $count - 1 ) );
			}
		}

		die();
	}


	/**
	 * Load wall comments
	 */
	function ajax_load_more_comments() {
		UM()->check_ajax_nonce();

		$offset = filter_input( INPUT_POST, 'offset', FILTER_SANITIZE_NUMBER_INT );
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		$comments_all = $this->get_comments_number( $post_id );

		ob_start();

		if( $comments_all > 0 ){
			$number = UM()->options()->get( 'groups_load_comments_count' );
			$comments = get_comments( array(
					'post_id' => $post_id,
					'parent' => 0,
					'number' => $number,
					'offset' => $offset,
					'order' => UM()->options()->get( 'groups_order_comment' )
			) );

			$t_args = compact( 'comments', 'likes', 'user_hidden' );
			UM()->get_template( 'discussion/comment.php', um_groups_plugin, $t_args, true );

			// Load more replies
			$comments_count = $offset + count( $comments );
			$this->the_comment_loadmore( $comments_all, $comments_count, 'comment' );
		}

		$html = ob_get_clean();

		wp_die( um_compress_html( $html ) );
		exit;
	}


	/**
	 * Load wall replies
	 */
	function ajax_load_more_replies() {
		UM()->check_ajax_nonce();

		$offset = filter_input( INPUT_POST, 'offset', FILTER_SANITIZE_NUMBER_INT );
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$comment_id = filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		$child_all = get_comments( array(
				'post_id' => $post_id,
				'parent' => $comment_id,
				'count' => true
		) );

		ob_start();

		if( $child_all > 0 ){
			$number = UM()->options()->get( 'groups_load_comments_count' );
			$child = get_comments( array(
					'post_id' => $post_id,
					'parent' => $comment_id,
					'number' => $number,
					'offset' => $offset,
					'order' => UM()->options()->get( 'groups_order_comment' )
			) );

			foreach ( $child as $commentc ) {
				$avatar = get_avatar( $commentc->comment_author_email, 80 );
				$likes = get_comment_meta( $commentc->comment_ID, '_likes', TRUE );
				$user_hidden = $this->user_hidden_comment( $commentc->comment_ID );

				$t_args = compact( 'avatar', 'commentc', 'likes', 'user_hidden' );
				UM()->get_template( 'discussion/comment-reply.php', um_groups_plugin, $t_args, true );
			}

			// Load more replies
			$child_count = $offset + count( $child );
			$this->the_comment_loadmore( $child_all, $child_count, 'reply' );
		}

		$html = ob_get_clean();

		die( um_compress_html( $html ) );
		exit;
	}


	/**
	 * Like wall comment
	 * @return json object
	 */
	function ajax_like_comment() {
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$comment_id = filter_input( INPUT_POST, 'commentid', FILTER_SANITIZE_NUMBER_INT );

		if ( !UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ) {
			return wp_send_json( array( 'restricted' => __( "You do not have the necessary permission for the specified Group to perform this action.", 'um-groups' ) ) );
		}

		$output[ 'error' ] = '';
		if ( !is_user_logged_in() ) {
			$output[ 'error' ] = __( 'You must login to like', 'um-groups' );
		}
		if ( !$comment_id || !is_numeric( $comment_id ) ) {
			$output[ 'error' ] = __( 'Invalid comment', 'um-groups' );
		}

		if ( !$output[ 'error' ] ) {

			$likes = (int) get_comment_meta( $comment_id, '_likes', TRUE );
			update_comment_meta( $comment_id, '_likes', $likes + 1 );

			$liked = get_comment_meta( $comment_id, '_liked', TRUE );
			if ( !$liked ) {
				$liked = array( get_current_user_id() );
			}
			else {
				$liked[] = get_current_user_id();
			}
			update_comment_meta( $comment_id, '_liked', $liked );

			UM()->Groups()->api()->set_group_last_activity( $group_id );
		}

		return wp_send_json( $output );
	}


	/**
	 * Unlike wall comment
	 * @return json object
	 */
	function ajax_unlike_comment() {
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$comment_id = filter_input( INPUT_POST, 'commentid', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		$output['error'] = '';
		if ( !is_user_logged_in() ) {
			$output[ 'error' ] = __( 'You must login to unlike', 'um-groups' );
		}
		if ( !$comment_id || !is_numeric( $comment_id ) ) {
			$output[ 'error' ] = __( 'Invalid comment', 'um-groups' );
		}

		if ( ! $output['error'] ) {

			$likes = get_comment_meta( $comment_id, '_likes', TRUE );
			update_comment_meta( $comment_id, '_likes', $likes - 1 );

			$liked = get_comment_meta( $comment_id, '_liked', TRUE );
			if ( $liked ) {
				$liked = array_diff( $liked, array( get_current_user_id() ) );
			}
			update_comment_meta( $comment_id, '_liked', $liked );

		}

		return wp_send_json( $output );
	}


	/**
	 * Like a wall post
	 * @return json object
	 */
	function ajax_like_post() {
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$post_id = filter_input( INPUT_POST, 'postid', FILTER_SANITIZE_NUMBER_INT );

		if ( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ) {
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		$output['error'] = '';
		if ( !is_user_logged_in() ) {
			$output[ 'error' ] = __( 'You must login to like', 'um-groups' );
		}
		if ( !$post_id || !is_numeric( $post_id ) ) {
			$output[ 'error' ] = __( 'Invalid wall post', 'um-groups' );
		}

		if ( ! $output['error'] ) {

			$likes = get_post_meta( $post_id, '_likes', TRUE );
			update_post_meta( $post_id, '_likes', $likes + 1 );

			$liked = get_post_meta( $post_id, '_liked', TRUE );
			if (!$liked) {
				$liked = array( get_current_user_id() );
			} else {
				$liked[] = get_current_user_id();
			}
			update_post_meta( $post_id, '_liked', $liked );

			do_action( 'um_groups_after_wall_post_liked', $post_id, get_current_user_id() );

			UM()->Groups()->api()->set_group_last_activity( $group_id );
		}

		return wp_send_json( $output );
	}


	/**
	 * Unlike wall post
	 * @return json object
	 */
	function ajax_unlike_post() {
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$post_id = filter_input( INPUT_POST, 'postid', FILTER_SANITIZE_NUMBER_INT );

		if ( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ) {
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		$output['error'] = '';
		if( !is_user_logged_in() ) {
			$output[ 'error' ] = __( 'You must login to unlike', 'um-groups' );
		}
		if( !$post_id || !is_numeric( $post_id ) ) {
			$output[ 'error' ] = __( 'Invalid wall post', 'um-groups' );
		}

		if ( ! $output['error'] ) {

			$likes = get_post_meta( $post_id, '_likes', TRUE );
			update_post_meta( $post_id, '_likes', $likes - 1 );

			$liked = get_post_meta( $post_id, '_liked', TRUE );
			if ( $liked ) {
				$liked = array_diff( $liked, array( get_current_user_id() ) );
			}
			update_post_meta( $post_id, '_liked', $liked );

		}

		return wp_send_json( $output );
	}


	/**
	 * Add a new wall post comment
	 * @return json object
	 */
	function ajax_wall_comment() {
		UM()->check_ajax_nonce();

		$time = current_time( 'mysql' );
		$post_id = filter_input( INPUT_POST, 'postid', FILTER_SANITIZE_NUMBER_INT );
		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$commentid = filter_input( INPUT_POST, 'commentid', FILTER_SANITIZE_NUMBER_INT );
		$comment_parent = filter_input( INPUT_POST, 'reply_to', FILTER_SANITIZE_NUMBER_INT );
		$comment = filter_input( INPUT_POST, 'comment', FILTER_SANITIZE_STRING );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id  ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		$output['error'] = '';

		if ( !is_user_logged_in() ) {
			$output[ 'error' ] = __( 'Login to post a comment', 'um-groups' );
		}
		if ( !$post_id || !is_numeric( $post_id ) ) {
			$output[ 'error' ] = __( 'Invalid wall post', 'um-groups' );
		}
		if ( !$comment || trim( $comment ) == '' ) {
			$output[ 'error' ] = __( 'Enter a comment first', 'um-groups' );
		}

		if ( ! $output['error'] ) {

			um_fetch_user( get_current_user_id() );

			$comment_content = wp_kses( $comment, array( '' ) );
			$comment_content = apply_filters( 'um_groups_comment_content_new', $comment_content, $post_id );
			// apply hashtag
			$this->hashtagit( $post_id, $comment_content );

			$comment_content = $this->hashtag_links( $comment_content );
			$comment_content = apply_filters( 'um_groups_insert_post_content_filter', $comment_content, get_current_user_id(), absint( $post_id ), 'new' );

			um_fetch_user( get_current_user_id() );

			$data = array(
				'comment_post_ID'      => $post_id,
				'comment_author'       => um_user( 'display_name' ),
				'comment_author_email' => um_user( 'user_email' ),
				'comment_author_url'   => um_user_profile_url(),
				'comment_content'      => $comment_content,
				'user_id'              => get_current_user_id(),
				'comment_date'         => $time,
				'comment_approved'     => 1,
				'comment_author_IP'    => um_user_ip(),
				'comment_type'         => 'um-social-activity'
			);

			$comment_content = $this->make_links_clickable( $comment_content );
			$output['comment_content'] = stripslashes_deep( $comment_content );

			if ( $comment_parent ) {
				$data['comment_parent'] = $comment_parent;
				do_action( 'um_groups_after_wall_comment_reply_published', $commentid, $comment_parent, absint( $post_id ), get_current_user_id() );
			}

			if ( $commentid ) {
				$data['comment_ID'] = $commentid;
				wp_update_comment( $data );
			} else {
				$commentid = wp_insert_comment( $data );
			}

			$comment_count = get_post_meta( $post_id, '_comments', TRUE );
			update_post_meta( $post_id, '_comments', $comment_count + 1 );

			$output['commentid'] = $commentid;

			UM()->Groups()->api()->set_group_last_activity( $group_id );

			do_action( 'um_groups_after_wall_comment_published', $commentid, $comment_parent, $post_id, get_current_user_id() );
		}

		return wp_send_json( $output );
	}


	/**
	 * Add new wall post
	 * @return json object
	 */
	function ajax_activity_publish() {
		UM()->check_ajax_nonce();

		$group_id = filter_input( INPUT_POST, '_group_id', FILTER_SANITIZE_NUMBER_INT );
		$post_id = filter_input( INPUT_POST, '_post_id', FILTER_SANITIZE_NUMBER_INT );
		$wall_id = filter_input( INPUT_POST, '_wall_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( get_current_user_id(), $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}

		$output['error'] = '';
		if( !is_user_logged_in() ) {
			$output[ 'error' ] = __( 'You can not post as guest', 'um-groups' );
		}

		extract( $_POST );
		if ( $_post_content == '' || trim( $_post_content ) == '') {
			if ( trim( $_post_img ) == '') {
				$output['error'] = __( 'You should type something first', 'um-groups' );
			}
		}

		if ( ! $output['error'] ) {

			$has_oEmbed = FALSE;

			if ( $post_id == 0 ) {

				$args = array(
					'post_title'  => '',
					'post_type'   => 'um_groups_discussion',
					'post_status' => 'publish',
					'post_author' => get_current_user_id(),
				);

				if ( trim( $_post_content ) ) {
					$orig_content = trim( $_post_content );
					$safe_content = wp_kses( $_post_content, array(
						'br' => array()
					) );

					// shared a link
					$shared_link = $this->get_content_link( $safe_content );
					$has_oEmbed = $this->is_oEmbed( $shared_link );

					if (isset( $shared_link ) && $shared_link && !$_post_img && !$has_oEmbed) {
						$safe_content = str_replace( $shared_link, '', $safe_content );
						$output['_shared_link'] = $shared_link;
					}

					$args['post_content'] = $safe_content;
				}

				$args = apply_filters( 'um_groups_insert_post_args', $args );

				$post_id = wp_insert_post( $args );

				UM()->Groups()->api()->set_group_last_activity( $group_id );

				// shared a link
				if (isset( $shared_link ) && $shared_link && !$_post_img && !$has_oEmbed) {
					$output['link'] = $this->set_url_meta( $shared_link, $post_id );
				} else {
					delete_post_meta( $post_id, '_shared_link' );
				}

				$args['post_content'] = apply_filters( 'um_groups_insert_post_content_filter', $args['post_content'], get_current_user_id(), $post_id, 'new' );

				wp_update_post( array( 'ID' => $post_id, 'post_title' => $post_id, 'post_name' => $post_id, 'post_content' => $args['post_content'] ) );

				if (isset( $safe_content )) {
					$this->hashtagit( $post_id, $safe_content );
					update_post_meta( $post_id, '_original_content', $orig_content );
					$output['orig_content'] = stripslashes_deep( $orig_content );
				}

				if ($wall_id > 0) {
					update_post_meta( $post_id, '_wall_id', $wall_id );
				}

				// Save item meta
				update_post_meta( $post_id, '_oembed', $has_oEmbed );
				update_post_meta( $post_id, '_action', 'status' );
				update_post_meta( $post_id, '_user_id', get_current_user_id() );
				update_post_meta( $post_id, '_likes', 0 );
				update_post_meta( $post_id, '_comments', 0 );
				update_post_meta( $post_id, '_group_id', $group_id );

				$group_moderation = get_post_meta( $group_id,  '_um_groups_posts_moderation', true );

				// Administrators/Moderators posts are automatically approved
				if( UM()->Groups()->api()->can_moderate_posts( $group_id ) ){
					update_post_meta( $post_id, '_group_moderation', 'approved' );
				}else{
					// Members
					if( 'auto-published' == $group_moderation ){
						update_post_meta( $post_id, '_group_moderation', 'approved' );
					}else{
						update_post_meta( $post_id, '_group_moderation', 'pending_review' );
						$output['pending_review'] = true;
					}
				}

				if ( $_post_img ) {

					$photo_uri = um_is_file_owner( $_post_img, get_current_user_id() ) ? $_post_img : false;

					update_post_meta( $post_id, '_photo', $photo_uri );

					UM()->uploader()->replace_upload_dir = true;
					UM()->uploader()->move_temporary_files( get_current_user_id(), array( '_photo' => $photo_uri ), true );
					UM()->uploader()->replace_upload_dir = false;

					$photo_uri = wp_basename( $photo_uri );

					$output['photo'] = UM()->uploader()->get_upload_user_base_url( get_current_user_id() ) ."/{$photo_uri}";
					$output['photo_base'] = $photo_uri;

				}


				$output['postid'] = $post_id;
				$output['content'] = $this->get_content( $post_id );
				$output['video'] = $this->get_video( $post_id );

				do_action( 'um_groups_after_wall_post_published', $post_id, get_current_user_id(), $wall_id );

			} else {

				// Updating a current wall post
				if (trim( $_post_content )) {
					$orig_content = trim( $_post_content );
					$safe_content = wp_kses( $_post_content, array(
						'br' => array()
					) );

					// shared a link
					$shared_link = $this->get_content_link( $safe_content );
					$has_oEmbed = $this->is_oEmbed( $shared_link );

					if (isset( $shared_link ) && $shared_link && !$_post_img && !$has_oEmbed) {
						$safe_content = str_replace( $shared_link, '', $safe_content );
						$output['link'] = $this->set_url_meta( $shared_link, $post_id );
					} else {
						delete_post_meta( $post_id, '_shared_link' );
					}


					$safe_content = apply_filters( 'um_groups_update_post_content_filter', $safe_content, $this->get_author( $post_id ), $post_id, 'save' );

					$args['post_content'] = $safe_content;
				}

				$args['ID'] = $post_id;
				$args = apply_filters( 'um_groups_update_post_args', $args );

				// hash tag replies
				$args['post_content'] = apply_filters( 'um_groups_insert_post_content_filter', $args['post_content'], get_current_user_id(), $post_id, 'new' );

				wp_update_post( $args );

				if (isset( $safe_content )) {
					$this->hashtagit( $post_id, $safe_content );
					update_post_meta( $post_id, '_original_content', $orig_content );
					$output['orig_content'] = stripslashes_deep( $orig_content );
				}


				if ( trim( $_post_img ) != '' ) {

					$photo_uri = um_is_file_owner( $_post_img, get_current_user_id() ) ? $_post_img : false;

					UM()->uploader()->replace_upload_dir = true;
					UM()->uploader()->move_temporary_files( get_current_user_id(), array( '_photo' => $photo_uri ), true );
					UM()->uploader()->replace_upload_dir = false;

					update_post_meta( $post_id, '_photo', $photo_uri );

					$photo_uri = wp_basename( $photo_uri );

					$output['photo'] = UM()->uploader()->get_upload_user_base_url( get_current_user_id() ) ."/{$photo_uri}";
					$output['photo_base'] = wp_basename( $output['photo'] );

				} else {

					$photo_uri = get_post_meta( $post_id, '_photo', true );

					UM()->uploader()->replace_upload_dir = true;
					UM()->uploader()->get_upload_user_base_dir( get_current_user_id() ) ."/{$photo_uri}";
					UM()->uploader()->delete_existing_file( $photo_uri );
					UM()->uploader()->replace_upload_dir = false;

					delete_post_meta( $post_id, '_photo' );

				}

				$output['postid'] = $post_id;
				$output['content'] = $this->get_content( $post_id );
				$output['video'] = $this->get_video( $post_id );

				do_action( 'um_groups_after_wall_post_updated', $post_id, get_current_user_id(), $wall_id );

			}

			// other output
			$output['permalink'] = add_query_arg( 'group_post', $post_id, get_permalink( $group_id ) );

		}


		return wp_send_json( $output );
	}


	/**
	 * Approve discussion post
	 * @return json object
	 */
	function ajax_approve_discussion_post() {
		UM()->check_ajax_nonce();

		$output = array();

		$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$user_id = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );

		if( ! UM()->Groups()->api()->has_joined_group( $user_id , $group_id ) || empty( $group_id ) ){
			return wp_send_json( array('restricted' => __("You do not have the necessary permission for the specified Group to perform this action.",'um-groups' ) ) );
		}


		$author_id = $this->get_author( $post_id );

		$action = filter_input( INPUT_POST, 'action' );
		$role = filter_input( INPUT_POST, 'role' );
		if( $role ){
			$action = $role;
		}

		switch( $action ){

			case 'approve':
				wp_update_post(
					array( 'ID' => $post_id, 'post_date' => current_time('mysql') )
				);

				update_post_meta( $post_id, '_group_moderation', 'approved' );

				UM()->Groups()->api()->set_group_last_activity( $group_id );

				return wp_send_json( array('status' => 'approved', 'message' => __("Post has been approved","um-groups") ) );

			case 'delete':
				if ( current_user_can( 'edit_users' ) ) {
					wp_delete_post( $post_id, TRUE );
				} else if ( $author_id == $user_id && is_user_logged_in()) {
					wp_delete_post( $post_id, TRUE );
				}

				return wp_send_json( array('status' => 'deleted', 'message' => __("Post has been deleted","um-groups") ) );

			case 'report':
				# code...
				break;

		}

		return wp_send_json( $output );
	}


	/**
	 * Get pending reviews count
	 * @param  integer $user_id
	 * @param  integer $group_id
	 * @return integer
	 */
	function get_pending_reviews_count( $user_id, $group_id ){

		if( UM()->Groups()->api()->can_moderate_posts( $group_id ) ){

			$args_pending_reviews = array(
				'post_type' => 'um_groups_discussion',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_group_id',
						'value' => $group_id,
						'compare' => '='
					),
					array(
						'key' => '_group_moderation',
						'value' => 'pending_review',
						'compare' => '='
					)
				)
			);

		}else{

			$args_pending_reviews = array(
				'post_type' => 'um_groups_discussion',
				'author'	=> $user_id,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_group_id',
						'value' => $group_id,
						'compare' => '='
					),
					array(
						'key' => '_group_moderation',
						'value' => 'pending_review',
						'compare' => '='
					),
					array(
						'key' => '_user_id',
						'value' 	=> $user_id,
						'compare' 	=> '='
					)
				)
			);

		}

		$pending_reviews = new \WP_Query( $args_pending_reviews );

		return $pending_reviews->found_posts;
	}


	/**
	 * Has group discussions
	 * @return boolean
	 */
	function has_group_discussions( $group_id = null ){

		$args_group_posts = array(
			'post_type' => 'um_groups_discussion',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_group_id',
					'value' => $group_id,
					'compare' => '='
				),
				array(
					'key' => '_group_moderation',
					'value' => 'approved',
					'compare' => '='
				)
			)
		);

		$groups_discussion = new \WP_Query( $args_group_posts );

		return $groups_discussion->found_posts;
	}


	/**
	 * Get template "Child comments"
	 * @param WP_Comment $comment
	 */
	function the_comment_child( $comment = null ) {

		if( empty( $comment ) ) {
			return;
		}

		$child_all = get_comments( array(
				'post_id'	 => $comment->comment_post_ID,
				'parent'	 => $comment->comment_ID,
				'count'		 => true
				) );

		if( $child_all > 0 ):
			$comm_num = !empty( $_GET[ 'wall_comment_id' ] ) ? 10000 : UM()->options()->get( 'groups_init_comments_count' );
			$child = get_comments( array(
					'post_id'	 => $comment->comment_post_ID,
					'parent'	 => $comment->comment_ID,
					'number'	 => $comm_num,
					'offset'	 => 0,
					'order'		 => UM()->options()->get( 'groups_order_comment' )
					) );

			echo '<div class="um-groups-comment-child">';

			foreach( $child as $commentc ) {
				$avatar = get_avatar( $commentc->user_id, 80 );
				$likes = get_comment_meta( $commentc->comment_ID, '_likes', true );
				$user_hidden = UM()->Groups()->discussion()->user_hidden_comment( $commentc->comment_ID );

				$t_args = compact( 'avatar', 'commentc', 'likes', 'user_hidden' );
				UM()->get_template( 'discussion/comment-reply.php', um_groups_plugin, $t_args, true );
			}

			// Load more replies
			$child_count = 0 + count( $child );
			$this->the_comment_loadmore( $child_all, $child_count, 'reply' );

			echo '</div>';
		endif;
	}


	/**
	 * Get template "Load more replies"
	 * @param int $comments_all
	 * @param int $comments_count
	 * @param string $type
	 */
	function the_comment_loadmore( $comments_all, $comments_count, $type = 'comment' ) {
		if( $comments_all > $comments_count ) {
			$calc = min( $comments_all - $comments_count, UM()->options()->get( 'groups_load_comments_count' ) );

			switch( $type ) {
				case 'comment':
					if( $calc > 1 ) {
						$text = sprintf( __( 'load %s more comments', 'um-groups' ), $calc );
					} else if( $calc == 1 ) {
						$text = sprintf( __( 'load %s more comment', 'um-groups' ), $calc );
					}
					echo '<a href="#" class="um-groups-commentload" data-load_replies="' . __( 'load more replies', 'um-groups' ) . '" data-load_comments="' . __( 'load more comments', 'um-groups' ) . '" data-loaded="' . $comments_count . '"><i class="um-icon-forward"></i><span>' . $text . '</span></a>';
					echo '<div class="um-groups-commentload-spin"></div>';
					break;

				case 'reply':
				default:
					if( $calc > 1 ) {
						$text = sprintf( __( 'load %s more replies', 'um-groups' ), $calc );
					} else if( $calc == 1 ) {
						$text = sprintf( __( 'load %s more reply', 'um-groups' ), $calc );
					}
					echo '<a href="#" class="um-groups-ccommentload" data-load_replies="' . __( 'load more replies', 'um-groups' ) . '" data-load_comments="' . __( 'load more comments', 'um-groups' ) . '" data-loaded="' . $comments_count . '"><i class="um-icon-forward"></i><span>' . $text . '</span></a>';
					echo '<div class="um-groups-commentload-spin"></div>';
					break;
			}
		}
	}

}