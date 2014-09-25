<?php
/**
 * Registers IssueM Widgets
 *
 * @package IssueM
 * @since 1.0.0
 */

/**
 * Register our widgets classes with WP
 *
 * @since 1.0.0
 */
function register_issuem_widgets() {
	
	$settings = get_issuem_settings();
	
	register_widget( 'IssueM_Active_Issue' );
	register_widget( 'IssueM_Article_List' );

	if ( empty( $settings['use_wp_taxonomies'] ) ) 
		register_widget( 'IssueM_Article_Categories' );

}
add_action( 'widgets_init', 'register_issuem_widgets' );

/**
 * This class registers and returns the Cover Image Widget
 *
 * @since 1.0.0
 */
class IssueM_Active_Issue extends WP_Widget {
	
	/**
	 * Set's widget name and description
	 *
	 * @since 1.0.0
	 */
	function IssueM_Active_Issue() {
		
		$widget_ops = array( 'classname' => 'issuem_active_issue', 'description' => __( 'Displays the active IssueM Issue details', 'issuem' ) );
		$this->WP_Widget( 'IssueM_Active_Issue', __( 'IssueM Active Issue', 'issuem' ), $widget_ops );
	
	}
	
	/**
	 * Displays the widget on the front end
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		
		extract( $args );
	
		$issuem_settings = get_issuem_settings();
		$issue = get_active_issuem_issue();
		$term = get_term_by( 'slug', $issue, 'issuem_issue' );
		$meta_options = get_option( 'issuem_issue_' . $term->term_id . '_meta' );

		$title = apply_filters('active_issue_widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);

		if ( 0 == $issuem_settings['page_for_articles'] )
				$article_page = get_bloginfo( 'wpurl' ) . '/' . apply_filters( 'issuem_page_for_articles', 'article/' );
			else
				$article_page = get_page_link( $issuem_settings['page_for_articles'] );
		
		$issue_url = get_term_link( $issue, 'issuem_issue' );
        if ( !empty( $issuem_settings['use_issue_tax_links'] ) || is_wp_error( $issue_url ) ) {
            $issue_url = add_query_arg( 'issue', $issue, $article_page );
        }

		$out = '';
		
		if ( 'on' == $instance['display_issue_name'] )
			$out .= '<p class="issuem_widget_issue_name"><a href="' . apply_filters( 'issuem_issue_url', $issue_url, $issue, $meta_options ) . '">' . $term->name . '</a></p>';
		
		if ( 'on' == $instance['display_issue_cover'] ) {
		
			if ( !empty( $meta_options['cover_image'] ) )
				$out .= '<p class="issuem_widget_issue_cover_image"><a href="' . apply_filters( 'issuem_issue_url', $issue_url, $issue, $meta_options ) . '">' . wp_get_attachment_image( $meta_options['cover_image'], 'issuem-cover-image' ) . '</a></p>';
			else
				$out .= '<p class="issuem_widget_issue_cover_image"><img src="' . $issuem_settings['default_issue_image'] . '" /></p>';
				
		}
		
		if ( 'on' == $instance['display_pdf_link'] ) {
			if ( !empty( $meta_options['pdf_version'] ) )
				$out .= '<p><a class="issuem_widget_issue_pdf_link" target="_blank" href="' . apply_filters( 'issuem_pdf_attachment_url', wp_get_attachment_url( $meta_options['pdf_version'] ), $meta_options['pdf_version'] ) . '">' . $issuem_settings['pdf_title'] . '</a></p>';
			else if ( !empty( $meta_options['external_pdf_link'] ) )
				$out .= '<p><a class="issuem_widget_issue_pdf_link" target="_blank" href="' . apply_filters( 'issuem_pdf_link_url', $meta_options['external_pdf_link'] ) . '">' . $issuem_settings['pdf_title'] . '</a></p>';
		}

		if ( ! empty( $out ) ) {
			
			echo $before_widget;
			if ( $title) {
				echo $before_title . $title . $after_title;
			}
			echo '<div class="issuem_active_list_widget">';
			echo $out; 
			echo '</div>';
			echo $after_widget;	
		
		}
	
	}

	/**
	 * Saves the widgets options on submit
	 *
	 * @since 1.0.0
	 * 
	 * @param array $new_instance
	 * @param array $old_isntance
	 */
	function update( $new_instance, $old_instance ) {
		
		$instance 							= $old_instance;
		$instance['title'] 					= $new_instance['title'];
		$instance['display_issue_name'] 	= ( 'on' == $new_instance['display_issue_name'] ) ? 'on' : 'off';
		$instance['display_issue_cover'] 	= ( 'on' == $new_instance['display_issue_cover'] ) ? 'on' : 'off';
		$instance['display_pdf_link'] 		= ( 'on' == $new_instance['display_pdf_link'] ) ? 'on' : 'off';
	
		return $instance;
	
	}

	/**
	 * Displays the widget options in the dashboard
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance
	 */
	function form( $instance ) {
		
		$available_issues = get_terms( 'issuem_issue', array( 'hide_empty' => false ) );
			
		//Defaults
		$defaults = array(
			'display_issue_name'	=> 'on',
			'display_issue_cover'	=> 'on',
			'display_pdf_link'		=> 'on'
		);
		
		extract( wp_parse_args( (array) $instance, $defaults ) );
		
		if ( !empty( $available_issues ) ) :
		
			?>

			<p>
	        	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'issuem' ); ?></label>
	            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( strip_tags( $title ) ); ?>" />
	        </p>
	        
			<p>
	        	<label for="<?php echo $this->get_field_id('display_issue_name'); ?>"><?php _e( 'Display Issue Title?', 'issuem' ); ?></label>
                <input class="checkbox" id="<?php echo $this->get_field_id('display_issue_name'); ?>" name="<?php echo $this->get_field_name('display_issue_name'); ?>" type="checkbox" value="on" <?php checked( 'on' == $display_issue_name ) ?> />
	        </p>
            
			<p>
	        	<label for="<?php echo $this->get_field_id('display_issue_cover'); ?>"><?php _e( 'Display Issue Cover Image?', 'issuem' ); ?></label>
                <input class="checkbox" id="<?php echo $this->get_field_id('display_issue_cover'); ?>" name="<?php echo $this->get_field_name('display_issue_cover'); ?>" type="checkbox" value="on" <?php checked( 'on' == $display_issue_cover ) ?> />
	        </p>
            
			<p>
	        	<label for="<?php echo $this->get_field_id('display_pdf_link'); ?>"><?php _e( 'Display Issue PDF Link?', 'issuem' ); ?></label>
                <input class="checkbox" id="<?php echo $this->get_field_id('display_pdf_link'); ?>" name="<?php echo $this->get_field_name('display_pdf_link'); ?>" type="checkbox" value="on" <?php checked( 'on' == $display_pdf_link ) ?> />
	        </p>
        	<?php 
        
        else : 
        
            _e( 'You have to create a issue before you can use this widget.', 'issuem' );
        
        endif;
	
	}

}
 
/**
 * This class registers and returns the Cover Image Widget
 *
 * @since 1.0.0
 */
class IssueM_Article_List extends WP_Widget {
	
	/**
	 * Set's widget name and description
	 *
	 * @since 1.0.0
	 */
	function IssueM_Article_List() {
		
		$widget_ops = array( 'classname' => 'issuem_article_list', 'description' => __( 'Sidebar widget to display the current articles.', 'issuem' ) );
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget( 'IssueM_Article_List', __( 'IssueM Article List', 'issuem' ), $widget_ops, $control_ops );
	
	}
	
	/**
	 * Displays the widget on the front end
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $atts, $instance ) {
		
		global $post;
		
		if ( !empty( $post->ID ) )
			$current_post_id = $post->ID;
		else
			$current_post_id = 0;
	
		$issuem_settings = get_issuem_settings();
			
		$out = '';
		
		extract( $atts );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);
		
		$args = array(
			'posts_per_page'    => empty( $instance['posts_per_page'] ) ? -1 : $instance['posts_per_page'],
			'post_type'			=> 'article',
			'orderby'			=> empty( $instance['orderby'] ) ? 'menu_order' : $instance['orderby'],
			'order' 			=> empty( $instance['order'] ) ? 'DESC' : $instance['order'],
		);
		
		$issuem_issue = array(
			'taxonomy' 	=> 'issuem_issue',
			'field' 	=> 'slug',
			'terms' 	=> get_active_issuem_issue()
		);
		
		if ( !empty( $instance['article_category'] ) && 'all' != $instance['article_category'] ) {
			
			if ( !empty( $issuem_settings['use_wp_taxonomies'] ) ) 
				$cat_type = 'category';
			else
				$cat_type = 'issuem_issue_categories';
		
			$category = array(
				'taxonomy' 	=> $cat_type,
				'field' 	=> 'slug',
				'terms' 	=> (array)$instance['article_category']
			);	
			
			$args['tax_query'] = array(
				'relation'	=> 'AND',
				$issuem_issue,
				$category
			);
			
		} else {
			
			$args['tax_query'] = array(
				$issuem_issue
			);
			
		}
		
		$articles = new WP_Query( $args );
		
		if ( $articles->have_posts() ) : 
		
			while ( $articles->have_posts() ) : $articles->the_post();
				
				$out .= '<div class="article_list">';
			
				$out .= "\n\n";
			
				if ( $current_post_id == $post->ID )
					$out .= '<div id="current_article">';
				
				$out .= issuem_replacements_args( $instance['article_format'], $post );
			
				if ( $current_post_id == $post->ID )
					$out .= '</div>';
					
				$out .= '</div>';
			
            endwhile;
		
		endif;
		
		if ( !empty( $out ) ) {
			
			echo $before_widget;
			
			if ( $title)
				echo $before_title . $title . $after_title;
				
			echo '<div class="issuem_article_list_widget">';
			echo $out; 
			echo '</div>';
			echo $after_widget;	
		
		}

		wp_reset_query();
	
	}

	/**
	 * Save's the widgets options on submit
	 *
	 * @since 1.0
	 
	 * @param array $new_instance
	 * @param array $old_instance
	 */
	function update( $new_instance, $old_instance ) {
		
		$instance 						= $old_instance;
		$instance['title'] 				= $new_instance['title'];
		$instance['posts_per_page'] 	= $new_instance['posts_per_page'];
		$instance['article_format'] 	= $new_instance['article_format'];
		$instance['article_category'] 	= $new_instance['article_category'];
		$instance['orderby'] 			= $new_instance['orderby'];
		$instance['order'] 				= $new_instance['order'];
		return $instance;
	
	}

	/**
	 * Displays the widget options in the dashboard
	 *
	 * @since 1.0
	 
	 * @param array $instance
	 */
	function form( $instance ) {
		
		$available_issues = get_terms( 'issuem_issue', array( 'hide_empty' => false ) );
			
		//Defaults
		$defaults = array(
			'title'				=> '',
			'posts_per_page'	=> '-1',
			'article_format'	=> 	'<p class="issuem_widget_category">%CATEGORY[1]%</p>' . "\n" .
									'<p><a class="issuem_widget_link" href="%URL%">%TITLE%</a></p>' . "\n" .
									'<p class="issuem_widget_teaser">%TEASER%</p>' . "\n",
			'article_category'	=> 	'all',
			'orderby'			=> 	'menu_order',
			'order'				=> 	'DESC',
		);
		
		extract( wp_parse_args( (array) $instance, $defaults ) );
		
		if ( !empty( $available_issues ) ) :
		
			?>
			<p>
	        	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'issuem' ); ?></label>
	            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( strip_tags( $title ) ); ?>" />
	        </p>
            
			<p>
	        	<label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e( 'Number of Articles to Show:', 'issuem' ); ?></label>
	            <input class="widefat" id="<?php echo $this->get_field_id('posts_per_page'); ?>" name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="text" value="<?php echo esc_attr( strip_tags( $posts_per_page ) ); ?>" />
                <small>-1 = All Articles</small>
	        </p>
			<?php
		
			if ( !empty( $issuem_settings['use_wp_taxonomies'] ) ) 
				$cat_type = 'category';
			else
				$cat_type = 'issuem_issue_categories';
			
			$categories = get_terms( $cat_type );
		
			?>  
			<p>
	        	<label for="<?php echo $this->get_field_id('article_category'); ?>"><?php _e( 'Select Category to Display:', 'issuem' ); ?></label><br />
                <select id="<?php echo $this->get_field_id('article_category'); ?>" name="<?php echo $this->get_field_name('article_category'); ?>">
				<option value="all" <?php selected( 'all', $article_category ); ?>><?php _e( 'All Categories', 'issuem' ); ?></option>
				<?php foreach ( $categories as $cat ) { ?>
					<option value="<?php echo $cat->slug; ?>" <?php selected( $cat->slug, $article_category ); ?>><?php echo $cat->name; ?></option>
                <?php } ?>
                </select>
	        </p>
            
            <p>
				<?php
                $orderby_options = array( 
                    'none' 				=> __( 'None', 'issuem' ), 
                    'ID' 				=> __( 'Article ID', 'issuem' ), 
                    'author' 			=> __( 'Article Author', 'issuem' ), 
                    'title' 			=> __( 'Article Title', 'issuem' ), 
                    'name' 				=> __( 'Article Name', 'issuem' ), 
                    'date'				=> __( 'Article Publish Date', 'issuem' ), 
                    'modified'			=> __( 'Article Modified Date', 'issuem' ), 
                    'menu_order'		=> __( 'Article Order', 'issuem' ), 
                    'rand'				=> __( 'Random Order', 'issuem' ), 
                    'comment_count' 	=> __( 'Comment Count', 'issuem' )
                );
                ?>
            
                <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e( 'Select Sort Order:', 'issuem' ); ?></label><br />
                <select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
                <?php foreach ( $orderby_options as $orderby_key => $orderby_title ) { ?>
                    <option value="<?php echo $orderby_key; ?>" <?php selected( $orderby_key, $orderby ); ?>><?php echo $orderby_title; ?></option>
                <?php } ?>
                </select>
            </p>
            
            <p>
				<?php
                $order_options = array( 
                    'DESC' 	=> __( 'Descending', 'issuem' ), 
                    'ASC' 	=> __( 'Ascending', 'issuem' ), 
                );
                ?>
            
                <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e( 'Select Order Direction:', 'issuem' ); ?></label><br />
                <select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
                <?php foreach ( $order_options as $order_key => $order_title ) { ?>
                    <option value="<?php echo $order_key; ?>" <?php selected( $order_key, $order ); ?>><?php echo $order_title; ?></option>
                <?php } ?>
                </select>
            </p>
            
			<p>
	        	<label for="<?php echo $this->get_field_id('article_format'); ?>"><?php _e( 'Article Format:', 'issuem' ); ?></label><br />
                <textarea id="<?php echo $this->get_field_id('article_format'); ?>" name="<?php echo $this->get_field_name('article_format'); ?>" cols="70" rows="16"><?php echo $article_format; ?></textarea>
	        </p>
            <p><a href="/wp-admin/edit.php?post_type=article&page=issuem-help"><?php _e( 'See IssueM Help for details on article formatting', 'issuem' ); ?></a></p>
        	<?php 
        
        else : 
        
            _e( 'You have to create a issue before you can use this widget.', 'issuem' );
        
        endif;
	
	}

}

/**
 * Article Categories widget class
 *
 * @since 1.2.0
 */
class IssueM_Article_Categories extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'issuem_widget_categories', 'description' => __( 'A list or dropdown of Article categories', 'issuem' ) );
		parent::__construct('categories', __( 'Article Categories', 'issuem' ), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Article Categories', 'issuem' ) : $instance['title'], $instance, $this->id_base);
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

		if ( $d ) {
			$cat_args['show_option_none'] = __( 'Select Article Category', 'issuem' );
			issuem_dropdown_categories( apply_filters('issuem_widget_categories_dropdown_args', $cat_args) );
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var issuem_cat_dropdown = document.getElementById("issuem_issue_cat");
	function onIssueMCatChange() {
		if ( issuem_cat_dropdown.options[issuem_cat_dropdown.selectedIndex].value != 0 
		&& issuem_cat_dropdown.options[issuem_cat_dropdown.selectedIndex].value != -1 ) {
			location.href = "<?php echo home_url(); ?>/?issuem_issue_categories="+issuem_cat_dropdown.options[issuem_cat_dropdown.selectedIndex].value;
		}
	}
	issuem_cat_dropdown.onchange = onIssueMCatChange;
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		$cat_args['taxonomy'] = 'issuem_issue_categories';
		wp_list_categories(apply_filters('issuem_widget_categories_args', $cat_args));
?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = !empty($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = !empty( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = !empty( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'issuem' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown', 'issuem' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show product counts', 'issuem' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy', 'issuem' ); ?></label></p>
<?php
	}

}
