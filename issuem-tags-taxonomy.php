<?php
/**
 * Registers IssueM Article, Tags Taxonomy w/ Meta Boxes
 *
 * @package IssueM
 * @since 1.0.0
 */
 
if ( !function_exists( 'create_issuem_tags_taxonomy' ) ) {
		
	/**
	 * Registers Article Category taxonomy for IssueM
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be issuem_article_tags
	 */
	function create_issuem_tags_taxonomy() {
		
	  $labels = array(
	  
			'name' 					=> __( 'Article Tags', 'issuem' ),
			'singular_name' 		=> __( 'Article Tag', 'issuem' ),
			'search_items' 			=> __( 'Search Article Tags', 'issuem' ),
			'all_items' 			=> __( 'All Article Tags', 'issuem' ), 
			'parent_item' 			=> __( 'Parent Article Tag', 'issuem' ),
			'parent_item_colon' 	=> __( 'Parent Article Tag:', 'issuem' ),
			'edit_item' 			=> __( 'Edit Article Tag', 'issuem' ), 
			'update_item' 			=> __( 'Update Article Tag', 'issuem' ),
			'add_new_item' 			=> __( 'Add New Article Tag', 'issuem' ),
			'new_item_name' 		=> __( 'New Article Tag', 'issuem' ),
			'menu_name' 			=> __( 'Article Tags', 'issuem' )
			
		); 	
	
		register_taxonomy(
			'issuem_issue_tags', 
			array( ), 
			array(
				'hierarchical' 	=> false,
				'labels' 		=> $labels,
				'show_ui' 		=> true,
				'show_tagcloud' => true,
				'query_var' 	=> true,
				'rewrite' 		=> array( 'slug' => 'article-tags' ),
				'capabilities' 	=> array(
						'manage_terms' 	=> 'manage_article_tags',
						'edit_terms' 	=> 'manage_article_tags',
						'delete_terms' 	=> 'manage_article_tags',
						'assign_terms' 	=> 'edit_issues'
						)
						
			)
		);
		
	}
	add_action( 'init', 'create_issuem_tags_taxonomy', 0 );
	
}