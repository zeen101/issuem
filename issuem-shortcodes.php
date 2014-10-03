<?php
/**
 * Registers IssueM class for setting up IssueM shortcodes
 *
 * @package IssueM
 * @since 1.0.0
 */
	
if ( !function_exists( 'do_issuem_articles' ) ) {
	
	/**
	 * Outputs Article HTML from shortcode call
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of IssueM Articles
	 */
	function do_issuem_articles( $atts, $article_format = NULL ) {
		
		global $post;
		
		$issuem_settings = get_issuem_settings();
		$results = '';
		$articles = array();
		$post__in = array();
		
		$defaults = array(
			'posts_per_page'    	=> -1,
			'offset'            	=> 0,
			'orderby'           	=> 'menu_order',
			'order'             	=> 'DESC',
			'article_format'		=> empty( $article_format ) ? $issuem_settings['article_format'] : $article_format,
			'show_featured'			=> 1,
			'issue'					=> get_active_issuem_issue(),
			'article_category'		=> 'all',
			'use_category_order'	=> 'false',
		);
	
		// Merge defaults with passed atts
		// Extract (make each array element its own PHP var
		extract( shortcode_atts( $defaults, $atts ) );
		
		$args = array(
			'posts_per_page'	=> $posts_per_page,
			'offset'			=> $offset,
			'post_type'			=> 'article',
			'orderby'			=> $orderby,
			'order'				=> $order
		);
		
		if ( !$show_featured ) {
			
			$args['meta_query'] = array(
									'relation' => 'AND',
									array(
										'key' => '_featured_rotator',
										'compare' => 'NOT EXISTS'
									),
									array(
										'key' => '_featured_thumb',
										'compare' => 'NOT EXISTS'
									)
								);
			
		}
	
		$issuem_issue = array(
			'taxonomy' 	=> 'issuem_issue',
			'field' 	=> 'slug',
			'terms' 	=> $issue
		);
		
		$args['tax_query'] = array(
			$issuem_issue
		);
		
		if ( !empty( $issuem_settings['use_wp_taxonomies'] ) ) 
			$cat_type = 'category';
		else
			$cat_type = 'issuem_issue_categories';
			
		if ( 'true' === $use_category_order && 'issuem_issue_categories' === $cat_type ) {

			$count = 0;
			
			if ( 'all' === $article_category ) {
			
				$all_terms = get_terms( 'issuem_issue_categories' );
				
				foreach( $all_terms as $term ) {
				
					$issue_cat_meta = get_option( 'issuem_issue_categories_' . $term->term_id . '_meta' );
						
					if ( !empty( $issue_cat_meta['category_order'] ) )
						$terms[ $issue_cat_meta['category_order'] ] = $term->slug;
					else
						$terms[ '-' . ++$count ] = $term->slug;
						
				}
				
			} else {
			
				foreach( split( ',', $article_category ) as $term_slug ) {
					
					$term = get_term_by( 'slug', $term_slug, 'issuem_issue_categories' );
				
					$issue_cat_meta = get_option( 'issuem_issue_categories_' . $term->term_id . '_meta' );
						
					if ( !empty( $issue_cat_meta['category_order'] ) )
						$terms[ $issue_cat_meta['category_order'] ] = $term->slug;
					else
						$terms[ '-' . ++$count ] = $term->slug;
						
				}
			
			}
			
			krsort( $terms );
			$articles = array();
			
			foreach( $terms as $term ) {
			
				$category = array(
					'taxonomy' 	=> $cat_type,
					'field' 	=> 'slug',
					'terms' 	=> $term,
				);	
				
				$args['tax_query'] = array(
					'relation'	=> 'AND',
					$issuem_issue,
					$category
				);
				
				$articles = array_merge( $articles, get_posts( $args ) );
				
			}
		
			//And we want all articles not in a category
			$category = array(
				'taxonomy' 	=> $cat_type,
				'field'		=> 'slug',
				'terms'		=> $terms, 
				'operator'	=> 'NOT IN',
			);

			$args['tax_query'] = array(
                               'relation'      => 'AND',
                                $issuem_issue,
                                $category
                        );

                        $articles = array_merge( $articles, get_posts( $args ) );

			//Now we need to get rid of duplicates (assuming an article is in more than one category
			if ( !empty( $articles ) ) {
				
				foreach( $articles as $article ) {
				
					$post__in[] = $article->ID;
					
				}
				
				$args['post__in']	= array_unique( $post__in );
				$args['orderby']	= 'post__in';
				unset( $args['tax_query'] );
					
				$articles = get_posts( $args );
			
			}
			
		} else {
			
			if ( !empty( $article_category ) && 'all' !== $article_category ) {
					
				$category = array(
					'taxonomy' 	=> $cat_type,
					'field' 	=> 'slug',
					'terms' 	=> split( ',', $article_category ),
				);	
				
				$args['tax_query'] = array(
					'relation'	=> 'AND',
					$issuem_issue,
					$category
				);
				
			}
				
			$articles = get_posts( $args );
			
		}
		
		$results .= '<div class="issuem_articles_shortcode">';
	
		if ( $articles ) : 
		
			$old_post = $post;
			
			foreach( $articles as $article ) {
				
				$post = $article;
				setup_postdata( $article );
				
				$results .= '<div class="issuem_article article-' . $article->ID . '">';
				$results .= "\n" . issuem_replacements_args( $article_format, $post ) . "\n";
				$results .= '</div>';
			
			}
			
			if ( get_option( 'issuem_api_error_received' ) )
				$results .= '<div class="api_error"><p><a href="http://issuem.com/" target="_blank">' . __( 'Issue Management by ', 'issuem' ) . 'IssueM</a></div>';
		
			$post = $old_post;
	
		else :
	
			$results .= apply_filters( 'issuem_no_articles_found_shortcode_message', '<h1 class="issuem-entry-title no-articles-found">' . __( 'No articles Found', 'issuem' ) . '</h1>' );
	
		endif;
		
		$results .= '</div>';
		
		wp_reset_postdata();
		
		return $results;
		
	}
	add_shortcode( 'issuem_articles', 'do_issuem_articles' );

}

if ( !function_exists( 'do_issuem_title' ) ) {
	
	/**
	 * Outputs Issue Title HTML from shortcode call
	 *
	 * @since 1.1.8
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of Issue Title
	 */
	function do_issuem_title( $atts ) {
		
		$issuem_settings = get_issuem_settings();
		
		$defaults = array(
			'issue' => get_active_issuem_issue(),
			'field'	=> 'slug'
		);
	
		// Merge defaults with passed atts
		// Extract (make each array element its own PHP var
		extract( shortcode_atts( $defaults, $atts ) );
		
		$term = get_term_by( $field, $issue, 'issuem_issue' );
		
		return '<div class="issuem_title">' . $term->name . '</div>';
		
	}
	add_shortcode( 'issuem_issue_title', 'do_issuem_title' );

}
	
if ( !function_exists( 'do_issuem_archives' ) ) {
	
	/**
	 * Outputs Issue Archives HTML from shortcode call
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of Issue Archives
	 */
	function do_issuem_archives( $atts ) {
		
		$issuem_settings = get_issuem_settings();
		
		$defaults = array(
							'orderby' 		=> 'issue_order',
							'order'			=> 'DESC',
							'limit'			=> 0,
							'pdf_title'		=> $issuem_settings['pdf_title'],
							'default_image'	=> $issuem_settings['default_issue_image'],
							'args'			=> array( 'hide_empty' => 0 ),
						);
		extract( shortcode_atts( $defaults, $atts ) );
		
		if ( is_string( $args ) ) {
			$args = str_replace( '&amp;', '&', $args );
			$args = str_replace( '&#038;', '&', $args );
		}
		
		$args = apply_filters( 'do_issuem_archives_get_terms_args', $args );
		$issuem_issues = get_terms( 'issuem_issue', $args );
		$archives = array();
		$archives_no_issue_order = array();
		
		foreach ( $issuem_issues as $issue ) {
		
			$issue_meta = get_option( 'issuem_issue_' . $issue->term_id . '_meta' );
			
			// If issue is not a Draft, add it to the archive array;
			if ( !empty( $issue_meta['issue_status'] ) && ( 'Draft' !== $issue_meta['issue_status'] || current_user_can( apply_filters( 'see_issuem_draft_issues', 'manage_issues' ) ) ) ) {
			
				switch( $orderby ) {
					
					case "issue_order":
						if ( !empty( $issue_meta['issue_order'] ) )
							$archives[ $issue_meta['issue_order'] ] = array( $issue, $issue_meta );
						else 
							$archives_no_issue_order[] = array( $issue, $issue_meta );
						break;
						
					case "name":
						$archives[ $issue_meta['name'] ] = array( $issue, $issue_meta );
						break;
					
					case "term_id":
						$archives[ $issue->term_id ] = array( $issue, $issue_meta );
						break;
					
				}
			
			}
			
		}
		
		if ( 'issue_order' == $orderby && !empty( $archives_no_issue_order ) )
			$archives = array_merge( $archives_no_issue_order, $archives );
		
		if ( "DESC" == $order )
			krsort( $archives );
		else
			ksort( $archives );
			
		$archive_count = count( $archives ) - 1; //we want zero based
		
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		if ( !empty( $limit ) ) {
			$offset = ( $paged - 1 ) * $limit;
			$archives = array_slice( $archives, $offset, $limit );
		}
			
		$results = '<div class="issuem_archives_shortcode">';
		
		foreach ( $archives as $archive => $issue_array ) {
		
			$issue_meta = get_option( 'issuem_issue_' . $issue_array[0]->term_id . '_meta' );
				
			$class = '';
			if ( 'Draft' === $issue_meta['issue_status'] )
				$class = 'issuem_issue_draft';
			
			$results .= '<div id="issue-' . $issue_array[0]->term_id . '" class="issuem_archive ' . $class . '">';
			
			if ( 0 == $issuem_settings['page_for_articles'] )
				$article_page = get_bloginfo( 'wpurl' ) . '/' . apply_filters( 'issuem_page_for_articles', 'article/' );
			else
				$article_page = get_page_link( $issuem_settings['page_for_articles'] );
		
			$issue_url = get_term_link( $issue_array[0], 'issuem_issue' );
		    if ( !empty( $issuem_settings['use_issue_tax_links'] ) || is_wp_error( $issue_url ) ) {
		        $issue_url = add_query_arg( 'issue', $issue_array[0]->slug, $article_page );
		    }
				
			if ( !empty( $issue_array[1]['pdf_version'] ) || !empty( $issue_meta['external_pdf_link'] ) ) {
				
				$pdf_url = empty( $issue_meta['external_pdf_link'] ) ? apply_filters( 'issuem_pdf_attachment_url', wp_get_attachment_url( $issue_array[1]['pdf_version'] ), $issue_array[1]['pdf_version'] ) : $issue_meta['external_pdf_link'];
				
				$pdf_line = '<a href="' . $pdf_url . '" target="' . $issuem_settings['pdf_open_target'] . '">';
				
				if ( 'PDF Archive' == $issue_array[1]['issue_status'] ) {
					
					$issue_url = $pdf_url;
					$pdf_line .= empty( $pdf_only_title ) ? $issuem_settings['pdf_only_title'] : $pdf_only_title;
					
				} else {
					
					$pdf_line .= empty( $pdf_title ) ? $issuem_settings['pdf_title'] : $pdf_title;
				
				}
				
				$pdf_line .= '</a>';
				
			} else {
			
				$pdf_line = apply_filters( 'issuem_pdf_version', '&nbsp;', $pdf_title, $issue_array[0] );
				
			}
						
			if ( !empty( $issue_meta['external_link'] ) )
				$issue_url = apply_filters( 'archive_issue_url_external_link', $issue_meta['external_link'], $issue_url );
	
			if ( !empty( $issue_array[1]['cover_image'] ) )
				$image_line = wp_get_attachment_image( $issue_array[1]['cover_image'], 'issuem-cover-image' );
			else
				$image_line = '<img src="' . $default_image . '" />';
				
			$results .= '<p><a class="featured_archives_cover" style="width: ' . apply_filters( 'issuem-cover-image-width', $issuem_settings['cover_image_width'] ) . 'px; height: ' . apply_filters( 'issuem-cover-image-height', $issuem_settings['cover_image_height'] ) . 'px;" href="' . $issue_url . '">' . $image_line . '</a>';
			$results .= '<br /><a href="' . $issue_url . '">' . $issue_array[0]->name . '</a>';
			$results .= '<br />' . $pdf_line;
		
			$results .= '</div>';
						
		}
		
		if ( !empty( $limit ) ) {
		
			$url = remove_query_arg( array( 'page', 'paged' ) );
		
			$results .= '<div class="next_previous_archive_pagination">';
		
			if ( 0 === $offset && $limit < $archive_count ) {
				//Previous link only
				$results .= '<div class="alignleft"><a href="' . add_query_arg( 'paged', $paged + 1, $url ) . '">' . __( 'Previous Archives', 'issuem' ) . '</a></div>';
				
			} else if ( $offset >= $archive_count ) {
				//Next link only
				$results .= '<div class="alignright"><a href="' . add_query_arg( 'paged', $paged - 1, $url ) . '">' . __( 'Next Archives', 'issuem' ) . '</a></div>';
			} else {
				//Next and Previous Links
				$results .= '<div class="alignleft"><a href="' . add_query_arg( 'paged', $paged + 1, $url ) . '">' . __( 'Previous Archives', 'issuem' ) . '</a></div>';
				$results .= '<div class="alignright"><a href="' . add_query_arg( 'paged', $paged - 1, $url ) . '">' . __( 'Next Archives', 'issuem' ) . '</a></div>';
			}
			
			
			$results .= '</div>';
		}
		
		if ( get_option( 'issuem_api_error_received' ) )
			$results .= '<div class="api_error"><p><a href="http://issuem.com/" target="_blank">' . __( 'Issue Management by ', 'issuem' ) . 'IssueM</a></div>';
			
		$results .= '</div>';
		
		return $results;
		
	}
	add_shortcode( 'issuem_archives', 'do_issuem_archives' );
	
}

if ( !function_exists( 'do_issuem_featured_rotator' ) ) {
	
	/**
	 * Outputs Issue Featured Rotator Images HTML from shortcode call
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of Issue Featured Rotator Images
	 */
	function do_issuem_featured_rotator( $atts ) {
		global $post;
		$results = '';
		
		$issuem_settings = get_issuem_settings();
		
		$defaults = array(
			'posts_per_page'    => -1,
			'offset'            => 0,
			'orderby'           => 'menu_order',
			'order'             => 'DESC',
			'issue'				=> get_active_issuem_issue(),
			'show_title'		=> true,
			'show_teaser'		=> true,
			'show_byline'		=> false,
			'article_category'		=> 'all',
		);
		
		// Merge defaults with passed atts
		// Extract (make each array element its own PHP var
		extract( shortcode_atts( $defaults, $atts ) );
		
		$args = array(
			'posts_per_page'	=> $posts_per_page,
			'offset'			=> $offset,
			'post_type'			=> 'article',
			'orderby'			=> $orderby,
			'order'				=> $order,
			'meta_key'			=> '_featured_rotator',
			'issuem_issue' 		=> $issue,
		);
		
		if ( !empty( $issuem_settings['use_wp_taxonomies'] ) ) 
			$cat_type = 'category';
		else
			$cat_type = 'issuem_issue_categories';
		
		if ( !empty( $article_category ) && 'all' !== $article_category ) {
				
			$category = array(
				'taxonomy' 	=> $cat_type,
				'field' 	=> 'slug',
				'terms' 	=> split( ',', $article_category ),
			);	
			
			$args['tax_query'] = array(
				'relation'	=> 'AND',
				$issuem_issue,
				$category
			);
			
		}
		
		$featured_articles = get_posts( $args );
		
		if ( $featured_articles ) :
			
			$results .= '<div id="issuem-featured-article-slideshowholder">'; 
			$results .= '<div class="issuem-flexslider">';
			$results .= '<ul class="slides">';
		
			/* start the loop */
			foreach( $featured_articles as $article ) {
				
				if ( has_post_thumbnail( $article->ID ) ) {
					
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $article->ID ), 'issuem-featured-rotator-image' );
					
					if ( !empty( $show_title ) ) 
						$title = get_the_title( $article->ID );
					else
						$title = '';
					
					if ( !empty( $show_teaser ) ) 
						$teaser = get_post_meta( $article->ID, '_teaser_text', true );
					else
						$teaser = '';
					
					if ( !empty( $show_byline ) ) {

						$author_name = get_issuem_author_name( $article );
						
						$byline = sprintf( __( 'By %s', 'issuem' ), apply_filters( 'issuem_author_name', $author_name, $article->ID ) );
					
					} else {
						
						$byline = '';
						
					}
					
					$caption = '<span class="featured_slider_title">' . $title . '</span> <span  class="featured_slider_teaser">' . $teaser . '</span> <span class="featured_slider_byline">' . $byline . '</span>';
					
					$results .= '<li>';
					$results .= '<a href="' . get_permalink( $article->ID ) . '"><img src="' . $image[0] .'" alt="' .strip_tags( $caption ) . '" />';
					$results .= '<div class="flex-caption"><div class="flex-caption-content">' . $caption . '</div></div></a>';
					$results .= '</li>';
					
				}
				
			}
			
			$results .= '</ul>';  //slides
			$results .= '</div>'; //flexslider
			$results .= '</div>'; //issuem-featured-article-slideshowholder

			if ( $issuem_settings['show_rotator_control'] ) {
				$control = 'true';
			} else {
				$control = 'false';
			}

			if ( $issuem_settings['show_rotator_direction'] ) {
				$direction = 'true';
			} else {
				$direction = 'false';
			}
					
			$results .= "<script type='text/javascript'>
						jQuery( window ).load( function(){
						  jQuery( '.issuem-flexslider' ).issuem_flexslider({
							animation: '" . $issuem_settings['animation_type'] . "',
							start: function(slider){
							  jQuery('body').removeClass('loading');
							},
							controlNav:" . $control . ",
							directionNav: " . $direction . ",
						  });
						
						var slideWidth = jQuery('.flex-viewport').outerWidth();
						jQuery('.flex-caption').css('width', slideWidth );
						  
						});
					  </script>";
			
		endif;
		
		return $results;
		
	}
	add_shortcode( 'issuem_featured_rotator', 'do_issuem_featured_rotator' );

}

if ( !function_exists( 'do_issuem_featured_thumbs' ) ) {

	/**
	 * Outputs Issue Featured Thumbnail Images HTML from shortcode call
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of Issue Featured Rotator Thumbnails
	 */
	function do_issuem_featured_thumbs( $atts ) {
		
		global $post;
		$results = '';
		
		$issuem_settings = get_issuem_settings();
		
		$defaults = array(
			'content_type'		=> 'teaser',
			'posts_per_page'    => -1,
			'offset'            => 0,
			'orderby'           => 'menu_order',
			'order'             => 'DESC',
			'max_images'		=> 0,
			'issue'				=> get_active_issuem_issue(),
			'article_category'		=> 'all',
			'show_cats'			=> false,
		);
		
		// Merge defaults with passed atts
		// Extract (make each array element its own PHP var
		extract( shortcode_atts( $defaults, $atts ) );
		
		$args = array(
			'posts_per_page'	=> $posts_per_page,
			'offset'			=> $offset,
			'post_type'			=> 'article',
			'orderby'			=> $orderby,
			'order'				=> $order,
			'meta_key'			=> '_featured_thumb',
			'issuem_issue' 		=> $issue,
		);
		
		if ( !empty( $issuem_settings['use_wp_taxonomies'] ) ) 
			$cat_type = 'category';
		else
			$cat_type = 'issuem_issue_categories';
		
		if ( !empty( $article_category ) && 'all' !== $article_category ) {
				
			$category = array(
				'taxonomy' 	=> $cat_type,
				'field' 	=> 'slug',
				'terms' 	=> split( ',', $article_category ),
			);	
			
			$args['tax_query'] = array(
				'relation'	=> 'AND',
				$issuem_issue,
				$category
			);
			
		}
						
		$featured_articles = get_posts( $args );
		
		if ( $featured_articles ) : 
			
			$results .= '<div id="issuem-featured-article-thumbs-wrap">';
		
			$count = 1;
			/* start the loop */
			foreach( $featured_articles as $article ) {
				
				if ( has_post_thumbnail( $article->ID ) ) {

					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $article->ID ), 'issuem-featured-thumb-image' );
					$image = apply_filters( 'issuem_featured_thumbs_article_image', $image, $article );
					
					$results .= apply_filters( 'issuem_featured_thumbs_before_thumbnail_div', '', $article );
					$results .= '<div class="issuem-featured-article-thumb">';
					$results .= apply_filters( 'issuem_featured_thumbs_start_thumbnail_div', '', $article );
					
					$results .= apply_filters( 'issuem_featured_thumbs_before_thumbnail_image', '', $article );
					$results .= apply_filters( 'issuem_featured_thumbs_thumbnail_image', '<a class="issuem-featured-thumbs-img" href="' . get_permalink( $article->ID ) . '"><img src="' . $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '" alt="' . get_post_meta( $article->ID, '_teaser_text', true ) . '" /></a>', $article );
					$results .= apply_filters( 'issuem_featured_thumbs_after_thumbnail_image', '', $article );
					
					
					if ( 'true' === $show_cats ) {
						$results .= apply_filters( 'issuem_featured_thumbs_before_thumbnail_category', '', $article );
						$results .= apply_filters( 'issuem_featured_thumbs_thumbnail_category', '<p class="issuem-article-category">' . get_the_term_list( $article->ID, $cat_type ) . '</p>', $article );
						$results .= apply_filters( 'issuem_featured_thumbs_after_thumbnail_category', '', $article );
					}
					

					$results .= apply_filters( 'issuem_featured_thumbs_before_thumbnail_title', '', $article );
					$results .= apply_filters( 'issuem_featured_thumbs_after_thumbnail_title', '<h3 class="issuem-featured-thumb-title"><a href="' . get_permalink( $article->ID ) . '">' . get_the_title( $article->ID ) . '</a></h3>', $article );
					$results .= apply_filters( 'issuem_featured_thumbs_after_thumbnail_title', '', $article );

					$results .= apply_filters( 'issuem_featured_thumbs_before_thumbnail_content', '', $article );
					switch ( $content_type ) {
								
							case 'excerpt':
								$results .= apply_filters( 'issuem_featured_thumbs_thumbnail_content', '<p class="issuem-featured-thumb-content">' . get_issuem_article_excerpt( $article->ID ) . '</p>', $article );	
								break;
								
							case 'teaser':	
							default:					
								$results .= apply_filters( 'issuem_featured_thumbs_thumbnail_content', '<p class="featured-thumb-content">' . get_post_meta( $article->ID, '_teaser_text', true ) . '</p>', $article );
								break;
								
					}
					$results .= apply_filters( 'issuem_featured_thumbs_after_thumbnail_content', '', $article );
					
					$results .= apply_filters( 'issuem_featured_thumbs_before_thumbnail_byline', '', $article );
					if ( $issuem_settings['show_thumbnail_byline'] ) {
						
						$author_name = get_issuem_author_name( $article );
						
						$results .= apply_filters( 'issuem_featured_thumbs_thumbnail_byline', '<p class="featured-thumb-byline">' . sprintf( __( 'By %s', 'issuem' ), apply_filters( 'issuem_author_name', $author_name, $article->ID ) ) . '</p>', $article );
						
					}
					$results .= apply_filters( 'issuem_featured_thumbs_before_thumbnail_byline', '', $article );
					
					$results .= apply_filters( 'issuem_featured_thumbs_end_thumbnail_div', '', $article );
					$results .= '</div>';
					$results .= apply_filters( 'issuem_featured_article_after_thumbnail_div', '', $article );

					if ( 0 != $max_images && $max_images <= $count )
						break;
						
					$count++;
					
				}
				
			}
			
			$results .= '</div>';
			
		endif;
		
		return $results;
		
	}
	add_shortcode( 'issuem_featured_thumbnails', 'do_issuem_featured_thumbs' );

}
