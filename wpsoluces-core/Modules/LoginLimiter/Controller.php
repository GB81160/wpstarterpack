<?php
namespace WPSolucesCore\Modules\LoginLimiter;

defined( 'ABSPATH' ) || exit;

/**
 * LoginLimiter – limite les connexions en fonction du login et de l’IP.
 * • Compte toute erreur (mauvais login OU mauvais mot de passe).
 * • Incrémente → vérifie → bloque dans le même hook « authenticate » (prio 100).
 */
class Controller {

    /** Secondes restantes pour l’erreur courante. */
    private static int $remain = 0;
    private static ?string $page_hook = null;

    public static function register(): void {

        /* ADMIN */
        add_action( 'admin_menu', [ self::class, 'add_settings_page' ] );
        add_action( 'admin_init', [ self::class, 'register_settings' ] );

        /* UN SEUL filtre « authenticate » à prio 100  ➜  fin du pipeline WP */
        add_filter( 'authenticate', [ self::class, 'check_and_count' ], 100, 3 );

        /* Connexion réussie ➜ reset */
        add_action( 'wp_login', static function ( string $login ) {
            Model::reset( $login );
        } );

        /* Remplace le message WP si c’est notre blocage  */
        add_filter( 'login_errors', [ self::class, 'replace_error' ] );
    }

    /* ---------------------------------------------------------------------
     * ADMIN
     * ------------------------------------------------------------------ */
    public static function add_settings_page(): void {
        self::$page_hook = add_options_page(
            __( 'Blocage connexion', 'wpsoluces' ),
            'Blocage connexion',
            'manage_options',
            'wpsc-ll',
            [ self::class, 'render_settings_page' ]
        );
    }

    public static function register_settings(): void {
        register_setting( 'wpsc_ll_group', Model::OPTION_MAX_ATTEMPTS, [ 'type' => 'integer', 'default' => 5 ] );
        register_setting( 'wpsc_ll_group', Model::OPTION_LOCK_MINUTES, [ 'type' => 'integer', 'default' => 30 ] );

        add_settings_section(
            'wpsc_ll_section',
            __( 'Limitation des tentatives', 'wpsoluces' ),
            null,
            'wpsc-ll'
        );

        add_settings_field(
            'wpsc_ll_max',
            __( 'Nombre maximal d\'essais', 'wpsoluces' ),
            [ self::class, 'field_max_attempts' ],
            'wpsc-ll',
            'wpsc_ll_section'
        );

        add_settings_field(
            'wpsc_ll_lock',
            __( 'Durée de blocage (min)', 'wpsoluces' ),
            [ self::class, 'field_lock_minutes' ],
            'wpsc-ll',
            'wpsc_ll_section'
        );
    }

    public static function render_settings_page(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Blocage connexion', 'wpsoluces' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'wpsc_ll_group' );
                do_settings_sections( 'wpsc-ll' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function field_max_attempts(): void {
        printf(
            '<input type="number" name="%1$s" value="%2$d" class="small-text" min="1" />',
            esc_attr( Model::OPTION_MAX_ATTEMPTS ),
            Model::max_attempts()
        );
    }

    public static function field_lock_minutes(): void {
        printf(
            '<input type="number" name="%1$s" value="%2$d" class="small-text" min="1" />',
            esc_attr( Model::OPTION_LOCK_MINUTES ),
            Model::lock_minutes()
        );
    }

    /**
     * Incrémente le compteur si échec, puis bloque si seuil atteint.
     *
     * @param \WP_User|\WP_Error|null $user
     * @param string                  $login
     * @param string                  $password
     *
     * @return \WP_User|\WP_Error|null
     */
    public static function check_and_count( $user, string $login, string $password ) {

        if ( empty( $login ) ) {
            return $user; // pas de login fourni
        }

        /* --- Si erreur WP précédente ➜ on compte l’échec --- */
        if ( $user instanceof \WP_Error ) {
            Model::increment( $login );
        }

        /* --- Vérifie blocage après incrément éventuel --- */
        if ( ! Model::is_locked( $login ) ) {
            return $user; // encore autorisé
        }

        /* Bloqué : calcule le temps restant et retourne WP_Error */
        self::$remain = Model::remaining( $login );
        $mins = (int) ceil( self::$remain / 60 );

        return new \WP_Error(
            'wpsc_login_lock',
            sprintf(
                /* translators: %d = minutes */
                __( 'Trop de tentatives ! Réessayez dans %d&nbsp;minute(s).', 'wpsoluces' ),
                $mins
            )
        );
    }

    /** Affiche notre message dans le formulaire de connexion */
    public static function replace_error( string $msg ): string {

        if ( self::$remain === 0 ) {
            return $msg; // pas notre cas
        }

        $mins = (int) ceil( self::$remain / 60 );

        return sprintf(
            __( 'Trop de tentatives ! Réessayez dans %d&nbsp;minute(s).', 'wpsoluces' ),
            $mins
        );
    }
}
