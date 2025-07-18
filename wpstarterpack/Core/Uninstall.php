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
        LoginLimiter::delete_all_transients();
    }
}