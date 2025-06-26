<?php
/**
 * Plugin Name:       WPSoluces Core
 * Description:       Fonctionnalités WordPress additionnelles : Config GTM, Changement URL WPadmin + Blocages tentatives connexion, Duplication pages & articles, XML-RPC Bloqué, Cacher version Wordpress.
 * Version:           1.0.0
 * Author:            Guillaume BLANCO – WPSoluces
 * Author URI:        https://wpsoluces.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */


if ( ! defined( 'ABSPATH' ) ) {
	// Sécurité : bloque tout accès direct.
	exit;
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
