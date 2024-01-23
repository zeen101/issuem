<?php

/**
 * Registers IssueM class for setting up IssueM
 *
 * @package IssueM
 * @since 1.0.0
 */


/**
 * This class registers the main issuem functionality
 *
 * @since 1.0.0
 */
class IssueM {

	/**
	 * Class constructor, puts things in motion
	 *
	 * @since 1.0.0
	 *
	 * @todo Move the the_author filter to a more appopriate place
	 * @todo Move the pre_get_posts filter to a more appopriate place
	 */
	public function __construct() {
		$settings = $this->get_settings();

		add_image_size( 'issuem-cover-image', apply_filters( 'issuem_cover_image_width', $settings['cover_image_width'] ), apply_filters( 'issuem_cover_image_height', $settings['cover_image_height'] ), true );
		add_image_size( 'issuem-featured-rotator-image', apply_filters( 'issuem_featured_rotator_image_width', $settings['featured_image_width'] ), apply_filters( 'issuem_featured_rotator_image_height', $settings['featured_image_height'] ), true );
		add_image_size( 'issuem-featured-thumb-image', apply_filters( 'issuem_featured_thumb_image_width', $settings['featured_thumb_width'] ), apply_filters( 'issuem_featured_thumb_image_height', $settings['featured_thumb_height'] ), true );

		add_action( 'admin_init', array( $this, 'upgrade' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_notices', array( $this, 'issuem_notification' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_wp_enqueue_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_wp_print_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		add_filter( 'views_edit-article', array( $this, 'display_zeen101_dot_com_rss_item' ) );
		add_filter( 'plugin_action_links_' . ISSUEM_BASENAME, array( $this, 'issuem_settings_link' ) );

		add_action( 'wp_ajax_issuem_process_notice_link', array( $this, 'ajax_process_notice_link' ) );



		if ( ! empty( $settings['issuem_author_name'] ) && ! is_admin() ) {
			add_filter( 'the_author', array( $this, 'the_author' ) );
		}

		if ( ! empty( $settings['use_wp_taxonomies'] ) ) {
			add_action( 'pre_get_posts', array( $this, 'add_issuem_articles_to_tag_query' ) );
		}

		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'remove_draft_issues_from_main_query' ) );
		}
	}

	/**
	 * Runs activation routines when IssueM is activated
	 *
	 * @since 1.0.0
	 *
	 */
	public function activation() {
		create_article_post_type();
		flush_rewrite_rules();
	}

	/**
	 * Runs deactivation routines if IssueM is deactivated
	 *
	 * @since 1.1.1
	 *
	 */
	public function deactivation() {
		// Clear the IssueM RSS reader if there is a schedule
		if ( wp_next_scheduled( 'zeen101_dot_com_rss_feed_check' ) ) {
			wp_clear_scheduled_hook( 'zeen101_dot_com_rss_feed_check' );
		}

		if ( wp_next_scheduled( 'zeen101_dot_com_rss_feed_check' ) ) {
			wp_clear_scheduled_hook( 'zeen101_dot_com_rss_feed_check' );
		}

		delete_option( 'issuem_flush_rewrite_rules' );

		flush_rewrite_rules();
	}

	/**
	 * Initialize IssueM Admin Menu
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		add_submenu_page( 'edit.php?post_type=article', __( 'IssueM Settings', 'issuem' ), __( 'IssueM Settings', 'issuem' ), apply_filters( 'manage_issuem_settings', 'manage_issuem_settings' ), 'issuem', array( $this, 'settings_page' ) );
		add_submenu_page( 'edit.php?post_type=article', __( 'IssueM Help', 'issuem' ), __( 'IssueM Help', 'issuem' ), apply_filters( 'manage_issuem_settings', 'manage_issuem_settings' ), 'issuem-help', array( $this, 'help_page' ) );
	}

	/**
	 * Add settings link to plugin page
	 *
	 * @since 2.0.4
	 */
	public function issuem_settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=article&page=issuem">Settings</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Displays latest RSS item from Zeen101.com on Article list
	 *
	 * @since 1.0.0
	 *
	 * @param string $views
	 */
	public function display_zeen101_dot_com_rss_item( $views ) {

		global $current_user;

		$hide = get_user_meta( $current_user->ID, 'issuem_rss_item_notice_link', true );

		if ( $hide == 1 ) {
			return $views;
		} else {
			$last_rss_item = get_option( 'last_zeen101_dot_com_rss_item', true );

			if ( $last_rss_item ) {

				echo '<div class="notice notice-success">';
				echo wp_kses_post( $last_rss_item );
				echo '<p><a href="#" class="notice-link" data-notice="rss_item" data-type="dismiss">Dismiss</a></p>';
				echo '</div>';
			}
		}


		return $views;
	}

	/**
	 * Replaces Author Name with IssueM setting, if it is set
	 * Otherwise, uses WordPress's Author name
	 *
	 * @since 1.0.0
	 *
	 * @param string $wp_author WordPress Author name
	 * @return string Author Name
	 */
	public function the_author( $wp_author ) {

		global $post;

		if ( ! empty( $post->ID ) ) {

			$author_name = get_post_meta( $post->ID, '_issuem_author_name', true );

			if ( $author_name ) {
				return $author_name;
			} else {
				return $wp_author;
			}
		}

		return $wp_author;
	}

	/**
	 * Modifies WordPress query to include Articles in Tag/Category queries
	 *
	 * @since 1.0.0
	 *
	 * @param object $query WordPress Query Object
	 */
	public function add_issuem_articles_to_tag_query( $query ) {
		if ( ( $query->is_main_query() && ( $query->is_tag() || $query->is_category() ) ) || $query->is_author() ) {
			$post_type = $query->get( 'post_type' );
			if ( empty( $post_type ) ) {
				$post_type = array( 'post', 'article' );
			} else {
				if ( ! is_array( $post_type ) ) {
					$post_type = array_map( 'trim', explode( ',', $post_type ) );
				}
				$post_type[] = 'article';
			}
			$query->set( 'post_type', $post_type );
		}
	}

	/**
	 * Modifies WordPress query to remove draft Issues from queries
	 * Except for users with permission to see drafts
	 *
	 * @since 1.2.0
	 *
	 * @param object $query WordPress Query Object
	 */
	public function remove_draft_issues_from_main_query( $query ) {

		if (
			! is_admin() && ( $query->is_main_query() || $query->is_author() )
			&& ! current_user_can( apply_filters( 'see_issuem_draft_issues', 'manage_issues' ) )
		) {

			$term_ids = get_issuem_draft_issues();

			$draft_issues = array(
				'taxonomy' => 'issuem_issue',
				'field'    => 'id',
				'terms'    => $term_ids,
				'operator' => 'NOT IN',
			);

			if ( ! $query->is_tax() ) {

				$query->set(
					'tax_query',
					array(
						$draft_issues,
					)
				);
			} else {

				$term_ids = get_issuem_draft_issues();

				$tax_query             = $query->tax_query->queries;
				$tax_query[]           = $draft_issues;
				$tax_query['relation'] = 'AND';

				$query->set( 'tax_query', apply_filters( 'issuem_draft_issue_tax_query', $tax_query ) );
			}
		}
	}

	/**
	 * Enqueues styles used by IssueM WordPress Dashboard
	 *
	 * @since 1.0.0
	 * @uses wp_enqueue_style() to enqueue CSS files
	 */
	public function admin_wp_print_styles() {
		wp_enqueue_style( 'issuem_admin_style', ISSUEM_URL . '/css/issuem-admin.css', '', ISSUEM_VERSION );
	}

	/**
	 * Enqueues scripts used by IssueM WordPress Dashboard
	 *
	 * @since 1.0.0
	 * @uses wp_enqueue_script() to enqueue JS files
	 */
	public function admin_wp_enqueue_scripts( $hook_suffix ) {

		// Hack for edit-tags to include the "enctype=multipart/form-data" argument in the edit tags HTML form,
		// for uploading issue cover images
		if ( 'edit-tags.php' == $hook_suffix && ! empty( $_GET['taxonomy'] ) && 'issuem_issue' == $_GET['taxonomy'] ) {
			wp_enqueue_script( 'issuem_issue-custom-tax-hacks', ISSUEM_URL . '/js/issuem_issue-custom-tax-hacks.js', array( 'jquery' ), ISSUEM_VERSION, true );
		}

		if ( 'term.php' == $hook_suffix && ! empty( $_GET['taxonomy'] ) && 'issuem_issue' == $_GET['taxonomy'] ) {
			wp_enqueue_script( 'issuem-issue-admin', ISSUEM_URL . '/js/issuem-issue-admin.js', array( 'jquery' ), ISSUEM_VERSION, true );
			wp_enqueue_media();
		}

		if ( 'post.php' == $hook_suffix ) {
			wp_enqueue_script( 'issuem_issue-edit-article-hacks', ISSUEM_URL . '/js/issuem_issue-edit-article-hacks.js', array( 'jquery' ), ISSUEM_VERSION, true );
		}

		if ( 'article_page_issuem' == $hook_suffix ) {
			wp_enqueue_script( 'issuem-admin', ISSUEM_URL . '/js/issuem-admin.js', array( 'jquery', 'wp-color-picker' ), ISSUEM_VERSION, true );
			wp_enqueue_media();
		}

		wp_enqueue_script( 'issuem-script', ISSUEM_URL . 'js/script.js', array( 'jquery' ), ISSUEM_VERSION, true );

		wp_localize_script(
			'issuem-script',
			'issuem_ajax',
			array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'noticeNonce' => wp_create_nonce( 'issuem-notice-nonce' ),
			)
		);
	}

	/**
	 * Enqueues styles and scripts used by IssueM on the frontend
	 *
	 * @since 1.0.0
	 * @uses wp_enqueue_script() to enqueue JS files
	 * @uses wp_enqueue_style() to enqueue CSS files
	 */
	public function frontend_scripts() {
		$settings = $this->get_settings();

		if ( apply_filters( 'enqueue_issuem_styles', 'true' ) ) {

			switch ( $settings['css_style'] ) {

				case 'none':
					break;

				case 'default':
				default:
					wp_enqueue_style( 'issuem_style', ISSUEM_URL . 'css/issuem.css', '', ISSUEM_VERSION );
					break;
			}
		}

		wp_enqueue_script( 'jquery-issuem-flexslider', ISSUEM_URL . 'js/jquery.flexslider-min.js', array( 'jquery' ), ISSUEM_VERSION, true );
		wp_enqueue_style( 'jquery-issuem-flexslider', ISSUEM_URL . 'css/flexslider.css', '', ISSUEM_VERSION );
	}

	/**
	 * Gets IssueM settings
	 *
	 * @since 1.0.0
	 *
	 * @return array IssueM settings, merged with defaults.
	 */
	public function get_settings() {
		$defaults = array(
			'page_for_articles'      => 0,
			'page_for_archives'      => 0,
			'pdf_title'              => __( 'Download PDF', 'issuem' ),
			'pdf_only_title'         => __( 'PDF Only', 'issuem' ),
			'pdf_open_target'        => '_blank',
			'cover_image_width'      => 200,
			'cover_image_height'     => 268,
			'featured_image_width'   => 600,
			'featured_image_height'  => 338,
			'featured_thumb_width'   => 160,
			'featured_thumb_height'  => 120,
			'default_issue_image'    => apply_filters( 'issuem_default_issue_image', ISSUEM_URL . '/images/archive-image-unavailable.jpg' ),
			'show_thumbnail_byline'  => '',
			'custom_image_used'      => 0,
			'display_byline_as'      => 'user_firstlast',
			'issuem_author_name'     => '',
			'use_wp_taxonomies'      => '',
			'use_issue_tax_links'    => '',
			'article_format'         => '<p class="issuem_article_category">%CATEGORY[1]%</p>' . "\n" .
				'<p><a class="issuem_article_link" href="%URL%">%TITLE%</a></p>' . "\n" .
				'<p class="issuem_article_content">%EXCERPT%</p>' . "\n" .
				'<p class="issuem_article_byline">%BYLINE%</p>' . "\n",
			'css_style'              => 'default',
			'show_rotator_control'   => '',
			'show_rotator_direction' => '',
			'animation_type'         => 'slide',
			'issue_order_converted'  => false,



		);

		$defaults = apply_filters( 'issuem_default_settings', $defaults );

		$settings = get_option( 'issuem' );

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Update IssueM settings
	 *
	 * @since 1.2.0
	 *
	 * @param array IssueM settings
	 */
	public function update_settings( $settings ) {


		update_option( 'issuem', $settings );
	}

	/**
	 * Outputs the IssueM settings page
	 *
	 * @since 1.0
	 * @todo perform the save function earlier
	 */
	public function settings_page() {
		// Get the user options
		$settings = $this->get_settings();

		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
		} elseif ( isset( $_GET['page'] ) && $_GET['page'] == 'issuem' ) {
			$tab = 'general';
		} else {
			$tab = '';
		}

		$settings_tabs = apply_filters( 'issuem_settings_tabs', array( 'general', 'licenses' ) );
		$current_tab   = apply_filters( 'issuem_current_tab', $tab, $settings_tabs );

		if ( $current_tab == 'general' ) {

			if ( isset( $_POST['issuem_general_options_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['issuem_general_options_nonce'] ), 'issuem_general_options' ) ) {

				if ( ! empty( $_REQUEST['remove_default_issue_image'] ) ) {

					wp_delete_attachment( sanitize_text_field( $_REQUEST['remove_default_issue_image'] ) );

					unset( $settings['default_issue_image'] );
					unset( $settings['custom_image_used'] );

					$this->update_settings( $settings );

					$settings = $this->get_settings();
				}

				if ( ! empty( $_POST['update_issuem_settings'] ) ) {

					if ( ! empty( $_POST['page_for_articles'] ) ) {
						$settings['page_for_articles'] = sanitize_text_field( $_POST['page_for_articles'] );
					}

					if ( ! empty( $_POST['page_for_archives'] ) ) {
						$settings['page_for_archives'] = sanitize_text_field( $_POST['page_for_archives'] );
					}

					if ( ! empty( $_POST['css_style'] ) ) {
						$settings['css_style'] = sanitize_text_field( $_POST['css_style'] );
					}

					if ( ! empty( $_POST['pdf_title'] ) ) {
						$settings['pdf_title'] = sanitize_text_field( $_POST['pdf_title'] );
					}

					if ( ! empty( $_POST['pdf_only_title'] ) ) {
						$settings['pdf_only_title'] = sanitize_text_field( $_POST['pdf_only_title'] );
					}

					if ( ! empty( $_POST['pdf_open_target'] ) ) {
						$settings['pdf_open_target'] = sanitize_text_field( $_POST['pdf_open_target'] );
					}

					if ( ! empty( $_POST['article_format'] ) ) {
						$settings['article_format'] = wp_kses_post( $_POST['article_format'] );
					}

					if ( ! empty( $_POST['cover_image_width'] ) ) {
						$settings['cover_image_width'] = sanitize_text_field( $_POST['cover_image_width'] );
					} else {
						unset( $settings['cover_image_width'] );
					}

					if ( ! empty( $_POST['cover_image_height'] ) ) {
						$settings['cover_image_height'] = sanitize_text_field( $_POST['cover_image_height'] );
					} else {
						unset( $settings['cover_image_height'] );
					}

					if ( ! empty( $_POST['featured_image_width'] ) ) {
						$settings['featured_image_width'] = sanitize_text_field( $_POST['featured_image_width'] );
					} else {
						unset( $settings['featured_image_width'] );
					}

					if ( ! empty( $_POST['featured_image_height'] ) ) {
						$settings['featured_image_height'] = sanitize_text_field( $_POST['featured_image_height'] );
					} else {
						unset( $settings['featured_image_height'] );
					}

					if ( ! empty( $_POST['featured_thumb_width'] ) ) {
						$settings['featured_thumb_width'] = sanitize_text_field( $_POST['featured_thumb_width'] );
					} else {
						unset( $settings['featured_thumb_width'] );
					}

					if ( ! empty( $_POST['featured_thumb_height'] ) ) {
						$settings['featured_thumb_height'] = sanitize_text_field( $_POST['featured_thumb_height'] );
					} else {
						unset( $settings['featured_thumb_height'] );
					}

					if ( ! empty( $_POST['default_issue_image'] ) ) {
						$settings['default_issue_image'] = sanitize_text_field( $_POST['default_issue_image'] );
						$settings['custom_image_used']   = 1;
					}

					if ( ! empty( $_POST['display_byline_as'] ) ) {
						$settings['display_byline_as'] = sanitize_text_field( $_POST['display_byline_as'] );
					}

					if ( ! empty( $_POST['issuem_author_name'] ) ) {
						$settings['issuem_author_name'] = sanitize_text_field( $_POST['issuem_author_name'] );
					} else {
						unset( $settings['issuem_author_name'] );
					}

					if ( ! empty( $_POST['show_thumbnail_byline'] ) ) {
						$settings['show_thumbnail_byline'] = sanitize_text_field( $_POST['show_thumbnail_byline'] );
					} else {
						unset( $settings['show_thumbnail_byline'] );
					}

					if ( ! empty( $_POST['use_wp_taxonomies'] ) ) {
						$settings['use_wp_taxonomies'] = sanitize_text_field( $_POST['use_wp_taxonomies'] );
					} else {
						unset( $settings['use_wp_taxonomies'] );
					}

					if ( ! empty( $_POST['use_issue_tax_links'] ) ) {
						$settings['use_issue_tax_links'] = sanitize_text_field( $_POST['use_issue_tax_links'] );
					} else {
						unset( $settings['use_issue_tax_links'] );
					}

					if ( ! empty( $_POST['show_rotator_control'] ) ) {
						$settings['show_rotator_control'] = sanitize_text_field( $_POST['show_rotator_control'] );
					} else {
						unset( $settings['show_rotator_control'] );
					}

					if ( ! empty( $_POST['show_rotator_direction'] ) ) {
						$settings['show_rotator_direction'] = sanitize_text_field( $_POST['show_rotator_direction'] );
					} else {
						unset( $settings['show_rotator_direction'] );
					}

					if ( ! empty( $_POST['animation_type'] ) ) {
						$settings['animation_type'] = sanitize_text_field( $_POST['animation_type'] );
					}

					$settings = apply_filters( 'issuem_save_settings', $settings );

					$this->update_settings( $settings );

				}
			}
		} // endif for saving general tab settings



		if ( ! empty( $_REQUEST['update_issuem_settings'] ) || ! empty( $_GET['settings_saved'] ) ) {

			// update settings notification
			?>
			<div class="updated">
				<p><strong><?php esc_html_e( 'Settings updated.', 'issuem' ); ?></strong></p>
			</div>
			<?php

		}

		// Display HTML form for the options below
		?>
		<div class="wrap issuem-settings">

			<h2 style='margin-bottom: 10px;'><?php esc_html_e( 'IssueM Settings', 'issuem' ); ?></h2>

			<div class="postbox-container column-primary">

				<?php
				if ( in_array( $current_tab, $settings_tabs ) ) {
					?>
					<h2 class="nav-tab-wrapper" id="issuem-tabs">

						<a id="general-tab" href="<?php echo esc_url( admin_url( 'edit.php?post_type=article&page=issuem' ) ); ?>" class="nav-tab
																<?php
																if ( $current_tab == 'general' ) {
																	?>
								nav-tab-active<?php } ?>"><?php esc_html_e( 'General', 'issuem' ); ?></a>

						<?php do_action( 'issuem_settings_tabs_links', $current_tab ); ?>

						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=article&page=issuem&tab=licenses' ) ); ?>" class="nav-tab
											<?php
											if ( $current_tab == 'licenses' ) {
												?>
								nav-tab-active<?php } ?>"><?php esc_html_e( 'Licenses', 'issuem' ); ?></a>

					</h2>
					<?php
				}
				?>



				<div class="tabwrapper">

					<form id="issuem" method="post" action="" enctype="multipart/form-data" encoding="multipart/form-data">

						<?php do_action( 'issuem_before_settings', $current_tab ); ?>

						<?php if ( $current_tab == 'general' ) : ?>



							<div id="general" class="issuemtab">
								<div>
									<div class="meta-box-sortables ui-sortable">

										<div id="modules">

											<div class="inside">

												<table id="issuem_administrator_options" class="form-table">

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Page for Articles', 'issuem' ); ?></th>
														<td>
														<?php
														wp_dropdown_pages(
															array(
																'name'              => 'page_for_articles',
																'show_option_none'  => esc_attr__( '&mdash; Select &mdash;' ),
																'option_none_value' => '0',
																'selected'          => esc_attr( $settings['page_for_articles'] ),
															)
														);
														?>
															</td>
													</tr>
													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Page for Issue Archives', 'issuem' ); ?></th>
														<td>
														<?php
														wp_dropdown_pages(
															array(
																'name'              => 'page_for_archives',
																'show_option_none'  => esc_attr__( '&mdash; Select &mdash;' ),
																'option_none_value' => '0',
																'selected'          => esc_attr( $settings['page_for_archives'] ),
															)
														);
														?>
															</td>
													</tr>

													<?php if ( apply_filters( 'enqueue_issuem_styles', true ) ) { ?>

														<tr>
															<th rowspan="1"> <?php esc_html_e( 'CSS Style', 'issuem' ); ?></th>
															<td>
																<select id='css_style' name='css_style'>
																	<?php
																	$css_styles = $this->get_css_styles();
																	foreach ( $css_styles as $slug => $name ) {
																		?>
																		<option value='<?php echo esc_attr( $slug ); ?>' <?php selected( $slug, $settings['css_style'] ); ?>><?php echo esc_html( $name ); ?></option>
																		<?php
																	}
																	?>
																</select>
															</td>
														</tr>

													<?php } ?>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'PDF Download Link Title', 'issuem' ); ?></th>
														<td><input type="text" id="pdf_title" class="regular-text" name="pdf_title" value="<?php echo esc_attr( $settings['pdf_title'] ); ?>" /></td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'PDF Only Title', 'issuem' ); ?></th>
														<td><input type="text" id="pdf_only_title" class="regular-text" name="pdf_only_title" value="<?php echo esc_attr( $settings['pdf_only_title'] ); ?>" /></td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'PDF Link Target', 'issuem' ); ?></th>
														<td>
															<select id='pdf_open_target' name='pdf_open_target'>
																<option value='_blank' <?php selected( '_blank', $settings['pdf_open_target'] ); ?>><?php esc_html_e( 'Open in New Window/Tab', 'issuem' ); ?></option>
																<option value='_self' <?php selected( '_self', $settings['pdf_open_target'] ); ?>><?php esc_html_e( 'Open in Same Window/Tab', 'issuem' ); ?></option>
															</select>
														</td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Cover Image Size', 'issuem' ); ?></th>
														<td>
															<?php esc_html_e( 'Width', 'issuem' ); ?> <input type="text" id="cover_image_width" class="small-text" name="cover_image_width" value="<?php echo esc_attr( stripcslashes( $settings['cover_image_width'] ) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php esc_html_e( 'Height', 'issuem' ); ?> <input type="text" id="cover_image_height" class="small-text" name="cover_image_height" value="<?php echo esc_attr( stripcslashes( $settings['cover_image_height'] ) ); ?>" />px
														</td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Featured Rotator Image Size', 'issuem' ); ?></th>
														<td>
															<?php esc_html_e( 'Width', 'issuem' ); ?> <input type="text" id="featured_image_width" class="small-text" name="featured_image_width" value="<?php echo esc_attr( stripcslashes( $settings['featured_image_width'] ) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php esc_html_e( 'Height', 'issuem' ); ?> <input type="text" id="featured_image_height" class="small-text" name="featured_image_height" value="<?php echo esc_attr( stripcslashes( $settings['featured_image_height'] ) ); ?>" />px
														</td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Featured Thumbnail Image Size', 'issuem' ); ?></th>
														<td>
															<?php esc_html_e( 'Width', 'issuem' ); ?> <input type="text" id="featured_thumb_width" class="small-text" name="featured_thumb_width" value="<?php echo esc_attr( stripcslashes( $settings['featured_thumb_width'] ) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php esc_html_e( 'Height', 'issuem' ); ?> <input type="text" id="featured_thumb_height" class="small-text" name="featured_thumb_height" value="<?php echo esc_attr( stripcslashes( $settings['featured_thumb_height'] ) ); ?>" />px
														</td>
													</tr>
													<tr>
														<td></td>
														<td>
															<p>After changing these image settings you may need to <a target="_blank" href="https://wordpress.org/plugins/regenerate-thumbnails/">regenerate your thumbnails</a>.</p>
														</td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Default Issue Image', 'issuem' ); ?></th>
														<td>
															<input id="default_issue_image" type="text" size="36" name="default_issue_image" value="<?php echo esc_attr( $settings['default_issue_image'] ); ?>" />
															<input id="upload_image_button" class="button" type="button" value="Upload Image" />
															<p>Enter a URL or upload an image</p>



															<p><img style="max-width: 400px;" src="<?php echo esc_url( $settings['default_issue_image'] ); ?>" /></p>

														</td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Display Byline As', 'issuem' ); ?></th>
														<td>
															<select id="display_byline_as" name="display_byline_as">
																<option value="user_firstlast" <?php selected( 'user_firstlast' == $settings['display_byline_as'] ); ?>>First & Last Name</option>
																<option value="user_firstname" <?php selected( 'user_firstname' == $settings['display_byline_as'] ); ?>>First Name</option>
																<option value="user_lastname" <?php selected( 'user_lastname' == $settings['display_byline_as'] ); ?>>Last Name</option>
																<option value="display_name" <?php selected( 'display_name' == $settings['display_byline_as'] ); ?>>Display Name</option>
															</select>
														</td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Show Thumbnail Byline', 'issuem' ); ?></th>
														<td><input type="checkbox" id="show_thumbnail_byline" name="show_thumbnail_byline" value="1" <?php checked( $settings['show_thumbnail_byline'], 1 ); ?> /></td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Name', 'issuem' ); ?></th>
														<td><input type="checkbox" id="issuem_author_name" name="issuem_author_name" value="1" <?php checked( $settings['issuem_author_name'], 1 ); ?> /> <?php esc_html_e( 'Use IssueM Author Name instead of WordPress Author', 'issuem' ); ?></td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Categories and Tags', 'issuem' ); ?></th>
														<td><input type="checkbox" id="use_wp_taxonomies" name="use_wp_taxonomies" value="1" <?php checked( $settings['use_wp_taxonomies'], 1 ); ?> /> <?php esc_html_e( 'Use Default WordPress Category and Tag Taxonomies', 'issuem' ); ?></td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Links', 'issuem' ); ?></th>
														<td><input type="checkbox" id="use_issue_tax_links" name="use_issue_tax_links" value="1" <?php checked( $settings['use_issue_tax_links'], 1 ); ?> /> <?php esc_html_e( 'Use Taxonomical links instead of shortcode based links for Issues', 'issuem' ); ?></td>
													</tr>

												</table>

												<?php wp_nonce_field( 'issuem_general_options', 'issuem_general_options_nonce' ); ?>



											</div> <!-- inside -->

										</div> <!-- postbox -->

										<div id="modules">


											<h3><span><?php esc_html_e( 'IssueM Featured Rotator Options', 'issuem' ); ?></span></h3>

											<div class="inside">

												<table id="issuem_administrator_options" class="form-table">

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Pagination Navigation', 'issuem' ); ?></th>
														<td><input type="checkbox" id="show_rotator_control" name="show_rotator_control" value="1" <?php checked( $settings['show_rotator_control'], 1 ); ?> /> Display pagination below the slider</td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Direction Navigation', 'issuem' ); ?></th>
														<td><input type="checkbox" id="show_rotator_direction" name="show_rotator_direction" value="1" <?php checked( $settings['show_rotator_direction'], 1 ); ?> />Display previous/next navigation arrows</td>
													</tr>

													<tr>
														<th rowspan="1"> <?php esc_html_e( 'Animation Type', 'issuem' ); ?></th>
														<td>
															<select id="animation_type" name="animation_type">
																<option value="slide" <?php selected( 'slide' == $settings['animation_type'] ); ?>>Slide</option>
																<option value="fade" <?php selected( 'fade' == $settings['animation_type'] ); ?>>Fade</option>

															</select>
														</td>
													</tr>



												</table>



											</div> <!-- inside -->

										</div> <!-- postbox -->

										<div id="modules">


											<h3><span><?php esc_html_e( 'IssueM Article Shortcode Format', 'issuem' ); ?></span></h3>

											<div class="inside">

												<p>This controls the article output of the [issuem_articles] shortcode on the Current Issue page.</p>

												<?php do_action( 'issuem_before_default_article_format', $settings ); ?>

												<textarea id="article_format" class="code" cols="75" rows="8" name="article_format"><?php echo wp_kses_post( stripslashes( $settings['article_format'] ) ); ?></textarea>

												<?php do_action( 'issuem_after_default_article_format', $settings ); ?>

												<p>Available template tags:<br> %CATEGORY%, %TAG%, %TEASER%, %EXCERPT%, %CONTENT%, %FEATURE_IMAGE%, %ISSUEM_FEATURE_THUMB%, %BYLINE%, and %DATE%</p>

												<p class="submit">
													<input class="button-primary" type="submit" name="update_issuem_settings" value="<?php esc_attr_e( 'Save Settings', 'issuem' ); ?>" />
												</p>

											</div> <!-- inside -->

										</div> <!-- postbox -->

									</div>

								</div>

							</div> <!-- hometab -->

							<?php do_action( 'issuem_settings_areas' ); ?>

							<?php
						endif; // general tab
						?>

						<?php if ( $current_tab == 'licenses' ) : ?>

							<?php do_action( 'issuem_before_licenses_settings' ); ?>

							<h2><a target="_blank" href="https://leakypaywall.com/downloads/category/issuem-addons/?utm_source=plugin&utm_medium=license_tab&utm_content=link&utm_campaign=settings">Find out more about our add-ons</a></h2>

							<?php wp_nonce_field( 'verify', 'issuem_license_wpnonce' ); ?>

							<?php do_action( 'issuem_after_licenses_settings' ); ?>

						<?php endif; ?>

					</form>

				</div> <!-- tabwrapper -->

			</div> <!-- column-primary -->

			<div class="postbox-container column-secondary">

				<div class="metabox-holder">
					<div class="postbox">

						<h3><span><?php esc_html_e( 'Need help setting up your publication?', 'issuem' ); ?></span></h3>

						<div class="inside">
							<p>Please read our <a target="_blank" href="https://docs.leakypaywall.com/article/84-getting-started-with-issuem">Getting Started</a> guide.</p>
						</div>

					</div>
				</div>

			</div>
		</div>


		<?php
	}

	/**
	 * Outputs the IssueM settings page
	 *
	 * @since 1.0.0
	 * @uses do_action() On 'help_page' for addons
	 */
	public function help_page() {
		// Display HTML
		?>
		<div class=wrap>
			<div style="width:70%;" class="postbox-container">


				<h2 style='margin-bottom: 10px;'><?php esc_html_e( 'IssueM Help', 'issuem' ); ?></h2>

				<div id="issuem-getting-started">


					<h3><span><?php esc_html_e( 'Getting Started', 'issuem' ); ?></span></h3>


					<p><?php esc_html_e( 'The following steps will demonstrate how to get started creating your online magazine.', 'issuem' ); ?></p>

					<iframe width="560" height="315" src="//www.youtube.com/embed/lUwsQFVB5ro?rel=0" frameborder="0" allowfullscreen></iframe>

					<h4>1. Install IssueM</h4>

					<ol>
						<li>Go to Plugins->Add New and search for IssueM</li>
						<li>Click "Install Now" and then "Active Plugin"</li>
					</ol>

					<h4>2. Create pages for Current Issue and Past Issues</h4>
					<ol>
						<li>Go to Pages->Add New</li>
						<li>Create a page for your current issue. We recommend using "Current Issue" as the page title.</li>
						<li>Create a page for your issue archives. We recommend using "Past Issues" as the page title.</li>
					</ol>

					<h4>3. Configure IssueM Settings</h4>
					<ol>
						<li>Go to Articles->IssueM Settings</li>
						<li>Choose your page for articles (your current issue page)</li>
						<li>Choose your page for issue archives (your past issues page)</li>
						<li>You can configure the rest of the options to your liking, or leave them in their default state.</li>
						<li>Click "Save Settings"</li>
					</ol>

					<h4>4. Create an Issue</h4>
					<ol>
						<li>Go to Articles->Issues</li>
						<li>Enter the name of the issue (i.e. Summer 2014) and click "Add New Issues"</li>
						<li>Click on the newly created issue title</li>
						<li>Upload a cover image. You can adjust the dimensions of the cover image on the IssueM Settings page.</li>
						<li>Enter any other information for the issue, if applicable</li>
						<li>Click "Update"</li>
					</ol>

					<h4>5. Add Articles to the Issue</h4>
					<ol>
						<li>Go to Articles->Add New</li>
						<li>Enter the title and content for your article, just like a normal WordPress post</li>
						<li>Add a featured image, if applicable</li>
						<li>Choose the issue the article is related to in the Issues sidebar area</li>
						<li>Adjust the IssueM Article Options at the bottom of the article, if applicable</li>
					</ol>

					<h4>6. Add IssueM Active Issue Widget to Sidebar</h4>
					<ol>
						<li>Go to Appearance->Widgets</li>
						<li>Drag the IssueM Active Issue widget into your sidebar</li>
						<li>Click "Save"</li>
					</ol>

					<h4>7. Set Issue to Published</h4>
					<ol>
						<li>Go to Articles->Issues</li>
						<li>Click on the title of the issue you want to make live</li>
						<li>Change the Issue Status dropdown to "Live"</li>
						<li>Click "Update"</li>
					</ol>

				</div>

				<h3><span><?php esc_html_e( 'IssueM Shortcodes', 'issuem' ); ?></span></h3>

				<p><strong>IssueM <?php esc_html_e( 'Issue Title:', 'issuem' ); ?> </strong><code style="font-size: 1.2em; background: #ffffe0;">[issuem_issue_title]</code></p>

				<p><?php esc_html_e( 'This shortcode will display the current issue title.', 'issuem' ); ?></p>

				<hr>


				<p><strong>IssueM <?php esc_html_e( 'Article Loop:', 'issuem' ); ?> </strong><code style="font-size: 1.2em; background: #ffffe0;">[issuem_articles]</code></p>

				<p><?php esc_html_e( 'This shortcode will display the list of articles in an issue.', 'issuem' ); ?></p>

				<h4><?php esc_html_e( 'Examples:', 'issuem' ); ?></h4>

				<p><em>[issuem_articles orderby="menu_order" order="ASC" show_featured="1"]</em></p>



				<hr>


				<p><strong>IssueM <?php esc_html_e( 'Featured Article Rotator:', 'issuem' ); ?> </strong><code style="font-size: 1.2em; background: #ffffe0;">[issuem_featured_rotator]</code></p>

				<p><?php esc_html_e( 'This shortcode will display a slideshow of articles that have been checked to display in the featured rotator.', 'issuem' ); ?></p>

				<h4><?php esc_html_e( 'Examples:', 'issuem' ); ?></h4>

				<p><em>[issuem_featured_rotator show_byline="true"]</em></p>

				<hr>

				<p><strong>IssueM <?php esc_html_e( 'Featured Thumbnails:', 'issuem' ); ?></strong> <code style="font-size: 1.2em; background: #ffffe0;">[issuem_featured_thumbnails]</code></code>



				<p><?php esc_html_e( 'This shortcode will display the grid of featured article thumbnails in an issue', 'issuem' ); ?>.</p>

				<h4><?php esc_html_e( 'Examples:', 'issuem' ); ?></h4>

				<p><em>[issuem_featured_thumbnails orderby="menu_order" order="ASC"]</em></p>

				<hr>


				<p><strong>IssueM <?php esc_html_e( 'Archive Page:', 'issuem' ); ?>:</strong> <code style="font-size: 1.2em; background: #ffffe0;">[issuem_archives]</code></p>

				<p><?php esc_html_e( 'This shortcode will display the list of current and past issues.', 'issuem' ); ?></p>

				<h4><?php esc_html_e( 'Default Arguments:', 'issuem' ); ?></h4>


				<ul>
					<li><em>orderby</em> - term_id</li>
					<li><em>order</em> - DESC</li>
					<li><em>limit</em> - 0</li>
					<li><em>pdf_title</em> - IssueM <?php esc_html_e( 'Setting "PDF Title"', 'issuem' ); ?></li>
					<li><em>default_image</em> - IssueM <?php esc_html_e( 'Setting "Default Cover Image"', 'issuem' ); ?></li>
				</ul>

				<h4><?php esc_html_e( 'Accepted Arguments:', 'issuem' ); ?></h4>

				<ul>
					<li><em>orderby</em> - term_id, issue_order, name</li>
					<li><em>order</em> - DESC, ASC</li>
					<li><em>limit</em> - <?php esc_html_e( 'Any number 0 and greater', 'issuem' ); ?></li>
					<li><em>pdf_title</em> - <?php esc_html_e( 'Text', 'issuem' ); ?></li>
					<li><em>default_image</em> - <?php esc_html_e( 'Image URL', 'issuem' ); ?></li>
				</ul>

				<h4><?php esc_html_e( 'Examples:', 'issuem' ); ?></h4>

				<p><em>[issuem_archives orderby="issue_order"]</em></p>
				<p><em>[issuem_archives orderby="name" order="ASC" limit=5 pdf_title="<?php esc_attr_e( 'Download Now', 'issuem' ); ?>" default_image="http://yoursite.com/yourimage.jpg"]</em></p>











				<?php do_action( 'issuem_help_page' ); ?>


			</div>
		</div>
		<?php
	}
	/**
	 * Outputs the IssueM CSS page
	 *
	 * @since 1.3.0
	 */
	public function css_page() {
		// Display HTML
		?>
		<div class=wrap>
			<div style="width:70%;" class="postbox-container">
				<div class="metabox-holder">
					<div class="meta-box-sortables ui-sortable">

						<h2 style='margin-bottom: 10px;'><?php esc_html_e( 'IssueM Advanced Styles', 'issuem' ); ?></h2>

						<div id="issuem-articles" class="postbox">

							<div class="handlediv" title="Click to toggle"><br /></div>

							<h3 class="hndle"><span><?php esc_html_e( 'Advanced Style Options', 'issuem' ); ?></span></h3>

							<div class="inside">

								<table class="form-table">

									<tr>

										<td>

											Reset to Default Styles

										</td>

									</tr>

								</table>

							</div>

						</div>

					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Upgrade function, tests for upgrade version changes and performs necessary actions
	 *
	 * @since 1.0.0
	 */
	public function upgrade() {
		$settings = $this->get_settings();

		if ( ! empty( $settings['version'] ) ) {
			$old_version = $settings['version'];
		} else {
			$old_version = 0;
		}

		if ( version_compare( $old_version, '1.1.2', '<' ) ) {

			delete_option( 'last_zeen101_dot_com_rss_item' );
			wp_clear_scheduled_hook( 'zeen101_dot_com_rss_feed_check' );
			zeen101_dot_com_rss_feed_check();
		}

		if ( version_compare( $old_version, '1.2.0', '<' ) ) {
			$this->upgrade_to_1_2_0( $old_version );
		}

		$settings['version'] = ISSUEM_VERSION;
		$this->update_settings( $settings );
	}

	/**
	 * Initialized permissions
	 *
	 * @since 1.2.0
	 */
	public function upgrade_to_1_2_0( $old_version ) {

		$role = get_role( 'administrator' );
		if ( $role !== null ) {
			// Articles
			$role->add_cap( 'edit_article' );
		}
		$role->add_cap( 'read_article' );
		$role->add_cap( 'delete_article' );
		$role->add_cap( 'edit_articles' );
		$role->add_cap( 'edit_others_articles' );
		$role->add_cap( 'publish_articles' );
		$role->add_cap( 'read_private_articles' );
		$role->add_cap( 'delete_articles' );
		$role->add_cap( 'delete_private_articles' );
		$role->add_cap( 'delete_published_articles' );
		$role->add_cap( 'delete_others_articles' );
		$role->add_cap( 'edit_private_articles' );
		$role->add_cap( 'edit_published_articles' );
		// Issues
		$role->add_cap( 'manage_issuem_settings' );
		$role->add_cap( 'manage_issues' );
		$role->add_cap( 'manage_article_categories' );
		$role->add_cap( 'manage_article_tags' );
		$role->add_cap( 'edit_issues' );
		$role->add_cap( 'edit_others_issues' );
		$role->add_cap( 'edit_published_issues' );
		$role->add_cap( 'publish_issues' );

		$role = get_role( 'editor' );

		// Articles
		$role->add_cap( 'edit_articles' );
		$role->add_cap( 'edit_others_articles' );
		$role->add_cap( 'edit_published_articles' );
		$role->add_cap( 'publish_articles' );
		$role->add_cap( 'delete_published_articles' );
		$role->add_cap( 'delete_others_articles' );
		$role->add_cap( 'delete_articles' );
		$role->add_cap( 'delete_private_articles' );
		$role->add_cap( 'edit_private_articles' );
		$role->add_cap( 'read_private_articles' );
		// Issues
		$role->add_cap( 'manage_issues' );
		$role->add_cap( 'manage_article_categories' );
		$role->add_cap( 'manage_article_tags' );
		$role->add_cap( 'edit_issues' );
		$role->add_cap( 'edit_others_issues' );
		$role->add_cap( 'edit_published_issues' );
		$role->add_cap( 'publish_issues' );

		$role = get_role( 'author' );

		// Articles
		$role->add_cap( 'edit_articles' );
		$role->add_cap( 'edit_published_articles' );
		$role->add_cap( 'publish_articles' );
		$role->add_cap( 'delete_articles' );
		$role->add_cap( 'delete_published_articles' );
		// Issues
		$role->add_cap( 'edit_issues' );
		$role->add_cap( 'edit_published_issues' );
		$role->add_cap( 'publish_issues' );

		$role = get_role( 'contributor' );

		// Articles
		$role->add_cap( 'edit_articles' );
		$role->add_cap( 'delete_articles' );
		// Issues
		$role->add_cap( 'edit_issues' );

		if ( 0 != $old_version ) {

			update_option( 'issuem_nag', '<strong>Attention IssueM Subscribers!</strong> We have launched a new version of IssueM and split out the Advanced Search and Migration Tool into their own plugins. If you were using either of these functions in your previous version of IssueM, you will need to download them from your account at <a href="http://issuem.com/">IssueM</a> and install them on your site.<br />Sorry for any inconvenience this may have caused you and thank you for your continued support!' );
		}
	}

	/**
	 * API Request sent and processed by the IssueM API
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments to send to the IssueM API
	 */
	public function issuem_api_request( $_action, $_data ) {

		$settings = $this->get_settings();

		$api_params = array(
			'edd_action' => 'get_version',
			'name'       => $_data['name'],
			'slug'       => $_data['slug'],
			'license'    => $_data['license'],
			'author'     => 'IssueM Development Team',
		);
		$request    = wp_remote_post(
			ZEEN101_STORE_URL,
			array(
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( ! is_wp_error( $request ) ) {

			$request = json_decode( wp_remote_retrieve_body( $request ) );

			if ( $request ) {
				$request->sections = maybe_unserialize( $request->sections );
			}

			return $request;
		} else {

			return false;
		}
	}

	/**
	 * Verify the API status reported back to IssueM
	 *
	 * @since 1.0.0
	 *
	 * @param object $response WordPress remote query body
	 */
	public function api_status( $response ) {

		if ( 1 < $response->account_status ) {

			update_option( 'issuem_nag', $response->response );
		} else {

			delete_option( 'issuem_nag' );
			delete_option( 'issuem_nag_version_dismissed' );
		}
	}

	/**
	 * Returns the style available with IssueM
	 *
	 * @since 1.0.0
	 * @uses apply_filters on 'issuem_css_styles' hook, for extending IssueM
	 */
	public function get_css_styles() {
		$styles = array(
			'default' => __( 'Default', 'issuem' ),
			'none'    => __( 'None', 'issuem' ),
		);

		return apply_filters( 'issuem_css_styles', $styles );
	}

	/**
	 * If an IssueM notification is set, display it.
	 * Called by teh admin_notices hook
	 *
	 * @since 1.0.0
	 */
	public function issuem_notification() {

		if ( ! empty( $_GET['remove_issuem_nag'] ) ) {
			delete_option( 'issuem_nag' );
			update_option( 'issuem_nag_version_dismissed', ISSUEM_VERSION );
		}

		$notification = get_option( 'issuem_nag' );

		if ( $notification && version_compare( get_option( 'issuem_nag_version_dismissed' ), ISSUEM_VERSION, '<' ) ) {
			echo '<div class="update-nag"><p>' . esc_html( $notification ) . '</p><p><a href="' . esc_url( add_query_arg( 'remove_issuem_nag', true ) ) . '">' . esc_html__( 'Dismiss', 'issuem' ) . '</a></p></div>';
		}
	}

	/**
	 * Process ajax calls for notice links
	 *
	 * @since 2.0.3
	 */
	public function ajax_process_notice_link() {

		if ( ! isset( $_POST['nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'issuem-notice-nonce' ) ) {
			die( 'Busted!' );
		}

		global $current_user;

		update_user_meta( $current_user->ID, 'issuem_rss_item_notice_link', 1 );

		echo esc_html( get_user_meta( $current_user->ID, 'issuem_rss_item_notice_link', true ) );

		exit;
	}
}
