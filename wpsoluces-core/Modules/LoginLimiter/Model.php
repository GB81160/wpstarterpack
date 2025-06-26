<?php
namespace WPSolucesCore\Modules\LoginLimiter;

defined( 'ABSPATH' ) || exit;

class Model {

    public const OPTION_MAX_ATTEMPTS = 'wpsc_ll_max';
    public const OPTION_LOCK_MINUTES = 'wpsc_ll_lock';
    private const PREFIX = 'wpsc_ll_';

    private static function key( string $login ): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return self::PREFIX . md5( strtolower( $login ) . '|' . $ip );
    }

    public static function get_attempts( string $login ): int {
        return (int) get_transient( self::key( $login ) );
    }

    public static function max_attempts(): int {
        return (int) get_option( self::OPTION_MAX_ATTEMPTS, 5 );
    }

    public static function lock_minutes(): int {
        return (int) get_option( self::OPTION_LOCK_MINUTES, 30 );
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
