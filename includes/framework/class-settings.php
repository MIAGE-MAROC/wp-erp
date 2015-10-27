<?php

/**
 * ERP Admin settings class
 */
class ERP_Admin_Settings {

    private static $settings = array();
    private static $section = array();

    /**
     * Include all settings file
     *
     * @since 0.1
     *
     * @return array
     */
    public static function get_settings() {

        if ( !self::$settings ) {

            $settings = array();

            $settings[] = include __DIR__ . '/settings/general.php';
           // $settings[] = include __DIR__ . '/settings/design.php';
           // $settings[] = include __DIR__ . '/settings/sharing.php';
           // $settings[] = include __DIR__ . '/settings/content.php';
            //$settings[] = include __DIR__ . '/settings/api-keys.php';
            $settings[] = include __DIR__ . '/settings/example.php';

            self::$settings = apply_filters( 'erp_settings_pages', $settings );
        }
        return self::$settings;
    }

    /**
     * Get current tab and subtab/section
     *
     * @since 0.1
     *
     * @return array()
     */
    public static function get_current_tab_and_section() {

        $settings        = self::get_settings();
        $query_arg       = array( 'tab' => false, 'subtab' => false );

        if ( ! isset( $settings[0] ) ) {
            return $query_arg;
        }

        if ( empty( $settings[0]->get_id() ) ) {
            return $query_arg;
        }

        $current_tab = $query_arg['tab']     = isset( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : $settings[0]->get_id();

        foreach ( $settings as $obj ) {
            $sections[$obj->get_id()] = isset( $obj->sections ) ? $obj->sections : array();
        }

        if ( ! isset( $sections[$current_tab] ) ) {
            return $query_arg;
        }

        if ( ! is_array( $sections[$current_tab] ) ) {
            return $query_arg;
        }

        if ( ! count( $sections[$current_tab] ) ) {
            return $query_arg;
        }

        $query_arg['subtab'] = isset( $_GET['section'] ) ? sanitize_title( $_GET['section'] ) : key( $sections[$current_tab] );

        return $query_arg;
    }

    /**
     * Show settings all tab and subtab fields
     *
     * @since 0.1
     *
     * @return void
     */
    public static function output() {
        global $current_section, $current_tab, $current_class;

        $settings        = self::get_settings();
        $query_arg       = self::get_current_tab_and_section();
        $current_tab     = $query_arg['tab'];
        $current_section = $query_arg['subtab'];
        $sections        = [];

        if ( ! $settings ) {
            return;
        }

        echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';

        foreach ( $settings as $obj ) {

            $url   = sprintf('admin.php?page=erp-settings&tab=%s', $obj->get_id() );
            $class = ( $current_tab == $obj->get_id() ) ? ' nav-tab-active' : '';
            if ( $current_tab == $obj->get_id() && $current_class === null ) {
                $current_class = $obj;
            }

            printf('<a class="nav-tab%s" href="%s">%s</a>', $class, $url, $obj->get_label() );
        }

        echo '</h2>';

        self::section_output();

        $current_class->save( $current_section );
        $current_class->output( $current_section );

    }

    /**
     * Show settings subtab fields
     *
     * @since 0.1
     *
     * @return void
     */
    public static function section_output() {
        $settings        = self::get_settings();
        $query_arg       = self::get_current_tab_and_section();
        $current_tab     = $query_arg['tab'];
        $current_section = $query_arg['subtab'];
        $sections        = [];

        if ( ! $current_section ) {
            return;
        }

        foreach ( $settings as $obj ) {
            $sections[$obj->get_id()] = isset( $obj->sections ) ? $obj->sections : array();
        }

        $tab_sections = $sections[$current_tab];

        // don't print sub-sections if only one section available
        if ( count( $tab_sections ) < 2 ) {
            return;
        }

        echo '<ul class="erp-subsubsub">';

            echo '<li>';
            foreach ( $tab_sections as $slug => $label ) {
                $url    = 'admin.php?page=erp-settings&tab='.$current_tab.'&section='.$slug;
                $class  = ( $current_section == $slug ) ? ' erp-nav-tab-active' : '';
                $link[] = '<a class="erp-nav-tab'.$class.'" href="'.$url.'">'.__( $label, 'wp-erp' ).'</a>';
            }

            echo implode( ' | </li><li>', $link );
            echo '</li>';

        echo '</ul>';

    }
}