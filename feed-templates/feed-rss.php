<?php
/**
 * Custom Feed Template for IssueM Issues
 *
 * @package IssueM
 * @since 1.0.2
 */

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
$more = 1;

$issue = get_query_var( 'issue' );
if ( empty( $issue ) )
	$issue = get_active_issuem_issue();
				
$args = array(
	'posts_per_page'	=> -1,
	'post_type'			=> 'article',
	'orderby'			=> 'post_date',
	'order'				=> 'ASC',
	'issuem_issue'		=> $issue
);

query_posts( $args );

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
<rss version="0.92">
<channel>
	<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss('description') ?></description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<docs>http://backend.userland.com/rss092</docs>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<?php do_action('rss_head'); ?>

<?php while (have_posts()) : the_post(); ?>
	<item>
		<title><?php the_title_rss() ?></title>
		<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
		<link><?php the_permalink_rss() ?></link>
		<?php do_action('rss_item'); ?>
	</item>
<?php endwhile; ?>
</channel>
</rss>
