<?php
namespace WPSolucesCore\Modules\LoginLimiter;

defined( 'ABSPATH' ) || exit;

class Model {

    public const MAX_ATTEMPTS = 5;
    public const LOCK_MINUTES = 30;
    private const PREFIX      = 'wpsc_ll_';

    private static function key( string $login ): string {
        return self::PREFIX . strtolower( $login );
    }

    public static function get_attempts( string $login ): int {
        return (int) get_transient( self::key( $login ) );
    }

    public static function increment( string $login ): void {
        set_transient(
            self::key( $login ),
            self::get_attempts( $login ) + 1,
            self::LOCK_MINUTES * MINUTE_IN_SECONDS
        );
    }

    public static function reset( string $login ): void {
        delete_transient( self::key( $login ) );
    }

    public static function is_locked( string $login ): bool {
        return self::get_attempts( $login ) >= self::MAX_ATTEMPTS;
    }

    public static function remaining( string $login ): int {
        $t = get_option( '_transient_timeout_' . self::key( $login ), 0 );
        return max( 0, $t - time() );
    }
}
