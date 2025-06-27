<?php
namespace WPStarterPack\Modules\LoginLimiter;

defined( 'ABSPATH' ) || exit;

/**
 * LoginLimiter – limite les connexions en fonction du login.
 * • Compte toute erreur (mauvais login OU mauvais mot de passe).
 * • Incrémente → vérifie → bloque dans le même hook « authenticate » (prio 100).
 */
class Controller {

    /** Secondes restantes pour l’erreur courante. */
    private static int $remain = 0;

    public static function register(): void {

        /* UN SEUL filtre « authenticate » à prio 100  ➜  fin du pipeline WP */
        add_filter( 'authenticate', [ self::class, 'check_and_count' ], 100, 3 );

        /* Connexion réussie ➜ reset */
        add_action( 'wp_login', static function ( string $login ) {
            Model::reset( $login );
        } );

        /* Remplace le message WP si c’est notre blocage  */
        add_filter( 'login_errors', [ self::class, 'replace_error' ] );
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
            'wpsp_login_lock',
            sprintf(
                /* translators: %d = minutes */
                __( 'Trop de tentatives ! Réessayez dans %d&nbsp;minute(s).', 'wpstarterpack' ),
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
            __( 'Trop de tentatives ! Réessayez dans %d&nbsp;minute(s).', 'wpstarterpack' ),
            $mins
        );
    }
}
