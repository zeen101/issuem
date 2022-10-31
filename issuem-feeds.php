<?php
/**
 * This script modifies and displays the default feed templates for IssueM Article feeds.
 *
 * @package IssueM
 * @since 1.0.2
 */
 
if ( ! function_exists( 'do_issuem_feed_rdf' ) ) {
	
	/**
	 * Load the RDF RSS 0.91 Feed template customized for IssueM Issues
	 *
	 * @since 1.0.2
	 */
	function do_issuem_feed_rdf() {
				
		if ( get_query_var( 'post_type' ) == 'article' ) {
			
			load_template( ISSUEM_PATH . '/feed-templates/feed-rdf.php' );
			
			remove_action( 'do_feed_rdf', 'do_feed_rdf', 10, 1 );
			
		}
	}
	add_action( 'do_feed_rdf', 'do_issuem_feed_rdf', 1, 1 );

}

if ( ! function_exists( 'do_issuem_feed_atom' ) ) {
	
	/**
	 * Load either Atom comment feed or Atom posts feed template customized for IssueM Issues
	 *
	 * @since 1.0.2
	 *
	 * @param bool $for_comments false for normal all article feeds
	 */
	function do_issuem_feed_atom( $for_comments ) {
		
		if ( $for_comments ) {
			return;
		}
		
		if ( get_query_var( 'post_type' ) == 'article' ) {
		
			load_template( ISSUEM_PATH . '/feed-templates/feed-atom.php' );
			
			remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1 );
			
		}
	}
	add_action( 'do_feed_atom', 'do_issuem_feed_atom', 1, 1 );

}

if ( ! function_exists( 'do_issuem_feed_rss' ) ) {
	
	/**
	 * Load the RSS 1.0 feed template customized for IssueM Issues
	 *
	 * @since 1.0.2
	 */
	function do_issuem_feed_rss() {
				
		if ( get_query_var( 'post_type' ) == 'article' ) {
			
			load_template( ISSUEM_PATH . '/feed-templates/feed-rss.php' );
			
			remove_action( 'do_feed_rss', 'do_feed_rss', 10, 1 );
			
		}
	}
	add_action( 'do_feed_rss', 'do_issuem_feed_rss', 1, 1 );
	
}

if ( ! function_exists( 'do_issuem_feed_rss2' ) ) {
	
	/**
	 * Load either the RSS2 comment feed or the RSS2 posts feed template customized for IssueM Issues
	 *
	 * @since 1.0.2
	 *
	 * @param bool $for_comments false for normal all article feeds
	 */
	function do_issuem_feed_rss2( $for_comments ) {
				
		if ( $for_comments ) {
			return;
		}
		
		if ( get_query_var( 'post_type' ) == 'article' ) {
		
			load_template( ISSUEM_PATH . '/feed-templates/feed-rss2.php' );
			
			remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );
			
		}
	}
	add_action( 'do_feed_rss2', 'do_issuem_feed_rss2', 1, 1 );

}
