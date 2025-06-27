<?php
namespace WPStarterPack\Modules\LoginRedirect;

defined( 'ABSPATH' ) || exit;

/**
 * Vue : écran Réglages → URL de connexion
 */
class View {

    /**
     * Texte commun de la notice.
     */
    private static function get_notice_message(): string {
        return sprintf(
            /* translators: 1: /wp-login.php, 2: /wp-admin */
            esc_html__(
                'Une fois la fonction activée, les pages %1$s et %2$s afficheront un code 404 pour les visiteurs.',
                'wpstarterpack'
            ),
            '<code>/wp-login.php</code>',
            '<code>/wp-admin</code>'
        );
    }


    /**
     * Page complète
     */
    public static function render_page(): void {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'URL de connexion', 'wpstarterpack' ) . '</h1>';

        echo '<p class="notice notice-info" style="padding:12px 15px;">';
        echo self::get_notice_message();
        echo '</p>';

        echo '<p>';
        printf(
            esc_html__( 'Adresse de connexion actuelle : %s', 'wpstarterpack' ),
            '<code>' . esc_url( home_url( '/' . Model::slug() ) ) . '</code>'
        );
        echo '</p>';

        echo '<form method="post" action="options.php">';
        settings_fields( 'wpsp_lr_group' );
        do_settings_sections( 'wpsp-lr' );
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
        esc_html_e( 'Activer le déplacement de la connexion', 'wpstarterpack' );
        echo '</label>';
    }

    /**
     * Champ slug
     */
    public static function slug_field(): void {
        echo '<span class="wpsp-url-prefix">' . esc_url( home_url( '/' ) ) . '</span>';
        echo '<input type="text" name="' . esc_attr( Model::OPTION_SLUG ) . '" value="' . esc_attr( Model::slug() ) . '" class="regular-text" placeholder="' . esc_attr( Model::DEFAULT_SLUG ) . '" />';
    }
}
