<div id="issuem-featured-article-slideshowholder">
    <div class="flexslider">
        <ul class="slides">
            <?php foreach( $articles as $article ) { if ( $article->has_image() ) { ?>
            <li>
                <a href="<?php echo $article->permalink(); ?>"><img src="<?php echo $article->image_src(); ?>" alt="<?php echo $article->image_alt(); ?>" /></a>
                <div class="flex-caption" style="width:<?php echo $settings['featured_image_width']; ?>px;">
                    <span class="featured_slider_title"><?php  echo ( $show_title  ) ? $article->title()  : ''; ?></span>
                    <span class="featured_slider_teaser"><?php echo ( $show_teaser ) ? $article->teaser() : ''; ?></span>
                    <span class="featured_slider_byline"><?php echo ( $show_byline ) ? $article->byline() : ''; ?></span>
                </div>
            </li>
            <?php } } ?>
        </ul>
    </div>
</div>
<script type='text/javascript'>
jQuery( window ).load( function(){
    jQuery( '.flexslider' ).flexslider({
        animation: 'slide',
        start: function(slider){
            jQuery('body').removeClass('loading');
        },
        controlNav: false,
        directionNav: false
    });
});
</script>
