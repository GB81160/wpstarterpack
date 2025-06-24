<?php
namespace WPSolucesCore\Modules\HideVersion;

defined( 'ABSPATH' ) || exit;

/**
 * Module HideVersion
 *
 * Se contente de vider complètement la sortie de `the_generator`.
 */
class Controller {

    /**
     * À appeler depuis Core\Init::run()
     */
    public static function register(): void {
        // Retire le meta generator dans le <head>
        remove_action( 'wp_head', 'wp_generator' );

        // Vide toute sortie de the_generator()
        add_filter( 'the_generator', [ __CLASS__, 'remove_generator' ], 10 );
    }

    /**
     * Callback qui retourne systématiquement une chaîne vide.
     */
    public static function remove_generator(): string {
        return '';
    }
}
