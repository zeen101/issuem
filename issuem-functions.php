<?php
/**
 * Misc helper functions for IssueM
 *
 * @package IssueM
 * @since 1.0.0
 */

if ( !function_exists( 'get_newest_issuem_issue_id' ) ) { 

	/**
	 * Get newest IssueM issue
	 *
	 * @since 1.0.0
	 *
	 * @param string $orderby 
	 * @return int $id
	 */
	function get_newest_issuem_issue_id( $orderby = 'issue_order' ) {
		
		$issues = array();
		$count = 0;
		
		$issuem_issues = get_terms( 'issuem_issue' );
						
		foreach ( $issuem_issues as $issue ) {
				
			$issue_meta = get_option( 'issuem_issue_' . $issue->term_id . '_meta' );
			
			// If issue is not a Draft, add it to the archive array;
			if ( !empty( $issue_meta ) && !empty( $issue_meta['issue_status'] ) 
				&& ( 'Live' === $issue_meta['issue_status'] || current_user_can( apply_filters( 'see_issuem_draft_issues', 'manage_issues' ) ) ) ) {
				
				switch( $orderby ) {
					
					case "issue_order":
						if ( !empty( $issue_meta['issue_order'] ) )
							$issues[ $issue_meta['issue_order'] ] = $issue->term_id;
						else
							$issues[ '-' . ++$count ] = $issue->term_id;
							
						break;
						
					case "name":
						$issues[ $issue_meta['name'] ] = $issue->term_id;
						break;
					
					case "term_id":
						$issues[ $issue->term_id ] = $issue->term_id;
						break;
					
				}
					 
			} else {
				$issues[ '-' . ++$count ] = $issue->term_id;
			}
			
		}
		
		krsort( $issues );
		
		return array_shift( $issues );
		
	}
	
}

if ( !function_exists( 'get_issuem_issue_meta' ) ) { 
	
	/**
	 * Get issue meta information, assumes latest issue if no id supplied
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Issue ID 
	 * @return mixed Value set for the issue meta option.
	 */
	function get_issuem_issue_meta( $id = false ) {
	
		if ( !$id ) {
				
			return get_option( 'issuem_issue_' . get_newest_issuem_issue_id() . '_meta' );
			
		} else {
		
			return get_option( 'issuem_issue_' . $id . '_meta' );
			
		}
		
	}

}

if ( !function_exists( 'get_issuem_issue_cover' ) ) { 
	
	/**
	 * Get issue cover image, assumes latest issue if no id supplied
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Issue ID 
	 * @return string URL of cover image
	 */
	function get_issuem_issue_cover( $id = false ) {
	
		if ( !$id ) {
					
			$issue_meta = get_option( 'issuem_issue_' . get_newest_issuem_issue_id() . '_meta' );
			
			return $issue_meta['cover_image'];
			
		} else {
	
			$issue_meta = get_option( 'issuem_issue_' . $id . '_meta' );
			
			return $issue_meta['cover_image'];
			
		}
		
	}

}

if ( !function_exists( 'get_issuem_issue_slug' ) ) { 
	
	/**
	 * Get issue slug, assumes latest issue if no id supplied
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Issue ID 
	 * @return string issue slug
	 */
	function get_issuem_issue_slug( $id = false ) {
	
		if ( !$id ) {
			
			$issue = get_term_by( 'id', get_newest_issuem_issue_id(), 'issuem_issue' );
						
		} else {
	
			$issue = get_term_by( 'id', $id, 'issuem_issue' );
			
		}
		
		return ( ( is_object( $issue ) && !empty( $issue->slug ) ) ? $issue->slug : '' );
		
	}

}

if ( !function_exists( 'get_issuem_issue_title' ) ) { 
	
	/**
	 * Get issue title, assumes latest issue if no id supplied
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Issue ID 
	 * @return string issue name
	 */
	function get_issuem_issue_title( $id = false ) {
	
		if ( !$id ) {
	
			$issue = get_term_by( 'id', get_newest_issuem_issue_id(), 'issuem_issue' );
			
			return $issue->name;
			
		} else {
	
			$issue = get_term_by( 'id', $id, 'issuem_issue' );
			
			return $issue->name;
			
		}
		
	}

}

if ( !function_exists( 'get_active_issuem_issue' ) ) { 

	/**
	 * Gets active issue, set by latest issue or by cookie if user selects a specific issue
	 *
	 * @since 1.0.0
	 *
	 * @return string issue slug
	 */
	function get_active_issuem_issue() {
	
		if ( !empty( $_COOKIE['issuem_issue'] ) )
			return $_COOKIE['issuem_issue'];
		else if ( !empty( $_GET['issue'] ) )
			return $_GET['issue'];
		else
			return get_issuem_issue_slug();
		
	}

}

if ( !function_exists( 'set_issuem_cookie' ) ) { 

	/**
	 * Sets IssueM issue cookie
	 *
	 * @since 1.0.0
	 */
	function set_issuem_cookie() {
		
		if ( !empty( $_GET['issue'] ) ) {
		
			$_COOKIE['issuem_issue'] = $_GET['issue'];
			setcookie( 'issuem_issue', $_GET['issue'], time() + 3600, '/' );
			
		} else {
		
			global $post;
			
			$issuem_settings = get_issuem_settings();
				
			if ( is_page( $issuem_settings['page_for_articles'] ) ) {
	
				$_COOKIE['issuem_issue'] = get_issuem_issue_slug();
				setcookie( 'issuem_issue', $_COOKIE['issuem_issue'], time() + 3600, '/' );
			
			} else if ( !empty( $post->post_type ) && 'article' != $post->post_type ) {
			
				unset( $_COOKIE['issuem_issue'] );
				setcookie( 'issuem_issue', '', 1, '/' );
				
			} else if ( is_single() && !empty( $post->post_type ) && 'article' == $post->post_type ) {
			
				$terms = wp_get_post_terms( $post->ID, 'issuem_issue' );
				if ( !empty( $terms ) ) {
					$_COOKIE['issuem_issue'] = $terms[0]->slug;
					setcookie( 'issuem_issue', $_COOKIE['issuem_issue'], time() + 3600, '/' );
				}		
				
			} else if ( taxonomy_exists( 'issuem_issue' ) ) {
				
				$_COOKIE['issuem_issue'] = get_query_var( 'issuem_issue' );
				setcookie( 'issuem_issue', $_COOKIE['issuem_issue'], time() + 3600, '/' );

			}
			
		}
	
	}
	add_action( 'wp', 'set_issuem_cookie' );

}

if ( !function_exists( 'issuem_replacements_args' ) ) {

	/**
	 * Replaces variables with WordPress content
	 *
	 * @since 1.0.0
	 *
	 * @param int $id User ID
	 */
	function issuem_replacements_args( $string, $post ) {
		
		$issuem_settings = get_issuem_settings();
		
		if ( !empty( $issuem_settings['use_wp_taxonomies'] ) ) {
			
			$tags = 'post_tag';
			$cats = 'category';	
			
		} else {
			
			$tags = 'issuem_issue_tags';
			$cats = 'issuem_issue_categories';
			
		}
		
		$string = str_ireplace( '%TITLE%', get_the_title(), $string );
		$string = str_ireplace( '%URL%', apply_filters( 'issuem_article_url', get_permalink( $post->ID ), $post->ID ), $string );
		
		if ( preg_match( '/%CATEGORY\[?(\d*)\]?%/i', $string, $matches ) ) {
			
			$post_cats = get_the_terms( $post->ID, $cats );
			$categories = '';
			
			if ( $post_cats && !is_wp_error( $post_cats ) ) :
			
				if ( !empty( $matches[1] ) )
					$max_cats = $matches[1];
				else
					$max_cats = 0;
					
				$cat_array = array();

				$count = 1;
				foreach ( $post_cats as $post_cat ) {
					
					$cat_array[] = $post_cat->name;
					
					if ( 0 != $max_cats && $max_cats <= $count )
						break;
						
					$count++;
					
				}
						
				$categories = join( ", ", $cat_array );
					
			endif;
				
			$string = preg_replace( '/%CATEGORY\[?(\d*)\]?%/i', $categories, $string );	
					
		}
		
		if ( preg_match( '/%TAG\[?(\d*)\]?%/i', $string, $matches ) ) {
			
			$post_tags = get_the_terms( $post->ID, $tags );
			$tag_string = '';
			
			if ( $post_tags && !is_wp_error( $post_tags ) ) :
			
				if ( !empty( $matches[1] ) )
					$max_tags = $matches[1];
				else
					$max_tags = 0;	
					
				$cat_array = array();

				$count = 1;
				foreach ( $post_tags as $post_tag ) {
					
					$cat_array[] = $post_tag->name;
					
					if ( 0 != $max_tags && $max_tags <= $count )
						break;
						
					$count++;
					
				}
						
				$tag_string = join( ", ", $cat_array );
					
			endif;
				
			$string = preg_replace( '/%TAG\[?(\d*)\]?%/i', $tag_string, $string );	
					
		}
		
		if ( preg_match( '/%TEASER%/i', $string, $matches ) ) {
			
			if ( $teaser = get_post_meta( $post->ID, '_teaser_text', true ) ) 
				$string = preg_replace( '/%TEASER%/i', $teaser, $string );	
			else
				$string = preg_replace( '/%TEASER%/i', '%EXCERPT%', $string );	// If no Teaser Text exists, try to get an excerpt
					
		}
		
		if ( preg_match( '/%EXCERPT\[?(\d*)\]?%/i', $string, $matches ) ) {
			
			if ( empty( $post->post_excerpt ) )
				$excerpt = get_the_content();
			else
				$excerpt = $post->post_excerpt;
			
			$excerpt = strip_shortcodes( $excerpt );
			$excerpt = apply_filters( 'the_content', $excerpt );
			$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
			
			if ( !empty( $matches[1] ) )
				$excerpt_length = $matches[1];
			else
				$excerpt_length = apply_filters('excerpt_length', 55);
					
			$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
			$excerpt = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
				
			$string = preg_replace( '/%EXCERPT\[?(\d*)\]?%/i', $excerpt, $string );	
					
		}
		
		if ( preg_match( '/%CONTENT%/i', $string, $matches ) ) {
		
			$content = get_the_content();
			$content = apply_filters( 'the_content', $content );
    			$content = str_replace( ']]>', ']]&gt;', $content );
			$string = preg_replace( '/%CONTENT%/i', $content, $string );	
					
		}
		
		if ( preg_match( '/%FEATURE_IMAGE%/i', $string, $matches ) ) {
		
			$image = get_the_post_thumbnail( $post->ID );
			$string = preg_replace( '/%FEATURE_IMAGE%/i', $image, $string );	
					
		}
		
		if ( preg_match( '/%ISSUEM_FEATURE_THUMB%/i', $string, $matches ) ) {
		
			$image = get_the_post_thumbnail( $post->ID, 'issuem-featured-thumb-image' );
			$string = preg_replace( '/%ISSUEM_FEATURE_THUMB%/i', $image, $string );	
					
		}
		
		if ( preg_match( '/%BYLINE%/i', $string, $matches ) ) {

			$author_name = get_issuem_author_name( $post );
			
			$byline = sprintf( __( 'By %s', 'issuem' ), apply_filters( 'issuem_author_name', $author_name, $post->ID ) );
				
			$string = preg_replace( '/%BYLINE%/i', $byline, $string );	
					
		}

		if ( preg_match( '/%DATE%/i', $string, $matches ) ) {

			$post_date = get_the_date( '', $post->ID );
			$string = preg_replace( '/%DATE%/i', $post_date, $string );	
					
		}
		
		$string = apply_filters( 'issuem_custom_replacement_args', $string, $post );
		
		return stripcslashes( $string );
		
	}

}

if ( !function_exists( 'get_issuem_author_name' ) ) {

	/**
	 * Function to get Article's Author Name
	 *
	 * @since 1.0.0
	 *
	 * @param object WordPress Post/Article object
	 * @return string Value set for the issuem options.
	 */
	function get_issuem_author_name( $article ) {
		
		$issuem_settings = get_issuem_settings();
	
		if ( !empty( $issuem_settings['issuem_author_name'] ) ) {
			
			$author_name = get_post_meta( $article->ID, '_issuem_author_name', true );
		
		} else {
		
			if ( 'user_firstlast' == $issuem_settings['display_byline_as'] ) {
				
				if ( ( $first_name = get_the_author_meta( 'user_firstname', $article->post_author ) ) && ( $last_name = get_the_author_meta( 'user_lastname', $article->post_author ) ) )
					$author_name = $first_name . ' ' . $last_name;
				else
					$author_name = '';
			
			} else {
				
				$author_name = get_the_author_meta( $issuem_settings['display_byline_as'], $article->post_author );
						
			}
			
			$author_name = ( !empty( $author_name ) ) ? $author_name : get_the_author_meta( 'display_name', $article->post_author );
				
			$author_name = '<a class="url fn n" href="' . esc_url( get_author_posts_url( $article->post_author ) ) . '" title="' . esc_attr( $author_name ) . '" rel="me">' . $author_name . '</a>';
			
		}
		
		return $author_name;
		
	}
	
}

if ( !function_exists( 'get_issuem_settings' ) ) {

	/**
	 * Helper function to get IssueM settings for current site
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Value set for the issuem options.
	 */
	function get_issuem_settings() {
	
		global $dl_plugin_issuem;
		
		return $dl_plugin_issuem->get_settings();
		
	}
	
}

if ( !function_exists( 'update_issuem_settings' ) ) {

	/**
	 * Helper function to get IssueM settings for current site
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Value set for the issuem options.
	 */
	function update_issuem_settings( $settings ) {
	
		global $dl_plugin_issuem;
		
		$dl_plugin_issuem->update_settings( $settings );
		
	}
	
}

if ( !function_exists( 'default_issue_content_filter' ) ) {

	/**
	 * Default content filter, sets IssueM Page for Articles to default shortcode content if no content exists for page
	 *
	 * @since 1.0.7
	 *
	 * @return string new content.
	 */
	function default_issue_content_filter( $content ) {
		
		global $post;
		
		$issuem_settings = get_issuem_settings();
		
		if ( $post->ID == $issuem_settings['page_for_articles'] && empty( $content ) ) 
			$content = '[issuem_featured_rotator] [issuem_featured_thumbnails max_images="3"] [issuem_articles]';
		else if ( $post->ID == $issuem_settings['page_for_archives'] && empty( $content ) )
			$content = '[issuem_archives orderby="issue_order"]';
		
		return $content;
		
	}
	add_filter( 'the_content', 'default_issue_content_filter', 5 );
	
}

if ( !function_exists( 'issuem_dot_com_rss_feed_check' ) ) {

	/**
	 * Check issuem.com for new RSS items in the buy-news category feed, to update clients of latest IssueM news
	 *
	 * @since 1.1.1
	 */
	function issuem_dot_com_rss_feed_check() {
			
		include_once( ABSPATH . WPINC . '/feed.php' );
	
		$output = '';
		$feedurl = 'http://issuem.com/category/buyer-news/feed';
	
		$rss = fetch_feed( $feedurl );
	
		if ( $rss && !is_wp_error( $rss ) ) {
	
			$rss_items = $rss->get_items( 0, 1 );
	
			foreach ( $rss_items as $item ) {
	
				$last_rss_item = get_option( 'last_issuem_dot_com_rss_item' );
				
				$latest_rss_item = '<a href="' . $item->get_permalink() . '" target="_blank">' . esc_html( $item->get_title() ) . '</a> - ' . $item->get_description() . '... <a href="' . $item->get_permalink() . '" target="_blank">read more</a>';
	
				if ( $last_rss_item !== $latest_rss_item )
					update_option( 'last_issuem_dot_com_rss_item', $latest_rss_item );
	
			}
	
		}
				
	}
	add_action( 'issuem_dot_com_rss_feed_check', 'issuem_dot_com_rss_feed_check' );
	
	if ( !wp_next_scheduled( 'issuem_dot_com_rss_feed_check' ) )
		wp_schedule_event( time(), 'daily', 'issuem_dot_com_rss_feed_check' );
	
}

if ( !function_exists( 'issuem_api_request' ) ) { 

	/**
	 * Helper function used to send API requests to IssueM.com
	 *
	 * HT: Glenn Ansley @ iThemes.com
	 *
	 * @since 1.2.0
	 *
	 * @param string $action Action to pass to API request
	 * @param array $args Arguments to pass to API request
	 */
    function issuem_api_request( $action, $args ) { 
	
		global $dl_plugin_issuem;
	
		return $dl_plugin_issuem->issuem_api_request( $action, $args );
	
    }   
	
}

if ( !function_exists( 'wp_print_r' ) ) { 

	/**
	 * Helper function used for printing out debug information
	 *
	 * HT: Glenn Ansley @ iThemes.com
	 *
	 * @since 1.1.6
	 *
	 * @param int $args Arguments to pass to print_r
	 * @param bool $die TRUE to die else FALSE (default FALSE)
	 */
    function wp_print_r( $args, $die = false ) { 
	
        $echo = '<pre>' . print_r( $args, true ) . '</pre>';
		
        if ( $die ) die( $echo );
        	else echo $echo;
		
    }   
	
}

if ( !function_exists( 'issuem_dropdown_categories' ) ) {
	
	/**
	 * Display or retrieve the HTML dropdown list of article categories.
	 * Adapted from WordPress' "wp_dropdown_categories"
	 *
	 * The list of arguments is below:
	 *     'show_option_all' (string) - Text to display for showing all categories.
	 *     'show_option_none' (string) - Text to display for showing no categories.
	 *     'orderby' (string) default is 'ID' - What column to use for ordering the
	 * categories.
	 *     'order' (string) default is 'ASC' - What direction to order categories.
	 *     'show_count' (bool|int) default is 0 - Whether to show how many posts are
	 * in the category.
	 *     'hide_empty' (bool|int) default is 1 - Whether to hide categories that
	 * don't have any posts attached to them.
	 *     'child_of' (int) default is 0 - See {@link get_categories()}.
	 *     'exclude' (string) - See {@link get_categories()}.
	 *     'echo' (bool|int) default is 1 - Whether to display or retrieve content.
	 *     'depth' (int) - The max depth.
	 *     'tab_index' (int) - Tab index for select element.
	 *     'name' (string) - The name attribute value for select element. Defaults to issuem_issue_cat.
	 *     'id' (string) - The ID attribute value for select element. Defaults to name if omitted.
	 *     'class' (string) - The class attribute value for select element.
	 *     'selected' (int) - Which category ID is selected.
	 *     'taxonomy' (string) - The name of the taxonomy to retrieve. Defaults to issuem_issue_categories.
	 *
	 * The 'hierarchical' argument, which is disabled by default, will override the
	 * depth argument, unless it is true. When the argument is false, it will
	 * display all of the categories. When it is enabled it will use the value in
	 * the 'depth' argument.
	 *
	 * @since 1.2.6 
	 *
	 * @param string|array $args Optional. Override default arguments.
	 * @return string HTML content only if 'echo' argument is 0.
	 */
	function issuem_dropdown_categories( $args = '' ) {
		$defaults = array(
			'show_option_all' => '', 'show_option_none' => '',
			'orderby' => 'id', 'order' => 'ASC',
			'show_count' => 0,
			'hide_empty' => 1, 'child_of' => 0,
			'exclude' => '', 'echo' => 1,
			'selected' => 0, 'hierarchical' => 0,
			'name' => 'issuem_issue_cat', 'id' => '',
			'class' => 'postform', 'depth' => 0,
			'tab_index' => 0, 'taxonomy' => 'issuem_issue_categories',
			'hide_if_empty' => false
		);
	
		$defaults['selected'] = ( is_category() ) ? get_query_var( 'cat' ) : 0;
	
		// Back compat.
		if ( isset( $args['type'] ) && 'link' == $args['type'] ) {
			_deprecated_argument( __FUNCTION__, '3.0', '' );
			$args['taxonomy'] = 'link_category';
		}
	
		$r = wp_parse_args( $args, $defaults );
	
		if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
			$r['pad_counts'] = true;
		}
	
		extract( $r );
	
		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )
			$tab_index_attribute = " tabindex=\"$tab_index\"";
	
		$categories = get_terms( $taxonomy, $r );
		$name = esc_attr( $name );
		$class = esc_attr( $class );
		$id = $id ? esc_attr( $id ) : $name;
	
		if ( ! $r['hide_if_empty'] || ! empty($categories) )
			$output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
		else
			$output = '';
	
		if ( empty($categories) && ! $r['hide_if_empty'] && !empty($show_option_none) ) {
			$show_option_none = apply_filters( 'list_cats', $show_option_none );
			$output .= "\t<option value='-1' selected='selected'>$show_option_none</option>\n";
		}
	
		if ( ! empty( $categories ) ) {
	
			if ( $show_option_all ) {
				$show_option_all = apply_filters( 'list_cats', $show_option_all );
				$selected = ( '0' === strval($r['selected']) ) ? " selected='selected'" : '';
				$output .= "\t<option value='0'$selected>$show_option_all</option>\n";
			}
	
			if ( $show_option_none ) {
				$show_option_none = apply_filters( 'list_cats', $show_option_none );
				$selected = ( '-1' === strval($r['selected']) ) ? " selected='selected'" : '';
				$output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
			}
	
			if ( $hierarchical )
				$depth = $r['depth'];  // Walk the full depth.
			else
				$depth = -1; // Flat.
	
			$output .= walk_issuem_category_dropdown_tree( $categories, $depth, $r );
		}
	
		if ( ! $r['hide_if_empty'] || ! empty($categories) )
			$output .= "</select>\n";
	
		$output = apply_filters( 'issuem_dropdown_cats', $output );
	
		if ( $echo )
			echo $output;
	
		return $output;
	}

}

if ( !function_exists( 'walk_issuem_category_dropdown_tree' ) ) {
		
	/**
	 * Retrieve HTML dropdown (select) content for category list.
	 * Adapted from WordPress' "walk_category_dropdown_tree"
	 *
	 * @uses Walker_IssueMCategoryDropdown to create HTML dropdown content.
	 * @since 1.2.6 
	 * @see Walker_IssueMCategoryDropdown::walk() for parameters and return description.
	 */
	function walk_issuem_category_dropdown_tree() {
		$args = func_get_args();
		// the user's options are the third parameter
		if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
			$walker = new Walker_IssueMCategoryDropdown;
		else
			$walker = $args[2]['walker'];
	
		return call_user_func_array(array( &$walker, 'walk' ), $args );
	}

}

if ( !function_exists( 'get_issuem_article_excerpt' ) ) { 
	
	/**
	 * Get article excerpt by id, for use outside of the loop
	 *
	 * @since 1.2.12
	 *
	 * @param int $id Article ID 
	 * @return excerpt for the article
	 */
	function get_issuem_article_excerpt( $id = false ) {
	
		if ( !$id ) {
				
			return;
			
		} else {

			$the_article = get_post($id);
			$the_excerpt = $the_article->post_excerpt;

			return $the_excerpt;
			
		}
		
	}

}
