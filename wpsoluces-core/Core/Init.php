<?php
namespace WPSolucesCore\Core;

/**
 * Initialise l’ensemble du MU-plugin.
 */
class Init {

    /**
     * Lance tout.
     */
    public static function run(): void {
        self::register_autoload();
        
        // Démarre chaque module ici
        // Set google tag manager
        \WPSolucesCore\Modules\TagManager\Controller::register();

        // Login redirect vers /connect -> /wp-admin et /wp-login.php sont interdit
        \WPSolucesCore\Modules\LoginRedirect\Controller::register();

        // Duplication Articles ou Pages -> actif par défaut
        \WPSolucesCore\Modules\DuplicatePost\Controller::register();
        
        // Lock à 5 tentatives pendant 30min -> même si le user n'existe pas !
        \WPSolucesCore\Modules\LoginLimiter\Controller::register();
        
        // Version Wordpress cachée -> Réduit le nombre d'attaques
        \WPSolucesCore\Modules\HideVersion\Controller::register();

        // Disable XmlRpc et ses pingbacks
        \WPSolucesCore\Modules\DisableXmlRpc\Controller::register();
    }

    /**
     * Autoloader PSR-4 minimaliste (sans Composer).
     */
    private static function register_autoload(): void {
        spl_autoload_register( function ( $class ) {
            // Préfixe de notre namespace
            $prefix = 'WPSolucesCore\\';

            // Ignore ce qui n’est pas dans notre plugin
            if ( str_starts_with( $class, $prefix ) === false ) {
                return;
            }

            // Chemin de base = dossier interne du plugin
            $base_dir = \WPSC_PATH . '/';

            // Classe relative (sans le préfixe)
            $relative_class = substr( $class, strlen( $prefix ) );

            // On transforme les \\ en / + .php
            $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

            if ( file_exists( $file ) ) {
                require $file;
            }
        } );
    }
}
