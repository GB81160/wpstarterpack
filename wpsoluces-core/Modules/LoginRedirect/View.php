<?php
namespace WPSolucesCore\Modules\LoginRedirect;

defined( 'ABSPATH' ) || exit;

/**
 * Vue : écran Réglages → URL de connexion
 */
class View {

	/**
	 * Page complète
	 */
    public static function render_page(): void {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'URL de connexion', 'wpsoluces' ) . '</h1>';

        echo '<p class="notice notice-info" style="padding:12px 15px;">';
        printf(
            /* translators: %s = nouvelle URL de connexion */
            esc_html__(
                'Une fois la fonction activée, les pages %1$s et %2$s afficheront un code 404 pour les visiteurs. '
                . 'La nouvelle adresse de connexion sera : %3$s',
                'wpsoluces'
            ),
            '<code>/wp-login.php</code>',
            '<code>/wp-admin</code>',
            '<code>' . esc_url( home_url( '/connect' ) ) . '</code>'
        );
        echo '</p>';

        echo '<form method="post" action="options.php">';
        settings_fields( 'wpsc_lr_group' );
        do_settings_sections( 'wpsc-lr' );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

	/**
	 * Case à cocher Activer / Désactiver
	 */
    public static function checkbox_field(): void {
        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr( Model::OPTION_ACTIVE ) . '" value="1" ';
        checked( Model::is_active() );
        echo '/> ';
        esc_html_e( 'Activer le déplacement de la connexion vers /connect', 'wpsoluces' );
        echo '</label>';
    }
}
