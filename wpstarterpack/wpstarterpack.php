<?php
/**
 * Plugin Name:       WP Starter Pack
 * Description:       Fonctionnalités WordPress additionnelles : Config GTM, Changement URL WPadmin + Blocages tentatives connexion, Duplication pages & articles, XML-RPC Bloqué, Cacher version Wordpress.
 * Version:           1.0.0
 * Text Domain:       wpstarterpack
 * Author:            Guillaume BLANCO – WPSoluces
 * Author URI:        https://wpsoluces.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:      8.2
 */


if ( ! defined( 'ABSPATH' ) ) {
    // Sécurité : bloque tout accès direct.
    exit;
}

/* --------------------------------------------------------------------- */
/* Compatibilité PHP                                                     */
/* --------------------------------------------------------------------- */
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'WP Starter Pack requires PHP 8.2 or higher.', 'wpstarterpack' );
        echo '</p></div>';
    } );
    return;
}

/* ------------------------------------------------------------------------- */
/* Chemins utiles                                                            */
/* ------------------------------------------------------------------------- */
define( 'WPSP_PATH', __DIR__ );
define( 'WPSP_URL',  plugin_dir_url( __FILE__ ) );

/* ------------------------------------------------------------------------- */
/* Amorçage                                                                  */
/* ------------------------------------------------------------------------- */
require_once WPSP_PATH . '/Core/Init.php';
require_once WPSP_PATH . '/Core/Uninstall.php';

/**
 * Initialise tous les modules une fois les plugins chargés.
 */
function wpsp_boot(): void {
    \WPStarterPack\Core\Init::run();
}
add_action( 'plugins_loaded', 'wpsp_boot' );

/**
 * Activation du plugin : enregistre les règles de réécriture.
 */
function wpsp_activate(): void {
    wpsp_boot();
    flush_rewrite_rules( false );
}
register_activation_hook( __FILE__, 'wpsp_activate' );

/**
 * Désactivation du plugin : purge les réécritures.
 */
function wpsp_deactivate(): void {
    flush_rewrite_rules( false );
}
register_deactivation_hook( __FILE__, 'wpsp_deactivate' );

/**
 * Nettoyage complet lors de la désinstallation du plugin.
 */
function wpsp_uninstall(): void {
    \WPStarterPack\Core\Uninstall::run();
}
register_uninstall_hook( __FILE__, 'wpsp_uninstall' );
