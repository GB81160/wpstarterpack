<?php
/**
 * Plugin Name:       WPSoluces Core
 * Description:       Fonctionnalités WordPress additionnelles : Config GTM, Changement URL WPadmin + Blocages tentatives connexion, Duplication pages & articles, XML-RPC Bloqué, Cacher version Wordpress.
 * Version:           1.0.0
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
        echo esc_html__( 'WPSoluces Core requires PHP 8.2 or higher.', 'wpsoluces' );
        echo '</p></div>';
    } );
    return;
}

/* ------------------------------------------------------------------------- */
/* Chemins utiles                                                            */
/* ------------------------------------------------------------------------- */
define( 'WPSC_PATH', __DIR__ );
define( 'WPSC_URL',  plugin_dir_url( __FILE__ ) );

/* ------------------------------------------------------------------------- */
/* Amorçage                                                                  */
/* ------------------------------------------------------------------------- */
require_once WPSC_PATH . '/Core/Init.php';

/**
 * Initialise tous les modules une fois les plugins chargés.
 */
function wpsc_core_boot(): void {
    \WPSolucesCore\Core\Init::run();
}
add_action( 'plugins_loaded', 'wpsc_core_boot' );

/**
 * Activation du plugin : enregistre les règles de réécriture.
 */
function wpsc_core_activate(): void {
    wpsc_core_boot();
    flush_rewrite_rules( false );
}
register_activation_hook( __FILE__, 'wpsc_core_activate' );

/**
 * Désactivation du plugin : purge les réécritures.
 */
function wpsc_core_deactivate(): void {
    flush_rewrite_rules( false );
}
register_deactivation_hook( __FILE__, 'wpsc_core_deactivate' );

/**
 * Nettoyage complet lors de la désinstallation du plugin.
 */
function wpsc_core_uninstall(): void {
    global $wpdb;

    // Options enregistrées par les modules
    $options = [
        'wpsc_lr_active',
        'wpsc_lr_rewrite_flushed',
        'wpsc_gtm_id',
        'wpsc_gtm_active',
    ];

    foreach ( $options as $option ) {
        delete_option( $option );
        delete_site_option( $option );
    }

    // Transient de configuration GTM
    delete_transient( 'wpsc_gtm_settings' );
    delete_site_transient( 'wpsc_gtm_settings' );

    // Tous les transients du LoginLimiter
    if ( $wpdb instanceof \wpdb ) {
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpsc_ll_%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wpsc_ll_%'" );
    }
}
register_uninstall_hook( __FILE__, 'wpsc_core_uninstall' );
