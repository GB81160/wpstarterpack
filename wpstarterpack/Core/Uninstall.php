<?php
namespace WPStarterPack\Core;

use WPStarterPack\Modules\TagManager\Model as TagManager;
use WPStarterPack\Modules\LoginLimiter\Model as LoginLimiter;
use WPStarterPack\Modules\LoginRedirect\Model as LoginRedirect;

/**
 * Centralise la désinstallation du plugin.
 */
class Uninstall {
    public static function run(): void {
        global $wpdb;

        // Autoload plugin classes if the plugin was not fully bootstrapped.
        spl_autoload_register( function ( $class ) {
            $prefix = 'WPStarterPack\\';
            if ( str_starts_with( $class, $prefix ) === false ) {
                return;
            }

            $base_dir = WPSP_PATH . '/';
            $relative_class = substr( $class, strlen( $prefix ) );
            $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

            if ( file_exists( $file ) ) {
                require $file;
            }
        } );

        // Options enregistrées par les modules
        $options = [
            LoginRedirect::OPTION_ACTIVE,
            LoginRedirect::OPTION_SLUG,
            LoginRedirect::OPTION_FLUSHED,
            TagManager::OPTION_KEY_ID,
            TagManager::OPTION_KEY_ACTIVE,
        ];

        foreach ( $options as $option ) {
            delete_option( $option );
            delete_site_option( $option );
        }

        // Transient du Tag Manager
        delete_transient( TagManager::TRANSIENT_KEY );
        delete_site_transient( TagManager::TRANSIENT_KEY );

        // Tous les transients du LoginLimiter
        if ( $wpdb instanceof \wpdb ) {
            $prefix = 'wpsp_ll_';
            $like   = $wpdb->esc_like( $prefix );
            $names  = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_{$like}%' OR option_name LIKE '_transient_timeout_{$like}%'" );
            foreach ( $names as $name ) {
                if ( str_starts_with( $name, '_transient_timeout_' ) ) {
                    delete_option( $name );
                } elseif ( str_starts_with( $name, '_transient_' ) ) {
                    $transient = substr( $name, 11 ); // _transient_
                    delete_transient( $transient );
                }
            }

            if ( is_multisite() ) {
                $site_names = $wpdb->get_col( "SELECT meta_key FROM {$wpdb->sitemeta} WHERE meta_key LIKE '_site_transient_{$like}%' OR meta_key LIKE '_site_transient_timeout_{$like}%'" );
                foreach ( $site_names as $name ) {
                    if ( str_starts_with( $name, '_site_transient_timeout_' ) ) {
                        delete_site_option( $name );
                    } elseif ( str_starts_with( $name, '_site_transient_' ) ) {
                        $transient = substr( $name, 16 ); // _site_transient_
                        delete_site_transient( $transient );
                    }
                }
            }
        }
    }
}