<?php
namespace WPSolucesCore\Modules\DisableXmlRpc;

defined( 'ABSPATH' ) || exit;

/**
 * Module DisableXmlRpc
 *
 * Désactive totalement XML-RPC, supprime les pingbacks/RSD/WLW/generator,
 * et bloque tout accès direct à xmlrpc.php.
 */
class Controller {

    /**
     * Appelé depuis Core\Init::run()
     */
    public static function register(): void {
        // 1. Désactive l’API XML-RPC
        add_filter( 'xmlrpc_enabled', '__return_false', 100 );

        // 2. Bloque tout accès direct à xmlrpc.php (init + parse_request)
        add_action( 'init',          [ __CLASS__, 'block_xmlrpc' ],      0 );
        add_action( 'parse_request', [ __CLASS__, 'block_xmlrpc' ],      0 );

        // 3. Supprime l’en-tête HTTP X-Pingback
        add_action( 'send_headers',  [ __CLASS__, 'remove_pingback_header' ], 0 );

        // 4. Nettoie les balises par défaut ajoutées dans <head>
        add_action( 'init', [ __CLASS__, 'cleanup_head_links' ], 1 );

        // 5. Empêche les pingbacks automatiques
        add_filter( 'pre_ping', '__return_false' );

        // 6. Neutralise bloginfo('pingback_url')
        add_filter( 'bloginfo_pingback_url', '__return_empty_string', 20 );

        // 7. Pour les thèmes qui codent en dur le <link rel="pingback"> avant wp_head()
        add_action( 'template_redirect', [ __CLASS__, 'start_buffer' ], 1 );
    }

    /**
     * Supprime toutes les actions par défaut qui injectent
     * RSD, WLW, pingback, feeds extras, REST link et generator.
     */
    public static function cleanup_head_links(): void {
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
        remove_action( 'wp_head', 'pingback_link' );
        remove_action( 'wp_head', 'feed_links_extra',       3 );
        remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
        remove_action( 'wp_head', 'wp_generator' );
    }

    /**
     * Si on est dans xmlrpc.php ou qu’on y accède directement → 403.
     */
    public static function block_xmlrpc(): void {
        if (
            ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ||
            ( isset( $_SERVER['SCRIPT_NAME'] ) && false !== stripos( $_SERVER['SCRIPT_NAME'], 'xmlrpc.php' ) ) ||
            ( isset( $_SERVER['REQUEST_URI']  ) && false !== stripos( $_SERVER['REQUEST_URI'],  'xmlrpc.php' ) )
        ) {
            wp_die(
                esc_html__( 'XML-RPC désactivé sur ce site.', 'wpsoluces' ),
                esc_html__( 'Accès interdit',            'wpsoluces' ),
                [ 'response' => 403 ]
            );
        }
    }

    /**
     * Supprime l’en-tête HTTP X-Pingback.
     */
    public static function remove_pingback_header(): void {
        header_remove( 'X-Pingback' );
    }

    /**
     * Démarre le buffer pour filtrer **tout** le HTML de la page
     * et enlever la balise hard-codée <link rel="pingback"...>.
     */
    public static function start_buffer(): void {
        ob_start( [ __CLASS__, 'remove_pingback_link_from_buffer' ] );
    }

    /**
     * Callback d’ob_end_flush() : on retire la <link rel="pingback">.
     *
     * @param string $buffer Le HTML complet de la page
     * @return string Le HTML nettoyé
     */
    public static function remove_pingback_link_from_buffer( string $buffer ): string {
        return preg_replace(
            '#<link[^>]+rel=[\'"]pingback[\'"][^>]*>\s*#i',
            '',
            $buffer
        );
    }
}
