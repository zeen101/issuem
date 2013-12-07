<?php

if ( ! class_exists( 'IssuemArticle' ) ) {

    class IssuemArticle {

        public $ID;
        public $object;
        public $settings;
        public $issue;
        public $authors;

        public static function from_object( $object ) {
            $item = new InspireArticle( $object->ID );
            $item->object = $object;
            return $item;
        }

        public function __construct( $ID ) {
            $this->ID = $ID;
        }

        public function get_object() {
            if ( ! $this->object )
                $this->object = get_post( $this->ID );
            return $this->object;
        }

        public function get_settings() {
            if ( ! $this->settings )
                $this->settings = get_issuem_settings();
            return $this->settings;
        }

        // ------------------------------------------------------------------------

        public function has_image() {
            return has_post_thumbnail( $this->ID );
        }

        public function permalink() {
            return get_permalink( $this->get_object() );
        }

        public function title() {
            return $this->get_object()->post_title;
        }

        public function teaser() {
            return get_post_meta( $this->ID, '_teaser_text', true );
        }

        public function byline() {
            $author_name = get_issuem_author_name( $this->get_object() );
            return sprintf( __( 'By %s', 'issuem' ), apply_filters( 'issuem_author_name', $author_name, $this->ID ) );
        }

        public function image_alt() {
            return $this->title() . ' ' . $this->teaser() . ' ' . $this->byline();
        }

        public function image_src( $size = 'issuem-featured-rotator-image' ) {
            return wp_get_attachment_image_src( get_post_thumbnail_id( $this->ID ), $size );
        }



        public function has_authors() {
            return count( $this->get_authors() ) > 0;
        }

        public function feature_img( $size = 'inspire-primary-feature-image' ) {

            $id = get_post_thumbnail_id( $this->ID );
            if ( $id == '' ) return;
            return wp_get_attachment_image( $id, $size );
        }

        public function excerpt() {
            $excerpt = $this->get_object()->post_excerpt;
            if ( trim( $excerpt ) == '' )
                $excerpt = $this->get_object()->post_content;

            return wp_trim_words( $excerpt );
        }
    }
}
