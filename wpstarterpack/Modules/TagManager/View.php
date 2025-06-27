<?php
namespace WPStarterPack\Modules\TagManager;

defined( 'ABSPATH' ) || exit;

class View {

    /** Page complète */
    public static function render_page(): void { ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Google Tag Manager', 'wpstarterpack' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'wpsp_gtm_group' );
                    do_settings_sections( 'wpsp-gtm' );
                    submit_button( __( 'Enregistrer les modifications', 'wpstarterpack' ) );
                ?>
            </form>
        </div>
    <?php }

    /** Champ texte */
    public static function input_field(): void {
        printf(
            '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="GTM-XXXXXXX" />',
            esc_attr( Model::OPTION_KEY_ID ),
            esc_attr( get_option( Model::OPTION_KEY_ID, '' ) )
        );
    }

    /** Case à cocher */
    public static function checkbox_field(): void {
        $checked = (int) get_option( Model::OPTION_KEY_ACTIVE, 0 );
        printf(
            '<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s /> %3$s</label>',
            esc_attr( Model::OPTION_KEY_ACTIVE ),
            checked( 1, $checked, false ),
            esc_html__( 'Activer l’injection du script', 'wpstarterpack' )
        );
    }
}
