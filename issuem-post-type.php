<?php

/**
 * Registers IssueM Article Post Type w/ Meta Boxes
 *
 * @package IssueM
 * @since 1.0.0
 */

if ( ! function_exists( 'create_article_post_type' ) ) {

	/**
	 * Registers Article Post type for IssueM
	 *
	 * @since 1.0.0
	 */
	function create_article_post_type() {
		$issuem_settings = get_issuem_settings();

		if ( ! empty( $issuem_settings['use_wp_taxonomies'] ) ) {
			$taxonomies = array( 'category', 'post_tag' );
		} else {
			$taxonomies = array( 'issuem_issue_categories', 'issuem_issue_tags' );
		}

		$labels = array(
			'name'               => __( 'Articles', 'issuem' ),
			'singular_name'      => __( 'Article', 'issuem' ),
			'add_new'            => __( 'Add New Article', 'issuem' ),
			'add_new_item'       => __( 'Add New Article', 'issuem' ),
			'edit_item'          => __( 'Edit Article', 'issuem' ),
			'new_item'           => __( 'New Article', 'issuem' ),
			'view_item'          => __( 'View Article', 'issuem' ),
			'search_items'       => __( 'Search Articles', 'issuem' ),
			'not_found'          => __( 'No articles found', 'issuem' ),
			'not_found_in_trash' => __( 'No articles found in trash', 'issuem' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Articles', 'issuem' ),
		);

		$args = array(
			'label'                => 'article',
			'labels'               => $labels,
			'description'          => __( 'IssueM Articles', 'issuem' ),
			'public'               => true,
			'publicly_queryable'   => true,
			'exclude_fromsearch'   => false,
			'show_ui'              => true,
			'show_in_menu'         => true,
			'capability_type'      => array( 'article', 'articles' ),
			'map_meta_cap'         => true,
			'hierarchical'         => false,
			'supports'             => array(
				'title',
				'author',
				'editor',
				'custom-fields',
				'revisions',
				'thumbnail',
				'excerpt',
				'trackbacks',
				'comments',
				'page-attributes',
				'post-formats',
			),
			'register_meta_box_cb' => 'add_issuem_articles_metaboxes',
			'has_archive'          => true,
			'rewrite'              => array( 'slug' => 'article' ),
			'show_in_rest'         => true,
			'taxonomies'           => $taxonomies,
			'menu_icon'            => ISSUEM_URL . '/images/oie_P7fhR5VjrKGg1.png',
		);

		register_post_type( 'article', apply_filters( 'issuem_article_post_type_args', $args ) );
	}
	add_action( 'init', 'create_article_post_type' );
}

if ( ! function_exists( 'issuem_article_add_post_thumbnails' ) ) {

	function issuem_article_add_post_thumbnails() {
		$supported_post_types = get_theme_support( 'post-thumbnails' );

		if ( false === $supported_post_types ) {

			$post_types = array( 'article' );
			add_theme_support( 'post-thumbnails', $post_types );
		} elseif ( is_array( $supported_post_types ) ) {

			$post_types   = $supported_post_types[0];
			$post_types[] = 'article';
			add_theme_support( 'post-thumbnails', $post_types );
		}
	}
	add_action( 'after_setup_theme', 'issuem_article_add_post_thumbnails', 99 );
}

if ( ! function_exists( 'add_issuem_articles_metaboxes' ) ) {

	/**
	 * Registers metaboxes for IssueM Articles
	 *
	 * @since 1.0.0
	 */
	function add_issuem_articles_metaboxes() {
		add_meta_box( 'issuem_article_meta_box', __( 'IssueM Article Options', 'issuem' ), 'issuem_article_meta_box', 'article', 'normal', 'high' );

		do_action( 'add_issuem_articles_metaboxes' );
	}
}

if ( ! function_exists( 'issuem_article_meta_box' ) ) {

	/**
	 * Outputs Article HTML for options metabox
	 *
	 * @since 1.0.0
	 *
	 * @param object $post WordPress post object
	 */
	function issuem_article_meta_box( $post ) {

		$issuem_settings = get_issuem_settings();

		$teaser_text        = get_post_meta( $post->ID, '_teaser_text', true );
		$featured_rotator   = get_post_meta( $post->ID, '_featured_rotator', true );
		$featured_thumb     = get_post_meta( $post->ID, '_featured_thumb', true );
		$issuem_author_name = get_post_meta( $post->ID, '_issuem_author_name', true );

		wp_nonce_field( 'issuem_article_meta_box_submit', 'issuem_article_meta_box_submit_field' );

		?>

		<div id="issuem-article-metabox">

			<p><input id="featured_rotator" type="checkbox" name="featured_rotator" <?php checked( 'on', $featured_rotator ); ?> />
				<label for="featured_rotator"><?php esc_html_e( 'Add article to Featured Rotator', 'issuem' ); ?></label>
			</p>


			<p><input id="featured_thumb" type="checkbox" name="featured_thumb" <?php checked( 'on', $featured_thumb ); ?> /><label for="featured_thumb"><?php esc_html_e( 'Add article to Featured Thumbnails', 'issuem' ); ?></label></p>

			<p>
				<label for="teaser_text"><strong><?php esc_html_e( 'Teaser Text', 'issuem' ); ?></strong></label><br>

				<input class="large-text" type="text" name="teaser_text" value="<?php echo esc_attr( $teaser_text ); ?>" />
			</p>

			<?php if ( ! empty( $issuem_settings['issuem_author_name'] ) ) { ?>
				<p><label for="featured_thumb"><strong><?php esc_html_e( 'IssueM Author Name', 'issuem' ); ?></strong></label><br>
					<input class="regular-text" type="text" name="issuem_author_name" value="<?php echo esc_attr( $issuem_author_name ); ?>" />
				</p>
			<?php } ?>






		</div>

		<?php
	}
}


/**
 * Saves Article meta
 *
 * @since 1.0.0
 *
 * @param int $post_id WordPress post ID
 */
function save_issuem_article_meta( $post_id ) {

	// verify if this is an auto save routine.
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// if our current user can't edit this post, bail
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	// if our nonce isn't there, or we can't verify it, bail
	if ( ! isset( $_POST['issuem_article_meta_box_submit_field'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['issuem_article_meta_box_submit_field'] ), 'issuem_article_meta_box_submit' ) ) {
		return;
	}


	if ( isset( $_POST['teaser_text'] ) ) {
		update_post_meta( $post_id, '_teaser_text', sanitize_text_field( $_POST['teaser_text'] ) );
	}

	if ( isset( $_POST['featured_rotator'] ) ) {
		update_post_meta( $post_id, '_featured_rotator', 'on' );
	} else {
		update_post_meta( $post_id, '_featured_rotator', 'off' );
	}

	if ( isset( $_POST['featured_thumb'] ) ) {
		update_post_meta( $post_id, '_featured_thumb', 'on' );
	} else {
		update_post_meta( $post_id, '_featured_thumb', 'off' );
	}

	if ( isset( $_POST['issuem_author_name'] ) ) {
		update_post_meta( $post_id, '_issuem_author_name', sanitize_text_field( $_POST['issuem_author_name'] ) );
	}


	do_action( 'save_issuem_article_meta', $post_id );
}
add_action( 'save_post', 'save_issuem_article_meta' );


add_filter( 'manage_edit-article_columns', 'issuem_article_admin_columns' );
add_action( 'manage_article_posts_custom_column', 'issuem_article_admin_custom_columns', 10, 2 );

function issuem_article_admin_columns( $columns ) {

	$columns['issue']    = __( 'Issue' );
	$columns['category'] = __( 'Categories' );
	$columns['tag']      = __( 'Tags' );

	return $columns;
}

function issuem_article_admin_custom_columns( $column, $post_id ) {

	$issuem_settings = get_issuem_settings();

	if ( ! empty( $issuem_settings['use_wp_taxonomies'] ) ) {
		$tag_type = 'post_tag';
		$cat_type = 'category';
	} else {
		$tag_type = 'issuem_issue_tags';
		$cat_type = 'issuem_issue_categories';
	}

	$cats   = get_the_terms( $post_id, $cat_type );
	$tags   = get_the_terms( $post_id, $tag_type );
	$issues = get_the_terms( $post_id, 'issuem_issue' );

	$cat_list   = '';
	$tag_list   = '';
	$issue_list = '';

	if ( ! empty( $cats ) ) {
		foreach ( $cats as $cat ) {
			$cat_list .= '<a href="' . get_term_link( $cat->term_id ) . '">' . $cat->name . '</a>, ';
		}
	}

	if ( ! empty( $tags ) ) {
		foreach ( $tags as $tag ) {
			$tag_list .= '<a href="' . get_term_link( $tag->term_id ) . '">' . $tag->name . '</a>, ';
		}
	}

	if ( ! empty( $issues ) ) {
		foreach ( $issues as $issue ) {
			$link        = admin_url() . 'edit.php?post_type=article&issuem_issue=' . $issue->slug;
			$issue_list .= '<a href="' . $link . '">' . $issue->name . '</a>, ';
		}
	}



	switch ( $column ) {

		case 'issue':
			echo wp_kses_post( substr_replace( $issue_list, '', -2 ) );
			break;

		case 'category':
			echo wp_kses_post( substr_replace( $cat_list, '', -2 ) );
			break;

		case 'tag':
			echo wp_kses_post( substr_replace( $tag_list, '', -2 ) );
			break;
	}
}
