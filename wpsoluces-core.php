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
define( 'WPSC_PATH', __DIR__ . '/wpsoluces-core' );
define( 'WPSC_URL',  plugin_dir_url( __FILE__ ) . 'wpsoluces-core' );

/* ------------------------------------------------------------------------- */
/* Amorçage                                                                  */
/* ------------------------------------------------------------------------- */
require_once WPSC_PATH . '/Core/Init.php';
\WPSolucesCore\Core\Init::run();
