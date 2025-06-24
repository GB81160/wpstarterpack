<?php
namespace WPSolucesCore\Modules\TagManager;

defined( 'ABSPATH' ) || exit;

/**
 * Controller – Google Tag Manager
 */
class Controller {

    private static ?string $page_hook = null;

    /* ---------------------------------------------------------------------
     * HOOKS
     * ------------------------------------------------------------------ */
    public static function register(): void {

        /* ADMIN */
        add_action( 'admin_menu',            [ self::class, 'add_settings_page' ] );
        add_action( 'admin_init',            [ self::class, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_validator_js' ] );

        add_action( 'update_option_' . Model::OPTION_KEY_ID,     [ Model::class, 'delete_cache' ], 10, 0 );
        add_action( 'update_option_' . Model::OPTION_KEY_ACTIVE, [ Model::class, 'delete_cache' ], 10, 0 );

        // Purge + désactivation si l’ID est vidé manuellement
        add_action(
            'update_option_' . Model::OPTION_KEY_ID,
            static function ( $old, $new ) {
                if ( trim( $new ) === '' ) {
                    delete_option( Model::OPTION_KEY_ID );
                    update_option( Model::OPTION_KEY_ACTIVE, 0 );
                    Model::delete_cache();
                }
            },
            10,
            2
        );

        /* FRONT */
        add_action( 'wp_head',      [ self::class, 'print_head_snippet' ], 1 );
        add_action( 'wp_body_open', [ self::class, 'print_body_noscript' ] );
    }

    /* ---------------------------------------------------------------------
     * ADMIN : page Réglages
     * ------------------------------------------------------------------ */
    public static function add_settings_page(): void {
        self::$page_hook = add_options_page(
            'Google Tag Manager',
            'Google Tag Manager',
            'manage_options',
            'wpsc-gtm',
            [ self::class, 'render_settings_page' ]
        );
    }

    public static function register_settings(): void {

        register_setting(
            'wpsc_gtm_group',
            Model::OPTION_KEY_ID,
            [
                'type'              => 'string',
                'sanitize_callback' => [ self::class, 'sanitize_gtm_id' ],
                'default'           => '',
            ]
        );

        register_setting(
            'wpsc_gtm_group',
            Model::OPTION_KEY_ACTIVE,
            [
                'type'              => 'boolean',
                'sanitize_callback' => fn ( $v ) => $v ? 1 : 0,
                'default'           => 0,
            ]
        );

        add_settings_section(
            'wpsc_gtm_section',
            'Balise Google Tag Manager',
            fn() => printf(
                '<p>%s</p>',
                esc_html__( 'Indiquez l’ID (GTM-XXXXXXX) puis cochez pour activer.', 'wpsoluces' )
            ),
            'wpsc-gtm'
        );

        add_settings_field(
            'wpsc_gtm_id',
            'ID du conteneur',
            [ View::class, 'input_field' ],
            'wpsc-gtm',
            'wpsc_gtm_section'
        );

        add_settings_field(
            'wpsc_gtm_active',
            'Activer l’injection',
            [ View::class, 'checkbox_field' ],
            'wpsc-gtm',
            'wpsc_gtm_section'
        );
    }

    /** Validation serveur stricte */
    public static function sanitize_gtm_id( string $raw ): string {

        $id     = strtoupper( trim( $raw ) );
        $active = isset( $_POST[ Model::OPTION_KEY_ACTIVE ] ) ? (int) $_POST[ Model::OPTION_KEY_ACTIVE ] : 0;

        if ( $active && ! preg_match( '/^GTM-[A-Z0-9]{7}$/', $id ) ) {
            add_settings_error(
                Model::OPTION_KEY_ID,
                'gtm_invalid',
                __( 'ID GTM invalide : format requis « GTM-XXXXXXX ».', 'wpsoluces' ),
                'error'
            );
            return ''; // empêche l’enregistrement
        }

        return $id;
    }

    public static function render_settings_page(): void { View::render_page(); }

    public static function enqueue_validator_js( string $hook ): void {
        if ( $hook === self::$page_hook ) {
            wp_enqueue_script(
                'wpsc-gtm-validator',
                WPSC_URL . '/assets/TagManager/gtm-settings.js',
                [],
                '1.1.0',
                true
            );
        }
    }

    /* ---------------------------------------------------------------------
     * FRONT helpers
     * ------------------------------------------------------------------ */
    private static function settings(): array {
        $s = Model::get_settings();
        return ( $s['active'] && ! empty( $s['id'] ) && ! is_admin() ) ? $s : [];
    }

    /* ---------------------------------------------------------------------
     * FRONT · Head + Body
     * ------------------------------------------------------------------ */
    public static function print_head_snippet(): void {
        if ( ! $s = self::settings() ) { return; }

        printf(
            "\n<!-- Google Tag Manager -->\n"
          . "<script>\n"
          . "    document.addEventListener('DOMContentLoaded', function () {\n"
          . "        (function (w, d, s, l, i) {\n"
          . "            w[l] = w[l] || [];\n"
          . "            w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });\n"
          . "            var f = d.getElementsByTagName(s)[0];\n"
          . "            var j = d.createElement(s);\n"
          . "            var dl = l !== 'dataLayer' ? '&l=' + l : '';\n"
          . "            j.async = true;\n"
          . "            j.defer = true;\n"
          . "            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;\n"
          . "            f.parentNode.insertBefore(j, f);\n"
          . "        })(window, document, 'script', 'dataLayer', '%s');\n"
          . "    });\n"
          . "</script>\n"
          . "<!-- End Google Tag Manager -->\n",
            esc_js( $s['id'] )
        );
    }

    public static function print_body_noscript(): void {
        if ( ! $s = self::settings() ) { return; }

        printf(
            "\n<!-- Google Tag Manager (noscript) -->\n"
          . "<noscript>\n"
          . "    <iframe src=\"https://www.googletagmanager.com/ns.html?id=%s\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe>\n"
          . "</noscript>\n"
          . "<!-- End Google Tag Manager (noscript) -->\n",
            esc_attr( $s['id'] )
        );
    }
}
