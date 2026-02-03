<?php
namespace WPStarterPack\Modules\LoginLimiter;

defined( 'ABSPATH' ) || exit;

class Model {

    /** Nombre maximal d'essais. */
    private const MAX_ATTEMPTS = 5;

    /** Durée de blocage en minutes. */
    private const LOCK_MINUTES = 30;

    private const PREFIX = 'wpsp_ll_';

    public static function normalize_login( string $login ): string {
        $normalized = sanitize_user( $login, true );
        return $normalized !== '' ? $normalized : strtolower( $login );
    }

    private static function key( string $login ): string {
        return self::PREFIX . md5( strtolower( self::normalize_login( $login ) ) );
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
        set_transient(
            self::key( $login ),
            self::get_attempts( $login ) + 1,
            self::lock_minutes() * MINUTE_IN_SECONDS
        );
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
