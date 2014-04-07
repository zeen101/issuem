<?php
/**
 * Registers IssueM Article, Category Taxonomy w/ Meta Boxes
 *
 * @package IssueM
 * @since 1.0.0
 */

if ( !function_exists( 'create_issuem_cats_taxonomy' ) ) {
		
	/**
	 * Registers Article Category taxonomy for IssueM
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be issuem_article_categories
	 */
	function create_issuem_cats_taxonomy() {
		
	  $labels = array(
	  
			'name' 				=> __( 'Article Categories', 'issuem' ),
			'singular_name' 	=> __( 'Article Category', 'issuem' ),
			'search_items' 		=> __( 'Search Article Categories', 'issuem' ),
			'all_items' 		=> __( 'All Article Categories', 'issuem' ), 
			'parent_item' 		=> __( 'Parent Article Category', 'issuem' ),
			'parent_item_colon' => __( 'Parent Article Category:', 'issuem' ),
			'edit_item' 		=> __( 'Edit Article Category', 'issuem' ), 
			'update_item' 		=> __( 'Update Article Category', 'issuem' ),
			'add_new_item' 		=> __( 'Add New Article Category', 'issuem' ),
			'new_item_name' 	=> __( 'New Article Category', 'issuem' ),
			'menu_name' 		=> __( 'Article Categories', 'issuem' )
			
		); 	
	
		register_taxonomy(
			'issuem_issue_categories', 
			array( ), 
			array(
				'hierarchical' 	=> true,
				'labels' 		=> $labels,
				'show_ui' 		=> true,
				'show_tagcloud' => true,
				'query_var' 	=> true,
				'rewrite' 		=> array( 'slug' => 'article-categories' ),
				'capabilities' 	=> array(
						'manage_terms' 	=> 'manage_article_categories',
						'edit_terms' 	=> 'manage_article_categories',
						'delete_terms' 	=> 'manage_article_categories',
						'assign_terms' 	=> 'edit_issues'
						)
						
			)
		);
		
	}
	add_action( 'init', 'create_issuem_cats_taxonomy', 0 );

}

if ( !function_exists( 'issuem_article_categories_columns' ) ) {
		
	/**
	 * Filters column headings for Article categories
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	function issuem_article_categories_columns( $columns ) {
		
		// We add a Category Order field
		$columns['category_order'] = __( 'Category Order', 'issuem' );
	
		return $columns;
		
	}
	add_filter( 'manage_edit-issuem_issue_categories_columns', 'issuem_article_categories_columns', 10, 1 );

}

if ( !function_exists( 'issuem_article_categories_sortable_columns' ) ) {
		
	/**
	 * Filters sortable columns
	 *
	 * @since 1.2.0
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	function issuem_article_categories_sortable_columns( $columns ) {
		
		$columns['category_order'] = 'category_order';
	
		return $columns;
		
	}
	add_filter( 'manage_edit-issuem_issue_categories_sortable_columns', 'issuem_article_categories_sortable_columns', 10, 1 );

}

if ( !function_exists( 'issuem_issue_categories_sortable_column_orderby' ) )  {
	
	/**
	 * Filters sortable columns
	 *
	 * @since 1.2.0
	 * @todo misnamed originaly, should reallly be issuem_article_categories
	 *
	 * @param array $terms
	 * @param array $taxonomies
	 * @param array $args
	 * @return array $terms
	 */
	function issuem_issue_categories_sortable_column_orderby( $terms, $taxonomies, $args ) {
	
		global $hook_suffix;
		
		if ( 'edit-tags.php' == $hook_suffix && in_array( 'issuem_issue_categories', $taxonomies ) 
				&& ( empty( $_GET['orderby'] ) && !empty( $args['orderby'] ) 
						|| ( !empty( $args['orderby'] ) && 'category_order' == $args['orderby'] ) ) ) {
				
			$sort = array();
			$count = 0;
		
			foreach ( $terms as $issue ) {
				
				$issue_meta = get_option( 'issuem_issue_categories_' . $issue->term_id . '_meta' );
			
				if ( !empty( $issue_meta['category_order'] ) )
					$sort[ $issue_meta['category_order'] ] = $issue;
				else 
					$sort[ '-' . ++$count ] = $issue;
				
			}
		
			if ( "asc" != $args['order'] )
				krsort( $sort );
			else
				ksort( $sort );
			
			$terms = $sort;
			
		}
		
		return $terms;
		
	}
	add_filter( 'get_terms', 'issuem_issue_categories_sortable_column_orderby', 10, 3 );

}

if ( !function_exists( 'manage_issuem_article_categories_custom_column' ) ) {
		
	/**
	 * Sets data for custom article cateagory columns
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $blank
	 * @param string $column_name
	 * @param int $term_id
	 *
	 * @return mixed Value of column for given term ID.
	 */
	function manage_issuem_article_categories_custom_column( $blank, $column_name, $term_id ) {
		
		$issue_cat_meta = get_option( 'issuem_issue_categories_' . $term_id . '_meta' );
	
		return $issue_cat_meta[$column_name];
	
	}
	add_filter( "manage_issuem_issue_categories_custom_column", 'manage_issuem_article_categories_custom_column', 10, 3 );
	
}

if ( !function_exists( 'issuem_article_categories_add_form_fields' ) ) {
		
	/**
	 * Outputs HTML for new form fields in Article Categories
	 *
	 * @since 1.0.0
	 */
	function issuem_article_categories_add_form_fields() {
		
		?>	
		
		<div class="form-field">
			<label for="category_order"><?php _e( 'Category Order', 'issuem' ); ?></label>
			<input type="text" name="category_order" id="category_order" />
		</div>
		
		<?php
		
	}
	add_action( 'issuem_issue_categories_add_form_fields', 'issuem_article_categories_add_form_fields' );

}

if ( !function_exists( 'issuem_article_categories_edit_form_fields' ) ) {
		
	/**
	 * Outputs HTML for new form fields in Article Categories (on Edit form)
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be issuem_article_categories
	 */
	function issuem_article_categories_edit_form_fields( $tag, $taxonomy ) {
	   
		$article_cat_meta = get_option( 'issuem_issue_categories_' . $tag->term_id . '_meta' );
		
		?>
		
		<tr class="form-field">
		<th valign="top" scope="row"><label for="category_order"><?php _e( 'Category Order', 'issuem' ); ?></label></th>
		<td><input type="text" name="category_order" id="category_order" value="<?php echo $article_cat_meta['category_order'] ?>" /></td>
		</tr>
		
		<?php
		
	}
	add_action( 'issuem_issue_categories_edit_form_fields', 'issuem_article_categories_edit_form_fields', 10, 2 );

}

if ( !function_exists( 'save_issuem_article_categories_meta' ) ) {
		
	/**
	 * Saves form fields for Article Categories taxonomy
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be issuem_article_categories
	 *
	 * @param int $term_id Term ID
	 * @param int $taxonomy_id Taxonomy ID
	 */
	function save_issuem_article_categories_meta( $term_id, $taxonomy_id ) {
	
		$issue_cat_meta = get_option( 'issuem_issue_categories_' . $term_id . '_meta' );
		
		if ( !empty( $_POST['category_order'] ) ) 
			$issue_cat_meta['category_order'] = $_POST['category_order'];
			
		update_option( 'issuem_issue_categories_' . $term_id . '_meta', $issue_cat_meta );
		
	}
	add_action( 'created_issuem_issue_categories', 'save_issuem_article_categories_meta', 10, 2 );
	add_action( 'edited_issuem_issue_categories', 'save_issuem_article_categories_meta', 10, 2 );

}


/**
 * Create HTML dropdown list of IssueM Article Categories.
 *
 * @since 1.2.6 
 * @uses Walker
 */
class Walker_IssueMCategoryDropdown extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	var $tree_type = 'category';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category. Used for padding.
	 * @param array  $args     Uses 'selected' and 'show_count' keys, if they exist. @see wp_dropdown_categories()
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$cat_name = apply_filters('list_issuem_cats', $category->name, $category);
		$output .= "\t<option class=\"level-$depth\" value=\"".$category->slug."\"";
		//if ( $category->slug === $args['selected'] )
		//	$output .= ' selected="selected"';
		$output .= '>';
		$output .= $pad.$cat_name;
		if ( $args['show_count'] )
			$output .= '&nbsp;&nbsp;('. $category->count .')';
		$output .= "</option>\n";
	}
}
