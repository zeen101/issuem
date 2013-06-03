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
if ( ! class_exists( 'IssueM' ) ) {
	
	class IssueM {
		
		/**
		 * Class constructor, puts things in motion
		 *
		 * @since 1.0.0
		 *
		 * @todo Move the the_author filter to a more appopriate place
		 * @todo Move the pre_get_posts filter to a more appopriate place
		 *
		 * @uses add_image_size() Sets image sizes for IssueM
		 * @uses add_action() Calls 'admin_init' hook on $this->upgrade
		 * @uses add_action() Calls 'admin_notices' hook on $this->issuem_notification
		 * @uses add_action() Calls 'admin_enqueue_scripts' hook on $this->admin_wp_enqueue_scripts
		 * @uses add_action() Calls 'admin_print_styles' hook on $this->admin_wp_print_styles
		 * @uses add_action() Calls 'wp_enqueue_scripts' hook on $this->frontend_scripts
		 * @uses add_filter() Calls 'plugins_api' hook on $this->issuem_plugins_api
		 * @uses add_filter() Calls 'pre_set_site_transient_update_plugins' hook on $this->issuem_update_plugins
		 * @uses add_filter() Calls 'views_edit-article' hook on $this->add_issuem_articles_to_tag_query
		 * @uses the_author() Calls 'the_author' hook on $this->issuem_the_author
		 * @uses add_action() Calls 'pre_get_posts' hook on $this->add_issuem_articles_to_tag_query
		 */
		function IssueM() {
			
			$settings = $this->get_settings();
			
			add_image_size( 'issuem-cover-image', apply_filters( 'issuem-cover-image-width', $settings['cover_image_width'] ), apply_filters( 'issuem-cover-image-height', $settings['cover_image_height'] ), true );
			add_image_size( 'issuem-featured-rotator-image', apply_filters( 'issuem-featured-rotator-image-width', $settings['featured_image_width'] ), apply_filters( 'issuem-featured-rotator-image-height', $settings['featured_image_height'] ), true );
			add_image_size( 'issuem-featured-thumb-image', apply_filters( 'issuem-featured-thumb-image-width', $settings['featured_thumb_width'] ), apply_filters( 'issuem-featured-thumb-image-height', $settings['featured_thumb_height'] ), true );
		
			add_action( 'admin_init', array( $this, 'upgrade' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_notices', array( $this, 'issuem_notification' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_wp_enqueue_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'admin_wp_print_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
			add_action( 'wp_ajax_verify', array( $this, 'issuem_api_ajax_verify' ) );
			
			if ( empty( $settings['issuem_API'] ) ) {
			
				update_option( 'issuem_api_error_received', true );
				update_option( 'issuem_api_error_message', __( 'You can enter your IssueM API key in the <a href="/wp-admin/edit.php?post_type=article&page=issuem">IssueM Settings</a> to continue to get access to premium support and add-ons.', 'issuem' ) );
				
			} else {
	
				//Premium Plugin Filters
				add_filter( 'plugins_api', array( $this, 'issuem_plugins_api' ), 10, 3 );
				add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'issuem_update_plugins' ) );
				
				delete_option( 'issuem_api_error_received' );
				delete_option( 'issuem_api_error_message' );
				
			}
			
			register_activation_hook( __FILE__, array( $this, 'issuem_flush_rewrite_rules' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
			
			add_filter( 'views_edit-article', array( $this, 'display_issuem_dot_com_rss_item' ) );
			
			if ( !empty( $settings['issuem_author_name'] ) && !is_admin() ) 
				add_filter( 'the_author', array( $this, 'the_author' ) );
			
			if ( !empty( $settings['use_wp_taxonomies'] ) ) 
				add_action( 'pre_get_posts', array( $this, 'add_issuem_articles_to_tag_query' ) );
				
			if ( !is_admin() )
				add_action( 'pre_get_posts', array( $this, 'remove_draft_issues_from_main_query' ) );
			
		}
		
		/**
		 * Runs activation routines when IssueM is activated
		 *
		 * @since 1.0.0
		 *
		 */
		function activation() {
					
			// ATTENTION: This is *only* done during plugin activation hook in this example!
			// You should *NEVER EVER* do this on every page load!!
			flush_rewrite_rules();
			
		}
	
		/**
		 * Runs deactivation routines if IssueM is deactivated
		 *
		 * @since 1.1.1
		 *
		 */
		function deactivation() {
			
			// Clear the IssueM RSS reader if there is a schedule
			if ( wp_next_scheduled( 'issuem_dot_com_rss_feed_check' ) )
				wp_clear_scheduled_hook( 'issuem_dot_com_rss_feed_check' );
			
		}
			
		/**
		 * Initialize IssueM Admin Menu
		 *
		 * @since 1.0.0
		 */
		function admin_menu() {
							
			add_submenu_page( 'edit.php?post_type=article', __( 'IssueM Settings', 'issuem' ), __( 'IssueM Settings', 'issuem' ), apply_filters( 'manage_issuem_settings', 'manage_issuem_settings' ), 'issuem', array( $this, 'settings_page' ) );
		
			add_submenu_page( 'edit.php?post_type=article', __( 'IssueM Help', 'issuem' ), __( 'IssueM Help', 'issuem' ), apply_filters( 'manage_issuem_settings', 'manage_issuem_settings' ), 'issuem-help', array( $this, 'help_page' ) );
			
		}
		
		/**
		 * Displays latest RSS item from IssueM.com on Article list
		 *
		 * @since 1.0.0
		 *
		 * @param string $views
		 */
		function display_issuem_dot_com_rss_item( $views ) {
		
			if ( $last_rss_item = get_option( 'last_issuem_dot_com_rss_item', true ) ) {
				
				echo '<div id="issuem_rss_item">';
				echo $last_rss_item;
				echo '</div>';
				
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
		function the_author( $wp_author ) {
		
			global $post;
			
			if ( !empty( $post->ID ) ) {
					
				if ( $author_name = get_post_meta( $post->ID, '_issuem_author_name', true ) )
					return $author_name;
				else
					return $wp_author;
				
			}
			
			return $wp_author;
			
		}
	
		/**
		 * Modifies WordPress query to include Articles in Tag/Category queries
		 *
		 * @since 1.0.0
		 *
		 * @param object $query WordPress Query Object
\		 */
		function add_issuem_articles_to_tag_query( $query ) {
		
		   if ( $query->is_main_query()
			 && ( $query->is_tag() || $query->is_category() ) )
			 $query->set( 'post_type', array( 'post', 'article' ) );
		   
		}
		
		/**
		 * Modifies WordPress query to remove draft Issues from queries
		 * Except for users with permission to see drafts
		 *
		 * @since 1.2.0
		 *
		 * @param object $query WordPress Query Object
\		 */
		function remove_draft_issues_from_main_query( $query ) {
			
			if ( !is_admin() && $query->is_main_query() 
				&& !current_user_can( apply_filters( 'see_issuem_draft_issues', 'manage_issues' ) ) ) {
								
				if ( empty( $query->tax_query->queries ) ) {
				
					$term_ids = get_issuem_draft_issues();	
					
					$query->set( 'tax_query', array(
							array(
								'taxonomy' => 'issuem_issue',
								'field' => 'id',
								'terms' => $term_ids,
								'operator' => 'NOT IN'
							),
						) 
					);
				
				}
			
			}
			
		}
		
		/**
		 * Enqueues styles used by IssueM WordPress Dashboard
		 *
		 * @since 1.0.0
		 * @uses wp_enqueue_style() to enqueue CSS files
		 */
		function admin_wp_print_styles() {
		
			global $hook_suffix;
			
			if ( 'article_page_issuem' == $hook_suffix 
				|| ( 'edit.php' == $hook_suffix && isset( $_GET['post_type'] ) && 'article' == $_GET['post_type'] ) )
				wp_enqueue_style( 'issuem_admin_style', ISSUEM_PLUGIN_URL . '/css/issuem-admin.css', '', ISSUEM_VERSION );
			
		}
	
		/**
		 * Enqueues scripts used by IssueM WordPress Dashboard
		 *
		 * @since 1.0.0
		 * @uses wp_enqueue_script() to enqueue JS files
		 */
		function admin_wp_enqueue_scripts( $hook_suffix ) {
			
			//echo "<h4>$hook_suffix</h4>";
			
			// Hack for edit-tags to include the "enctype=multipart/form-data" argument in the edit tags HTML form, 
		 	// for uploading issue cover images
			if ( 'edit-tags.php' == $hook_suffix && isset( $_GET['taxonomy'] ) && 'issuem_issue' == $_GET['taxonomy'] )
				wp_enqueue_script( 'issuem_issue-custom-tax-hacks', ISSUEM_PLUGIN_URL . '/js/issuem_issue-custom-tax-hacks.js', array( 'jquery' ), ISSUEM_VERSION );
				
			if ( 'post.php' == $hook_suffix )
				wp_enqueue_script( 'issuem_issue-edit-article-hacks', ISSUEM_PLUGIN_URL . '/js/issuem_issue-edit-article-hacks.js', array( 'jquery' ), ISSUEM_VERSION );
				
			if ( 'article_page_issuem' == $hook_suffix )
				wp_enqueue_script( 'issuem-admin', ISSUEM_PLUGIN_URL . '/js/issuem-admin.js', array( 'jquery' ), ISSUEM_VERSION );
			
		}
			
		/**
		 * Enqueues styles and scripts used by IssueM on the frontend
		 *
		 * @since 1.0.0
		 * @uses wp_enqueue_script() to enqueue JS files
		 * @uses wp_enqueue_style() to enqueue CSS files
		 */
		function frontend_scripts() {
			
			$settings = $this->get_settings();
			
			if ( apply_filters( 'enqueue_issuem_styles', 'true' ) ) {
		
				switch( $settings['css_style'] ) {
					
					case 'none' :
						break;
					
					case 'default' :
					default : 
						wp_enqueue_style( 'issuem_style', ISSUEM_PLUGIN_URL . '/css/issuem.css', '', ISSUEM_VERSION );
						break;
						
				}
			
			}
			
			wp_enqueue_script( 'jquery-issuem-flexslider', ISSUEM_PLUGIN_URL . '/js/jquery.flexslider-min.js', array( 'jquery' ), ISSUEM_VERSION );
			wp_enqueue_style( 'jquery-issuem-flexslider', ISSUEM_PLUGIN_URL . '/css/flexslider.css', '', ISSUEM_VERSION );
		
		}
		
		/**
		 * Gets IssueM settings
		 *
		 * @since 1.0.0
		 *
		 * @return array IssueM settings, merged with defaults.
\		 */
		function get_settings() {
			
			$defaults = array( 
								'issuem_API' 			=> '', 
								'page_for_articles'		=> 0,
								'pdf_title'				=> __( 'Download PDF', 'issuem' ),
								'pdf_only_title'		=> __( 'PDF Only', 'issuem' ),
								'pdf_open_target'		=> '_blank',
								'cover_image_width'		=> 130,
								'cover_image_height'	=> 165,
								'featured_image_width'	=> 480,
								'featured_image_height'	=> 320,
								'featured_thumb_width'	=> 150,
								'featured_thumb_height'	=> 100,
								'default_issue_image'	=> apply_filters( 'issuem_default_issue_image', ISSUEM_PLUGIN_URL . '/images/archive-image-unavailable.jpg' ),
								'custom_image_used'		=> 0,
								'show_featured_byline'	=> '',
								'show_thumbnail_byline'	=> '',
								'display_byline_as'		=> 'user_firstlast',
								'issuem_author_name'	=> '',
								'use_wp_taxonomies'		=> '',
								'article_format'		=> 	'<p class="issuem_article_category">%CATEGORY[1]%</p>' . "\n" .
															'<p><a class="issuem_article_link" href="%URL%">%TITLE%</a></p>' . "\n" .
															'<p class="issuem_article_content">%TEASER%</p>' . "\n" .
															'<p class="issuem_article_byline">%BYLINE%</p>' . "\n",
								'css_style'				=> 'default',
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
\		 */
		function update_settings( $settings ) {
		
			update_option( 'issuem', $settings );
			
		}
		
		/**
		 * Outputs the IssueM settings page
		 *
		 * @since 1.0
		 * @todo perform the save function earlier
		 */
		function settings_page() {
			
			// Get the user options
			$settings = $this->get_settings();
			
			if ( isset( $_REQUEST['remove_default_issue_image'] ) ) {
				
				wp_delete_attachment( $_REQUEST['remove_default_issue_image'] );
				
				unset( $settings['default_issue_image'] );
				unset( $settings['custom_image_used'] );
				
				$this->update_settings( $settings );
					
				$settings = $this->get_settings();
			
			}
			
			if ( isset( $_REQUEST['update_issuem_settings'] ) ) {
				
				if ( isset( $_REQUEST['issuem_API'] ) )
					$settings['issuem_API'] = $_REQUEST['issuem_API'];
					
				if ( isset( $_REQUEST['page_for_articles'] ) )
					$settings['page_for_articles'] = $_REQUEST['page_for_articles'];
					
				if ( isset( $_REQUEST['css_style'] ) )
					$settings['css_style'] = $_REQUEST['css_style'];
				
				if ( isset( $_REQUEST['pdf_title'] ) )
					$settings['pdf_title'] = $_REQUEST['pdf_title'];
				
				if ( isset( $_REQUEST['pdf_only_title'] ) )
					$settings['pdf_only_title'] = $_REQUEST['pdf_only_title'];
					
				if ( isset( $_REQUEST['pdf_open_target'] ) )
					$settings['pdf_open_target'] = $_REQUEST['pdf_open_target'];
				
				if ( isset( $_REQUEST['article_format'] ) )
					$settings['article_format'] = $_REQUEST['article_format'];
				
				if ( isset( $_REQUEST['cover_image_width'] ) )
					$settings['cover_image_width'] = $_REQUEST['cover_image_width'];
				else
					unset( $settings['cover_image_width'] );
				
				if ( isset( $_REQUEST['cover_image_height'] ) )
					$settings['cover_image_height'] = $_REQUEST['cover_image_height'];
				else
					unset( $settings['cover_image_height'] );
				
				if ( isset( $_REQUEST['featured_image_width'] ) )
					$settings['featured_image_width'] = $_REQUEST['featured_image_width'];
				else
					unset( $settings['featured_image_width'] );
				
				if ( isset( $_REQUEST['featured_image_height'] ) )
					$settings['featured_image_height'] = $_REQUEST['featured_image_height'];
				else
					unset( $settings['featured_image_height'] );
				
				if ( isset( $_REQUEST['featured_thumb_width'] ) )
					$settings['featured_thumb_width'] = $_REQUEST['featured_thumb_width'];
				else
					unset( $settings['featured_thumb_width'] );
				
				if ( isset( $_REQUEST['featured_thumb_height'] ) )
					$settings['featured_thumb_height'] = $_REQUEST['featured_thumb_height'];
				else
					unset( $settings['featured_thumb_height'] );
					
				if ( isset( $_FILES['default_issue_image'] ) && !empty( $_FILES['default_issue_image']['name'] ) ) {
		
					require_once(ABSPATH . 'wp-admin/includes/admin.php'); 
					$id = media_handle_upload( 'default_issue_image', 0 ); //post id of Client Files page  
					 
					if ( is_wp_error( $id ) ) {  
					
						$errors['upload_error'] = $id;  
						$id = false;  
						
					}
					
					list( $src, $width, $height ) = wp_get_attachment_image_src( $id, 'issuem-cover-image' );
					$settings['custom_image_used'] = $id;
					$settings['default_issue_image'] = $src;
					
				}
				
				if ( isset( $_REQUEST['show_featured_byline'] ) )
					$settings['show_featured_byline'] = $_REQUEST['show_featured_byline'];
				else
					unset( $settings['show_featured_byline'] );
				
				if ( isset( $_REQUEST['show_thumbnail_byline'] ) )
					$settings['show_thumbnail_byline'] = $_REQUEST['show_thumbnail_byline'];
				else
					unset( $settings['show_thumbnail_byline'] );
				
				if ( isset( $_REQUEST['display_byline_as'] ) )
					$settings['display_byline_as'] = $_REQUEST['display_byline_as'];
				
				if ( isset( $_REQUEST['issuem_author_name'] ) )
					$settings['issuem_author_name'] = $_REQUEST['issuem_author_name'];
				else
					unset( $settings['issuem_author_name'] );
				
				if ( isset( $_REQUEST['use_wp_taxonomies'] ) )
					$settings['use_wp_taxonomies'] = $_REQUEST['use_wp_taxonomies'];
				else
					unset( $settings['use_wp_taxonomies'] );
				
				$this->update_settings( $settings );
					
				// It's not pretty, but the easiest way to get the menu to refresh after save...
				?>
					<script type="text/javascript">
					<!--
					window.location = "<?php echo $_SERVER['PHP_SELF'] .'?post_type=article&page=issuem&settings_saved'; ?>"
					//-->
					</script>
				<?php
				
			}
			
			if ( isset( $_POST['update_issuem_settings'] ) || isset( $_GET['settings_saved'] ) ) {
				
				// update settings notification ?>
				<div class="updated"><p><strong><?php _e( 'IssueM Settings Updated.', 'issuem' );?></strong></p></div>
				<?php
				
			}
			
			// Display HTML form for the options below
			?>
			<div class=wrap>
            <div style="width:70%;" class="postbox-container">
            <div class="metabox-holder">	
            <div class="meta-box-sortables ui-sortable">
            
                <form id="issuem" method="post" action="" enctype="multipart/form-data" encoding="multipart/form-data">
            
                    <h2 style='margin-bottom: 10px;' ><?php _e( 'IssueM General Settings', 'issuem' ); ?></h2>
                    
                    <div id="api-key" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        
                        <h3 class="hndle"><span><?php _e( 'IssueM API Key', 'issuem' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <table id="issuem_api_key">
                        	<tr>
                                <th rowspan="1"> <?php _e( 'IssueM API Key', 'issuem' ); ?></th>
                                <td class="leenkme_plugin_name">
                                <input type="text" id="api" class="regular-text" name="issuem_API" value="<?php echo htmlspecialchars( stripcslashes( $settings['issuem_API'] ) ); ?>" />
                                
                                <input type="button" class="button" name="verify_issuem_api" id="verify" value="<?php _e( 'Verify IssueM API', 'issuem' ) ?>" />
                                <?php wp_nonce_field( 'verify', 'issuem_verify_wpnonce' ); ?>
                                </td>
                            </tr>
                        </table>
                                                  
                        <p class="submit">
                            <input class="button-primary" type="submit" name="update_issuem_settings" value="<?php _e( 'Save Settings', 'issuem' ) ?>" />
                        </p>
                        
                        </div>
                        
                    </div>
                    
                    <div id="modules" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        
                        <h3 class="hndle"><span><?php _e( 'IssueM Administrator Options', 'issuem' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <table id="issuem_administrator_options">
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Page for Articles', 'issuem' ); ?></th>
                                <td><?php echo wp_dropdown_pages( array( 'name' => 'page_for_articles', 'echo' => 0, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $settings['page_for_articles'] ) ); ?></td>
                            </tr>
                        
                        	<?php if ( apply_filters( 'enqueue_issuem_styles', true ) ) { ?>
                            
                        	<tr>
                                <th rowspan="1"> <?php _e( 'CSS Style', 'issuem' ); ?></th>
                                <td>
								<select id='css_style' name='css_style'>
                                <?php
								$css_styles = $this->get_css_styles();
								foreach ( $css_styles as $slug => $name ) {
									?>
									<option value='<?php echo $slug; ?>' <?php selected( $slug, $settings['css_style'] ); ?> ><?php echo $name; ?></option>
                                    <?php
								}
								?>
								</select>
                                </td>
                            </tr>
                            
                            <?php } ?>
                            
                            <tr>
                                <th rowspan="1"> <?php _e( 'PDF Download Link Title', 'issuem' ); ?></th>
                                <td><input type="text" id="pdf_title" class="regular-text" name="pdf_title" value="<?php echo htmlspecialchars( stripcslashes( $settings['pdf_title'] ) ); ?>" /></td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'PDF Only Title', 'issuem' ); ?></th>
                                <td><input type="text" id="pdf_only_title" class="regular-text" name="pdf_only_title" value="<?php echo htmlspecialchars( stripcslashes( $settings['pdf_only_title'] ) ); ?>" /></td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'PDF Link Target', 'issuem' ); ?></th>
                                <td>
								<select id='pdf_open_target' name='pdf_open_target'>
									<option value='_blank' <?php selected( '_blank', $settings['pdf_open_target'] ); ?> ><?php _e( 'Open in New Window/Tab', 'issuem' ); ?></option>
									<option value='_self' <?php selected( '_self', $settings['pdf_open_target'] ); ?> ><?php _e( 'Open in Same Window/Tab', 'issuem' ); ?></option>
								</select>
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Cover Image Size', 'issuem' ); ?></th>
                                <td>
                                <?php _e( 'Width', 'issuem' ); ?> <input type="text" id="cover_image_width" class="small-text" name="cover_image_width" value="<?php echo htmlspecialchars( stripcslashes( $settings['cover_image_width'] ) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php _e( 'Height', 'issuem' ); ?> <input type="text" id="cover_image_height" class="small-text" name="cover_image_height" value="<?php echo htmlspecialchars( stripcslashes( $settings['cover_image_height'] ) ); ?>" />px
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Featured Rotator Image Size', 'issuem' ); ?></th>
                                <td>
                                <?php _e( 'Width', 'issuem' ); ?> <input type="text" id="featured_image_width" class="small-text" name="featured_image_width" value="<?php echo htmlspecialchars( stripcslashes( $settings['featured_image_width'] ) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php _e( 'Height', 'issuem' ); ?> <input type="text" id="featured_image_height" class="small-text" name="featured_image_height" value="<?php echo htmlspecialchars( stripcslashes( $settings['featured_image_height'] ) ); ?>" />px
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Featured Thumbnail Image Size', 'issuem' ); ?></th>
                                <td>
                                <?php _e( 'Width', 'issuem' ); ?> <input type="text" id="featured_thumb_width" class="small-text" name="featured_thumb_width" value="<?php echo htmlspecialchars( stripcslashes( $settings['featured_thumb_width'] ) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php _e( 'Height', 'issuem' ); ?> <input type="text" id="featured_thumb_height" class="small-text" name="featured_thumb_height" value="<?php echo htmlspecialchars( stripcslashes( $settings['featured_thumb_height'] ) ); ?>" />px
                                </td>
                            </tr>
                            
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Default Issue Image', 'issuem' ); ?></th>
                                <td><input type="file" id="default_issue_image" class="regular-text" name="default_issue_image" value="" /><p><img src="<?php echo $settings['default_issue_image']; ?>" /></p>
                                
                                <?php if ( 0 < $settings['custom_image_used'] ) { ?>
                                <p><a href="?<?php echo http_build_query( wp_parse_args( array( 'remove_default_issue_image' => 1 ), $_GET ) ) . '">' . __( 'Remove Custom Default Issue Image', 'issuem' ); ?></a></p>
                                <?php } ?>
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Show Byline for Featured Rotator', 'issuem' ); ?></th>
                                <td><input type="checkbox" id="show_featured_byline" name="show_featured_byline" <?php checked( $settings['show_featured_byline'] || 'on' == $settings['show_featured_byline'] ); ?>" /></td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Show Byline for Featured Thumbnails', 'issuem' ); ?></th>
                                <td><input type="checkbox" id="show_thumbnail_byline" name="show_thumbnail_byline" <?php checked( $settings['show_thumbnail_byline'] || 'on' == $settings['show_thumbnail_byline'] ); ?>" /></td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Display Byline As', 'issuem' ); ?></th>
                                <td>
                                <select id="display_byline_as" name="display_byline_as" >
                                	<option value="user_firstlast" <?php selected( 'user_firstlast' == $settings['display_byline_as'] ); ?>>First & Last Name</option>
                                	<option value="user_firstname" <?php selected( 'user_firstname' == $settings['display_byline_as'] ); ?>>First Name</option>
                                	<option value="user_lastname" <?php selected( 'user_lastname' == $settings['display_byline_as'] ); ?>>Last Name</option>
                                	<option value="display_name" <?php selected( 'display_name' == $settings['display_byline_as'] ); ?>>Display Name</option>
                                </select>
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Use IssueM Author Name instead of WordPress Author', 'issuem' ); ?></th>
                                <td><input type="checkbox" id="issuem_author_name" name="issuem_author_name" <?php checked( $settings['issuem_author_name'] || 'on' == $settings['issuem_author_name'] ); ?>" /></td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Use Default WordPress Category and Tag Taxonomies', 'issuem' ); ?></th>
                                <td><input type="checkbox" id="use_wp_taxonomies" name="use_wp_taxonomies" <?php checked( $settings['use_wp_taxonomies'] || 'on' == $settings['use_wp_taxonomies'] ); ?>" /></td>
                            </tr>
                            
                        </table>
                        
                        <?php wp_nonce_field( 'issuem_general_options', 'issuem_general_options_nonce' ); ?>
                                                  
                        <p class="submit">
                            <input class="button-primary" type="submit" name="update_issuem_settings" value="<?php _e( 'Save Settings', 'issuem' ) ?>" />
                        </p>

                        </div>
                        
                    </div>
                    
                    <div id="modules" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        
                        <h3 class="hndle"><span><?php _e( 'IssueM Article Format', 'issuem' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <textarea id="article_format" class="large-text code" cols="50" rows="20" name="article_format"><?php echo htmlspecialchars( stripcslashes( $settings['article_format'] ) ); ?></textarea>
                        
                                                  
                        <p class="submit">
                            <input class="button-primary" type="submit" name="update_issuem_settings" value="<?php _e( 'Save Settings', 'issuem' ) ?>" />
                        </p>

                        </div>
                        
                    </div>
                    
                </form>
                
            </div>
            </div>
            </div>
			</div>
			<?php
			
		}

		/**
		 * Called by wp_ajax_verify action
		 * Used to verify the IssueM API key
		 *
		 * @since 1.0.0
		 */
		function issuem_api_ajax_verify() {
		
			check_ajax_referer( 'verify' );
						
			if ( isset( $_REQUEST['issuem_API'] ) ) {
						
				// POST data to send to your API
				$args = array(
					'action' 	=> 'verify-api',
					'api'		=> $_REQUEST['issuem_API']
				);
					
				// Send request for detailed information
				$response = $this->issuem_api_request( $args );
				
				die( $response->response );
		
			} else {
		
				die( __( 'Please fill in your API key.', 'issuem' ) );
		
			}
		
		}
		
		/**
		 * Outputs the IssueM settings page
		 *
		 * @since 1.0.0
		 * @uses do_action() On 'help_page' for addons
		 */
		function help_page() {
			
			// Display HTML
			?>
			<div class=wrap>
            <div style="width:70%;" class="postbox-container">
            <div class="metabox-holder">	
            <div class="meta-box-sortables ui-sortable">
        
                <h2 style='margin-bottom: 10px;' ><?php _e( 'IssueM Help', 'issuem' ); ?></h2>
                
                <div id="issuem-articles" class="postbox">
                
                    <div class="handlediv" title="Click to toggle"><br /></div>
    
                    <h3 class="hndle"><span><?php _e( '[issuem_articles] - Articles Shortcode', 'issuem' ); ?></span></h3>
                    
                    <div class="inside">
                                    
                        <table class="form-table">
                    
                            <tr>
                            
                                <td>
                                	
                                    IssueM <?php _e( 'Article Loop:', 'issuem' ); ?> <code style="font-size: 1.2em; background: #ffffe0;">[issuem_articles]</code>
                                    
                                    <p><?php _e( 'This shortcode will display the list of articles in an issue.', 'issuem' ); ?></p>
                                    
                                </td>
                                
                            </tr>
                            
                        </table>
                    
                    </div>
                    
                </div>
                
                <div id="issuem-featured-rotator" class="postbox">
                
                    <div class="handlediv" title="Click to toggle"><br /></div>
    
                    <h3 class="hndle"><span><?php _e( '[issuem_featured_rotator] - Featured Rotator Shortcode', 'issuem' ); ?></span></h3>
                    
                    <div class="inside">
                                    
                        <table class="form-table">
                    
                            <tr>
                            
                                <td>
                                                
                                    IssueM <?php _e( 'Featured Article Rotator:', 'issuem' ); ?> <code style="font-size: 1.2em; background: #ffffe0;">[issuem_featured_rotator]</code>
                                    
                                    <p><?php _e( 'This shortcode will display the list of articles in an issue.', 'issuem' ); ?></p>
                                    
                                </td>
                                
                            </tr>
                            
                        </table>
                    
                    </div>
                    
                </div>
                
                <div id="issuem-featured-thumbnails" class="postbox">
                
                    <div class="handlediv" title="Click to toggle"><br /></div>
    
                    <h3 class="hndle"><span><?php _e( '[issuem_featured_thumbnails] - Featured Thumbnails Shortcode', 'issuem' ); ?></span></h3>
                    
                    <div class="inside">
                                    
                        <table class="form-table">
                    
                            <tr>
                            
                                <td>
                                                
                                    IssueM <?php _e( 'Featured Thumbnails:', 'issuem' ); ?> <code style="font-size: 1.2em; background: #ffffe0;">[issuem_featured_thumbnails]</code>
                                    
                                    <p><?php _e( 'This shortcode will display the grid of featured article thumbnails in an issue', 'issuem' ); ?>.</p>
                                    
                                </td>
                                
                            </tr>
                            
                        </table>
                    
                    </div>
                    
                </div>
                
                <div id="issuem-featured-thumbnails" class="postbox">
                
                    <div class="handlediv" title="Click to toggle"><br /></div>
    
                    <h3 class="hndle"><span><?php _e( '[issuem_archives] - IssueM Archive Issues', 'issuem' ); ?></span></h3>
                    
                    <div class="inside">
                                    
                        <table class="form-table">
                    
                            <tr>
                            
                                <td>
                                                
                                    IssueM <?php _e( 'Archive Page:', 'issuem' ); ?>: <code style="font-size: 1.2em; background: #ffffe0;">[issuem_archives]</code>
                                    
                                    <p><?php _e( 'This shortcode will display the list of current and past issues.', 'issuem' ); ?></p>
                                                
                                                    <pre>
                                                    
<?php _e( 'Default Arguments:', 'issuem' ); ?>

orderby => 'term_id'
order => 'DESC'
limit => 0
pdf_title => IssueM <?php _e( 'Setting "PDF Title"', 'issuem' ); ?>
default_image => IssueM <?php _e( 'Setting "Default Cover Image"', 'issuem' ); ?>

<?php _e( 'Accepted Arguments:', 'issuem' ); ?>

orderby => 'term_id', 'issue_order', 'name' (for Issue ID Number, Issue Order, or Issue Name)
order => 'DESC' or 'ASC' <?php _e( '(for Descending or Ascending)', 'issuem' ); ?>
limit => <?php _e( 'Any number 0 and greater', 'issuem' ); ?>
pdf_title => '<?php _e( 'Text', 'issuem' ); ?>'
default_image => '<?php _e( 'Image URL', 'issuem' ); ?>'

<?php _e( 'Examples:', 'issuem' ); ?>

[issuem_archives orderby="issue_order"]
[issuem_archives orderby="name" order="ASC" limit=5 pdf_title="<?php _e( 'Download Now', 'issuem' ); ?>" default_image="http://yoursite.com/yourimage.jpg"]

                                                    </pre>
                                    
                                </td>
                                
                            </tr>
                            
                        </table>
                    
                    </div>
                    
                </div>
                
                <div id="issuem-featured-thumbnails" class="postbox">
                
                    <div class="handlediv" title="Click to toggle"><br /></div>
    
                    <h3 class="hndle"><span><?php _e( 'Custom Cover Image Size', 'issuem' ); ?></span></h3>
                    
                    <div class="inside">
                        
                        <p><?php _e( 'To customize the height and width of the featured images.', 'issuem' ); ?></p>
                    
                    </div>
                    
                </div>
                
                <?php do_action( 'issuem_help_page' ); ?>
                
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
		function upgrade() {
			
			$settings = $this->get_settings();
			
			if ( isset( $settings['version'] ) )
				$old_version = $settings['version'];
			else
				$old_version = 0;
				
			if ( version_compare( $old_version, '1.1.2', '<' ) ) {
				
				delete_option( 'last_issuem_dot_com_rss_item' );
				wp_clear_scheduled_hook( 'issuem_dot_com_rss_feed_check' );
				issuem_dot_com_rss_feed_check();
				
			}
			
			if ( version_compare( $old_version, '1.2.0', '<' ) )
				$this->upgrade_to_1_2_0();

			$settings['version'] = ISSUEM_VERSION;
			$this->update_settings( $settings );
			
		}
		
		/**
		 * Initialized permissions
		 *
		 * @since 1.2.0
		 */
		function upgrade_to_1_2_0() {
			
			$role = get_role('administrator');
			if ($role !== NULL)
				// Articles
				$role->add_cap('edit_article');
				$role->add_cap('read_article');
				$role->add_cap('delete_article');
				$role->add_cap('edit_articles');
				$role->add_cap('edit_others_articles');
				$role->add_cap('publish_articles');
				$role->add_cap('read_private_articles');
				$role->add_cap('delete_articles');
				$role->add_cap('delete_private_articles');
				$role->add_cap('delete_published_articles');
				$role->add_cap('delete_others_articles');
				$role->add_cap('edit_private_articles');
				$role->add_cap('edit_published_articles');
				// Issues
				$role->add_cap('manage_issuem_settings');
				$role->add_cap('manage_issues');
				$role->add_cap('manage_article_categories');
				$role->add_cap('manage_article_tags');
				$role->add_cap('edit_issues');
				$role->add_cap('edit_others_issues');
				$role->add_cap('edit_published_issues');
				$role->add_cap('publish_issues');
	
			$role = get_role('editor');
			if ($role !== NULL) {}
				// Articles
				$role->add_cap('edit_articles');
				$role->add_cap('edit_others_articles');
				$role->add_cap('edit_published_articles');
				$role->add_cap('publish_articles');
				$role->add_cap('delete_published_articles');
				$role->add_cap('delete_others_articles');
				$role->add_cap('delete_articles');
				$role->add_cap('delete_private_articles');
				$role->add_cap('edit_private_articles');
				$role->add_cap('read_private_articles');
				// Issues
				$role->add_cap('manage_issues');
				$role->add_cap('manage_article_categories');
				$role->add_cap('manage_article_tags');
				$role->add_cap('edit_issues');
				$role->add_cap('edit_others_issues');
				$role->add_cap('edit_published_issues');
				$role->add_cap('publish_issues');
	
			$role = get_role('author');
			if ($role !== NULL) {}
				// Articles
				$role->add_cap('edit_articles');
				$role->add_cap('edit_published_articles');
				$role->add_cap('publish_articles');
				$role->add_cap('delete_articles');
				$role->add_cap('delete_published_articles');
				// Issues
				$role->add_cap('edit_issues');
				$role->add_cap('edit_published_issues');
				$role->add_cap('publish_issues');
	
			$role = get_role('contributor');
			if ($role !== NULL) {}
				// Articles
				$role->add_cap('edit_articles');
				$role->add_cap('delete_articles');
				// Issues
				$role->add_cap('edit_issues');
				
		}
		
		/**
		 * Gets IssueM Plugin information for WordPress Updates
		 *
		 * @since 1.0.0
		 *
		 * @param bool $false
		 * @param string $action
		 * @param array $args Array of arguments to pass to the API request
		 * @return object $response
		 */
		function issuem_plugins_api( $false, $action, $args ) {
		
			$plugin_slug = ISSUEM_PLUGIN_SLUG;
			
			// Check if this plugins API is about this plugin
			if( !isset( $args->slug ) || $args->slug != $plugin_slug )
				return $false;
				
			// POST data to send to your API
			$args = array(
				'action'		=> 'get-plugin-information',
				'plugin_slug' 	=> $plugin_slug,
			);
				
			// Send request for detailed information
			$response = $this->issuem_api_request( $args );
				
			return $response;
			
		}
		
		/**
		 * Gets IssueM Plugin information for WordPress Updates
		 *
		 * @since 1.0.0
		 *
		 * @param object $transient
		 * @return object $transient
		 */
		function issuem_update_plugins( $transient ) {
			
			// Check if the transient contains the 'checked' information
    		// If no, just return its value without hacking it
			if ( empty( $transient->checked ) )
				return $transient;
		
			// The transient contains the 'checked' information
			// Now append to it information form your own API
			$plugin_slug = ISSUEM_PLUGIN_SLUG;
				
			// POST data to send to your API
			$args = array(
				'action' 		=> 'check-latest-version',
				'plugin_slug' 	=> $plugin_slug
			);
			
			// Send request checking for an update
			$response = $this->issuem_api_request( $args );
							
			// If there is a new version, modify the transient
			if ( isset( $response->new_version ) )
				if( version_compare( $response->new_version, $transient->checked[ISSUEM_PLUGIN_BASENAME], '>' ) )
					$transient->response[ISSUEM_PLUGIN_BASENAME] = $response;
				
			return $transient;
			
		}
		
		/**
		 * API Request sent and processed by the IssueM API
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Arguments to send to the IssueM API
		 */
		function issuem_api_request( $args ) {
			
			$settings = $this->get_settings();
			
			$args['site'] = network_site_url();
			
			if ( !isset( $args['api'] ) )
				$args['api'] = apply_filters( 'issuem_api_key', $settings['issuem_API'] );
			
			// Send request									
			$request = wp_remote_post( ISSUEM_API_URL, array( 'body' => $args ) );
			
			if ( is_wp_error( $request ) || 200 != wp_remote_retrieve_response_code( $request ) )
				return false;
				
			$response = unserialize( wp_remote_retrieve_body( $request ) );
									
			$this->api_status( $response );
			
			if ( is_object( $response ) )
				return $response;
			else
				return false;

		}
		
		/**
		 * Verify the API status reported back to IssueM
		 *
		 * @since 1.0.0
		 *
		 * @param object $response WordPress remote query body
		 */
		function api_status( $response ) {
		
			if ( 1 < $response->account_status ) {
				
				update_option( 'issuem_api_error_received', true );
				update_option( 'issuem_api_error_message', $response->response );
				
			} else {
			
				delete_option( 'issuem_api_error_received' );
				delete_option( 'issuem_api_error_message' );
				delete_option( 'issuem_api_error_message_version_dismissed' );
				
			}
			
		}
		
		/**
		 * Returns the style available with IssueM
		 *
		 * @since 1.0.0
		 * @uses apply_filters on 'issuem_css_styles' hook, for extending IssueM
		 */
		function get_css_styles() {
		
			$styles = array(
				'default'	=> __( 'Default', 'issuem' ),
				'none'		=> __( 'None', 'issuem' ),
			);
			
			return apply_filters( 'issuem_css_styles', $styles );
			
		}
		
		/**
		 * If an IssueM notification is set, display it.
		 * Called by teh admin_notices hook
		 *
		 * @since 1.0.0
		 */
		function issuem_notification() {
			
			if ( isset( $_REQUEST['remove_issuem_api_error_message'] ) ) {
				
				delete_option( 'issuem_api_error_message' );
				update_option( 'issuem_api_error_message_version_dismissed', ISSUEM_VERSION );
				
			}
		
			if ( ( $notification = get_option( 'issuem_api_error_message' ) ) && version_compare( get_option( 'issuem_api_error_message_version_dismissed' ), ISSUEM_VERSION, '<' ) )
				echo '<div class="update-nag">' . $notification . '<br /><a href="' . add_query_arg( 'remove_issuem_api_error_message', true ) . '">' . __( 'Dismiss', 'issuem' ) . '</a></div>';
		 
		}
		
	}
	
}