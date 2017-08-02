<?php
/**
 * Plugin Name:     Easy Digital Downloads - BadgeOS integration
 * Plugin URI:      https://wordpress.org/plugins/edd-badgeos
 * Description:     Connect BadgeOS with Easy Digital Downloads
 * Version:         1.0.1
 * Author:          Tsunoa
 * Author URI:      https://tsunoa.com
 * Text Domain:     edd-badgeos
 *
 * @package         EDD\BadgeOS
 * @author          Tsunoa
 * @copyright       Copyright (c) Tsunoa
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_BadgeOS' ) ) {

    /**
     * Main EDD_BadgeOS class
     *
     * @since       1.0.0
     */
    class EDD_BadgeOS {

        /**
         * @var         EDD_BadgeOS $instance The one true EDD_Download_Pages
         * @since       1.0.0
         */
        private static $instance;

        /**
         * @var         EDD_BadgeOS_Triggers BadgeOS EDD Addon triggers
         * @since       1.0.0
         */
        protected $triggers;

        /**
         * @var         EDD_BadgeOS_Listeners BadgeOS EDD Addon listeners
         * @since       1.0.0
         */
        protected $listeners;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_BadgeOS
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_BadgeOS();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
            }

            return self::$instance;
        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_BADGEOS', '1.0.0' );

            // Plugin path
            define( 'EDD_BADGEOS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_BADGEOS_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @since 1.0.0
         */
        private function includes() {
            require_once EDD_BADGEOS_DIR . 'includes/triggers.php' ;
            require_once EDD_BADGEOS_DIR . 'includes/listeners.php' ;

            $this->triggers = new EDD_BadgeOS_Triggers();
            $this->listeners = new EDD_BadgeOS_Listeners();
        }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_BADGEOS_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_badgeos_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-badgeos' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-badgeos', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-badgeos/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-badgeos/ folder
                load_textdomain( 'edd-badgeos', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-badgeos/languages/ folder
                load_textdomain( 'edd-badgeos', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-badgeos', false, $lang_dir );
            }
        }

    }
}

/**
 * The main function responsible for returning the one true EDD_BadgeOS instance
 *
 * @since       1.0.0
 * @return      \EDD_BadgeOS The one true EDD_BadgeOS
 */
function edd_badgeos() {
    return EDD_BadgeOS::instance();
}
add_action( 'plugins_loaded', 'edd_badgeos' );
