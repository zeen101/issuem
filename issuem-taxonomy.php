<?php
/**
 * Registers IssueM Issue Taxonomy w/ Meta Boxes
 *
 * @package IssueM
 * @since 1.0.0
 */
 
if ( !function_exists( 'create_issuem_cats_taxonomy' ) ) {
		
	/**
	 * Registers IssueM Issue Taxonomy
	 *
	 * @since 1.0.0
	 */
	function create_issue_taxonomy() {
		
	  $labels = array(
	  
			'name' 				=> __( 'Issues', 'issuem' ),
			'singular_name' 	=> __( 'Issue', 'issuem' ),
			'search_items'		=>  __( 'Search Issues', 'issuem' ),
			'all_items' 		=> __( 'All Issues', 'issuem' ), 
			'parent_item' 		=> __( 'Parent Issues', 'issuem' ),
			'parent_item_colon' => __( 'Parent Issues:', 'issuem' ),
			'edit_item' 		=> __( 'Edit Issues', 'issuem' ), 
			'update_item' 		=> __( 'Update Issues', 'issuem' ),
			'add_new_item' 		=> __( 'Add New Issues', 'issuem' ),
			'new_item_name' 	=> __( 'New Issue', 'issuem' ),
			'menu_name' 		=> __( 'Issues', 'issuem' )
			
		); 	
	
		register_taxonomy(
			'issuem_issue', 
			array( 'article' ), 
			array(
				'hierarchical' 	=> true,
				'labels' 		=> $labels,
				'show_ui' 		=> true,
				'query_var' 	=> true,
				'rewrite' 		=> array( 'slug' => 'issue' ),
				'capabilities' 	=> array(
						'manage_terms' 	=> 'manage_issues',
						'edit_terms' 	=> 'manage_issues',
						'delete_terms' 	=> 'manage_issues',
						'assign_terms' 	=> 'edit_issues'
						)
						
			)
		);
		
	}
	add_action( 'init', 'create_issue_taxonomy', 0 );

}

if ( !function_exists( 'issuem_issue_columns' ) ) {
		
	/**
	 * Filters column headings for Issues
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	function issuem_issue_columns( $columns ) {
		
		$columns['issue_order'] = __( 'Issue Order', 'issuem' );
		$columns['issue_status'] = __( 'Issue Status', 'issuem' );
	
		return $columns;
		
	}
	add_filter( 'manage_edit-issuem_issue_columns', 'issuem_issue_columns', 10, 1 );

}

if ( !function_exists( 'issuem_issue_sortable_columns' ) ) {
		
	/**
	 * Filters sortable columns
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	function issuem_issue_sortable_columns( $columns ) {
		
		$columns['issue_order'] = 'issue_order';
	
		return $columns;
		
	}
	add_filter( 'manage_edit-issuem_issue_sortable_columns', 'issuem_issue_sortable_columns', 10, 1 );

}

if ( !function_exists( 'issuem_issue_sortable_column_orderby' ) )  {
	
	/**
	 * Filters sortable columns
	 *
	 * @since 1.0.0
	 * @todo there is a better way to do this sort
	 *
	 * @param array $terms
	 * @param array $taxonomies
	 * @param array $args
	 * @return array Of sorted terms
	 */
	function issuem_issue_sortable_column_orderby( $terms, $taxonomies, $args ) {
	
		global $hook_suffix;
		
		if ( 'edit-tags.php' == $hook_suffix && in_array( 'issuem_issue', $taxonomies ) 
				&& ( empty( $_GET['orderby'] ) && !empty( $args['orderby'] ) 
						|| ( !empty( $args['orderby'] ) && 'issue_order' == $args['orderby'] ) ) ) {
				
			$sort = array();
			$count = 0;
		
			foreach ( $terms as $issue ) {
				
				$issue_meta = get_option( 'issuem_issue_' . $issue->term_id . '_meta' );
			
				if ( !empty( $issue_meta['issue_order'] ) )
					$sort[ $issue_meta['issue_order'] ] = $issue;
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
	add_filter( 'get_terms', 'issuem_issue_sortable_column_orderby', 10, 3 );

}

if ( !function_exists( 'manage_issuem_issue_custom_column' ) )  {
	
	/**
	 * Sets data for custom article cateagory columns
	 *
	 * @since 1.0.0
	 * @todo there is a better way to do this sort
	 *
	 * @param mixed $blank
	 * @param string $column_name
	 * @param int $term_id
	 *
	 * @return mixed Value of column for given term ID.
	 */
	function manage_issuem_issue_custom_column( $blank, $column_name, $term_id ) {
		
		$issue_meta = get_option( 'issuem_issue_' . $term_id . '_meta' );
	
		if ( !empty( $issue_meta[$column_name] ) )
			return $issue_meta[$column_name];
		else
			return '';
	
	}
	add_filter( "manage_issuem_issue_custom_column", 'manage_issuem_issue_custom_column', 10, 3 );
	
}

if ( !function_exists( 'issuem_issue_taxonomy_add_form_fields' ) )  {
	
	/**
	 * Outputs HTML for new form fields in Issues
	 *
	 * @since 1.0.0
	 */
	function issuem_issue_taxonomy_add_form_fields() {
		
		?>	
		
		<div class="form-field">
			<label for="issue_status"><?php _e( 'Issue Status', 'issuem' ); ?></label>
			<?php echo get_issuem_issue_statuses(); ?>
		</div>
		
		<div class="form-field">
			<label for="issue_order"><?php _e( 'Issue Order', 'issuem' ); ?></label>
			<input type="text" name="issue_order" id="issue_order" />
		</div>
		
		<?php
		
	}
	add_action( 'issuem_issue_add_form_fields', 'issuem_issue_taxonomy_add_form_fields' );

}

if ( !function_exists( 'get_issuem_issue_statuses' ) )  {
	
	/**
	 * Outputs HTML for IssueM Issue statuses
	 *
	 * @since 1.0.0
	 *
	 * @param string $select Currently selected option
	 * @return string select HTML of available statuses
	 */
	function get_issuem_issue_statuses( $select = false ) {
		
		$statuses = apply_filters( 'issuem_issue_statuses', array( __( 'Draft', 'issuem' ), __( 'Live', 'issuem' ), __( 'PDF Archive', 'issuem' ) ) );
		
		$html = '<select name="issue_status" id="issue_status">';
		foreach ( $statuses as $status ) {
		
			$html .= '<option value="' . $status . '" ' . selected( $select, $status, false ) . '>' . $status . '</option>';
			
		}
		$html .= '</select>';
		
		return $html;
		
	}

}

if ( !function_exists( 'issuem_issue_taxonomy_edit_form_fields' ) )  {
	
	/**
	 * Outputs HTML for new form fields in Issue (on Edit form)
	 *
	 * @since 1.0.0
	 */
	function issuem_issue_taxonomy_edit_form_fields( $tag, $taxonomy ) {
		
		$defaults = array(
						'issue_status'		=> '',
						'issue_order'		=> '',
						'cover_image'		=> '',
						'pdf_version'		=> '',
						'external_link'		=> '',
						'external_pdf_link'	=> '',
					);
		$issue_meta = get_option( 'issuem_issue_' . $tag->term_id . '_meta' );
		$issue_meta = wp_parse_args( $issue_meta, $defaults );
		
		?>	
			
		<tr class="form-field">
		<th valign="top" scope="row"><?php _e( 'Issue Status', 'issuem' ); ?></th>
		<td><?php echo get_issuem_issue_statuses( $issue_meta['issue_status'] ); ?></td>
		</tr>
		
		<tr class="form-field">
		<th valign="top" scope="row"><?php _e( 'Issue Order', 'issuem' ); ?></th>
		<td><input type="text" name="issue_order" id="issue_order" value="<?php echo $issue_meta['issue_order'] ?>" /></td>
		</tr>
		
		<?php
			if ( !empty( $_GET['remove_cover_image'] ) ) {
			
				wp_delete_attachment( $issue_meta['cover_image'] );
				$issue_meta['cover_image'] = '';
				update_option( 'issuem_issue_' . $tag->term_id . '_meta', $issue_meta );
				
			}
		
			if ( !empty( $issue_meta['cover_image'] ) ) {
			
				$view_image = '<p>' . wp_get_attachment_image( $issue_meta['cover_image'], 'issuem-cover-image' ) . '</p>';
				$remove_image = '<p><a href="?' . http_build_query( wp_parse_args( array( 'remove_cover_image' => $issue_meta['cover_image'] ), $_GET ) ) . '">' . __( 'Remove Cover Image', 'issuem' ) . '</a></p>';
			
			} else {
				
				$view_image = '';
				$remove_image = '';
				
			}
		?>
		
		<tr class="form-field">
		<th valign="top" scope="row"><?php _e( 'Cover Image', 'issuem' ); ?></th>
		<td><input type="file" name="cover_image" id="cover_image" value="" /><?php echo $view_image . $remove_image; ?></td>
		</tr>
		
		<?php
			if ( !empty( $_GET['remove_pdf_version'] ) ) {
			
				wp_delete_attachment( $issue_meta['pdf_version'] );
				$issue_meta['pdf_version'] = '';
				update_option( 'issuem_issue_' . $tag->term_id . '_meta', $issue_meta );
				
			}
		
			if ( !empty( $issue_meta['pdf_version'] ) ) {
			
				$view_pdf = '<p><a target="_blank" href="' . wp_get_attachment_url( $issue_meta['pdf_version'] ) . '">' . __( 'View PDF Version', 'issuem' ) . '</a></p>';
				$remove_pdf = '<p><a href="?' . http_build_query( wp_parse_args( array( 'remove_pdf_version' => $issue_meta['pdf_version'] ), $_GET ) ) . '">' . __( 'Remove PDF Version', 'issuem' ) . '</a></p>';
			
			} else {
				
				$view_pdf = '';
				$remove_pdf = '';
				
			}
		?>
		
		<tr class="form-field">
		<th valign="top" scope="row"><?php _e( 'External Issue Link', 'issuem' ); ?></th>
		<td><input type="text" name="external_link" id="external_link" value="<?php echo $issue_meta['external_link'] ?>" />
		<p class="description">Leave empty if you do not want your issue to link to an external source.</p>
		</td>
		</tr>
		
		<tr class="form-field">
		<th valign="top" scope="row"><?php _e( 'PDF Version', 'issuem' ); ?></th>
		<td>
        <input type="file" name="pdf_version" id="pdf_version" value="" />
		<?php 
		echo $view_pdf . $remove_pdf; 
		echo apply_filters( 'issuem_pdf_version', '', __( 'Issue-to-PDF Generated PDF', 'issuem' ), $tag );
		?>
        </td>
		</tr>
		
		<tr class="form-field">
		<th valign="top" scope="row"><?php _e( 'External PDF Link', 'issuem' ); ?></th>
		<td><input type="text" name="external_pdf_link" id="external_pdf_link" value="<?php echo $issue_meta['external_pdf_link'] ?>" />
		<p class="description">Leave empty if you do not want your PDF to link to an external source.</p>
		</td>
		</tr>
		
		<?php
		
	}
	add_action( 'issuem_issue_edit_form_fields', 'issuem_issue_taxonomy_edit_form_fields', 10, 2 );

}

if ( !function_exists( 'save_issuem_issue_meta' ) ) {
		
	/**
	 * Saves form fields for Issues taxonomy
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be issuem_article_categories
	 *
	 * @param int $term_id Term ID
	 * @param int $taxonomy_id Taxonomy ID
	 */
	function save_issuem_issue_meta( $term_id, $taxonomy_id ) {
	
		$issue_meta = get_option( 'issuem_issue_' . $term_id . '_meta' );
		
		if ( !empty( $_POST['issue_status'] ) ) 
			$issue_meta['issue_status'] = $_POST['issue_status'];
		
		if ( !empty( $_POST['issue_order'] ) ) 
			$issue_meta['issue_order'] = $_POST['issue_order'];
		
		if ( !empty( $_FILES['cover_image']['name'] ) ) {
			
			require_once(ABSPATH . 'wp-admin/includes/admin.php'); 
			$id = media_handle_upload( 'cover_image', 0 ); //post id of Client Files page  
			 
			if ( is_wp_error($id) ) {  
				$errors['upload_error'] = $id;  
				$id = false;  
			}
			
			$issue_meta['cover_image'] = $id;
			
		}
		
		if ( !empty( $_FILES['pdf_version']['name'] ) ) {
			
			require_once(ABSPATH . 'wp-admin/includes/admin.php'); 
			$id = media_handle_upload( 'pdf_version', 0 ); //post id of Client Files page  
			 
			if ( is_wp_error($id) ) {  
				$errors['upload_error'] = $id;  
				$id = false;  
			}
			
			$issue_meta['pdf_version'] = $id;
			
		}
		
		$issue_meta['external_link'] = !empty( $_POST['external_link'] ) ? $_POST['external_link'] : '';
		$issue_meta['external_pdf_link'] = !empty( $_POST['external_pdf_link'] ) ? $_POST['external_pdf_link'] : '';
	
		update_option( 'issuem_issue_' . $term_id . '_meta', $issue_meta );
		
	}
	add_action( 'created_issuem_issue', 'save_issuem_issue_meta', 10, 2 );
	add_action( 'edited_issuem_issue', 'save_issuem_issue_meta', 10, 2 );

}


if ( !function_exists( 'get_issuem_draft_issues' ) )  {
	
	/**
	 * Outputs array of Issue Terms IDs for IssueM Issue statuses set to Draft
	 *
	 * @since 1.0.0
	 *
	 * @return array Draft Issues
	 */
	function get_issuem_draft_issues() {
		
		global $wpdb;
		
		$term_ids = array();
		
		$term_option_names = $wpdb->get_col( 'SELECT option_name FROM ' . $wpdb->options . ' WHERE option_name LIKE "issuem_issue_%_meta" AND option_value LIKE "%Draft%"' );
		
		foreach( $term_option_names as $name )
			if ( preg_match( '/issuem_issue_(\d+)_meta/', $name, $matches ) )
				$term_ids[] = $matches[1];
		
		return $term_ids;
		
	}

}
