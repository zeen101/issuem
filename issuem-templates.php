<?php
/**
 * Renders overridable templates
 *
 * @package IssueM
 * @since 1.2.4
 */

/**
 * Credits to: Stephen Harris
 * Src: https://github.com/stephenharris/Event-Organiser/blob/1.7.3/includes/event-organiser-templates.php
 */

if ( ! function_exists( 'issuem_render' ) ) {

    /**
     * Find a template by $file_name, allowing for overrides in the child and
     * parent themes, then return the rendered result
     *
     * @since 1.2.4
     *
     * @param string $file_name Name of template to render
     * @param array $data A hash array of variables to make available when
     *                    rendering the template
     * @return string the rendered template
     */
    function issuem_render( $file_name, $data ) {

        $template_path = issuem_get_template_path( $file_name );

        return issuem_process_render( $template_path, $data );
    }
}

if ( ! function_exists( 'issuem_get_template_path' ) ) {

    /**
     * Return the path to a template allowing overrides by child and parent
     * themes
     *
     * @since 1.2.4
     *
     * @param string $file_name Name of template to find
     * @return string path to template found
     */
    function issuem_get_template_path( $file_name ) {
        $stack = array(
            ISSUEM_PATH_CHILD_THEME,
            ISSUEM_PATH_PARENT_THEME,
            ISSUEM_PATH,
        );
        return issuem_search_file_path( $file_name, $stack );
    }
}

if ( ! function_exists( 'issuem_get_asset_path' ) ) {

    /**
     * Return the URL to an asset allowing overrides by child and parent themes
     *
     * @since 1.2.4
     *
     * @param string $file_name Name of template to find
     * @return string path to template found
     */
    function issuem_get_asset_path( $file_name ) {
        $stack = array(
            ISSUEM_PATH_CHILD_THEME     => ISSUEM_URL_CHILD_THEME,
            ISSUEM_PATH_PARENT_THEME    => ISSUEM_URL_PARENT_THEME,
            ISSUEM_PATH                 => ISSUEM_URL,
        );
        return issuem_search_url_path( $file_name, $stack );
    }
}

if ( ! function_exists( 'issuem_search_url_path' ) ) {

    /**
     * Search through a path stack (dir, url) for the first location to match
     * the dir + file name and return the url + file name
     *
     * When only file paths are needed, use issuem_search_file_path()
     *
     * @since 1.2.4
     *
     * @param string $file_name The file to search for
     * @param array $stack A hash array of dir, url locations to search
     * @return string path to first file found
     */
    function issuem_search_url_path( $file_name, $stack ) {
        foreach ( $stack as $dir => $url ) {
            if ( file_exists( trailingslashit( $dir ) . $file_name ) )
                return trailingslashit( $url ) . $file_name;
        }
    }
}


if ( ! function_exists( 'issuem_search_file_path' ) ) {

    /**
     * Search through a path array for the first location to contain the file
     * name and return dir + filename
     *
     * @since 1.2.4
     *
     * @param string $file_name The file to search for
     * @param array $shallow_stack An ordered array of file locations to search
     * @return string path to first file found
     */
    function issuem_search_file_path( $file_name, $shallow_stack ) {
        $stack = array();
        foreach( $shallow_stack as $dir ) {
            $stack[ $dir ] = $dir;
        }
        return issuem_search_url_path( $file_name, $stack );
    }
}


if ( ! function_exists( 'issuem_render' ) ) {

    /**
     * Template rendering is done by extracting provided variables and simply
     * including a PHP file
     *
     * @since 1.2.4
     *
     * @param string $file_path The PHP file to include
     * @param array $view_data  A hash array of of variabled to be
     *                          accessable locally
     * @return string rendered file contents
     */
    function issuem_process_render( $file_path, $view_data = null ) {

        ( $view_data ) ? extract( $view_data ) : null;

        ob_start();
        include ( $file_path );
        $template = ob_get_contents();
        ob_end_clean();

        return $template;
    }
}
