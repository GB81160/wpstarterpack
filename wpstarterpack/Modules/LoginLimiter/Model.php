<?php
namespace WPStarterPack\Modules\LoginLimiter;

defined( 'ABSPATH' ) || exit;

class Model {

    /** Nombre maximal d'essais. */
    public const MAX_ATTEMPTS = 5;

    /** Durée de blocage en minutes. */
    public const LOCK_MINUTES = 30;

    /** Préfixe commun à tous les transients du module. */
    public const TRANSIENT_PREFIX = 'wpsp_ll_';

    /** Nom du hook planifié pour la suppression automatique. */
    public const CLEANUP_HOOK = 'wpsp_ll_cleanup';

    private static function key( string $login ): string {
        return self::TRANSIENT_PREFIX . md5( strtolower( $login ) );
    }

    /**
     * Planifie la suppression du transient après expiration.
     */
    private static function schedule_cleanup( string $key ): void {
        if ( ! wp_next_scheduled( self::CLEANUP_HOOK, [ $key ] ) ) {
            wp_schedule_single_event(
                time() + self::lock_minutes() * MINUTE_IN_SECONDS,
                self::CLEANUP_HOOK,
                [ $key ]
            );
        }
    }

    /**
     * Supprime le transient (appelé via cron).
     */
    public static function cleanup( string $key ): void {
        delete_transient( $key );
    }

    /**
     * Supprime tous les transients du LoginLimiter.
     */
    public static function delete_all_transients(): void {
        global $wpdb;

        if ( ! ( $wpdb instanceof \wpdb ) ) {
            return;
        }

        $like  = $wpdb->esc_like( self::TRANSIENT_PREFIX );
        $names = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_{$like}%' OR option_name LIKE '_transient_timeout_{$like}%'"
        );

        foreach ( $names as $name ) {
            if ( str_starts_with( $name, '_transient_timeout_' ) ) {
                delete_option( $name );
            } elseif ( str_starts_with( $name, '_transient_' ) ) {
                $transient = substr( $name, 11 );
                delete_transient( $transient );
            }
        }

        if ( is_multisite() ) {
            $site_names = $wpdb->get_col(
                "SELECT meta_key FROM {$wpdb->sitemeta} WHERE meta_key LIKE '_site_transient_{$like}%' OR meta_key LIKE '_site_transient_timeout_{$like}%'"
            );

            foreach ( $site_names as $name ) {
                if ( str_starts_with( $name, '_site_transient_timeout_' ) ) {
                    delete_site_option( $name );
                } elseif ( str_starts_with( $name, '_site_transient_' ) ) {
                    $transient = substr( $name, 16 );
                    delete_site_transient( $transient );
                }
            }
        }
    }

    public static function get_attempts( string $login ): int {
        return (int) get_transient( self::key( $login ) );
    }

    public static function max_attempts(): int {
        return self::MAX_ATTEMPTS;
    }

    public static function lock_minutes(): int {
        return self::LOCK_MINUTES;
    }

    public static function increment( string $login ): void {
        $key      = self::key( $login );
        $previous = self::get_attempts( $login );
        $attempts = $previous + 1;

        if ( $previous < self::MAX_ATTEMPTS && $attempts >= self::MAX_ATTEMPTS ) {
            $expiration = self::lock_minutes() * MINUTE_IN_SECONDS;
            self::schedule_cleanup( $key );
        } elseif ( $previous >= self::MAX_ATTEMPTS ) {
            $expiration = max( 1, self::remaining( $login ) );
        } else {
            $expiration = self::lock_minutes() * MINUTE_IN_SECONDS;
        }

        set_transient( $key, $attempts, $expiration );
    }

    public static function reset( string $login ): void {
        delete_transient( self::key( $login ) );
    }

    public static function is_locked( string $login ): bool {
        return self::get_attempts( $login ) >= self::max_attempts();
    }

    public static function remaining( string $login ): int {
        $t = get_option( '_transient_timeout_' . self::key( $login ), 0 );
        return max( 0, $t - time() );
    }
}
