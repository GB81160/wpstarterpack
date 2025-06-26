<?php
namespace WPSolucesCore\Modules\TagManager;

defined( 'ABSPATH' ) || exit;

/**
 * Accès aux réglages + cache transients (12 h)
 */
class Model {

	/* Options WP */
	public const OPTION_KEY_ID     = 'wpsc_gtm_id';
	public const OPTION_KEY_ACTIVE = 'wpsc_gtm_active';

        /* Transient */
        public const TRANSIENT_KEY = 'wpsc_gtm_settings';
        private const TTL          = 12 * HOUR_IN_SECONDS;

	/**
	 * Retourne les réglages (cachés 12 h).
	 *
	 * @return array{id:string,active:bool}
	 */
	public static function get_settings(): array {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( $cached !== false ) {
			return $cached;
		}

                $settings = [
                        'id'     => get_option( self::OPTION_KEY_ID, '' ),
                        'active' => (bool) get_option( self::OPTION_KEY_ACTIVE, 0 ),
                ];

                /**
                 * Filter to override settings programmatically (e.g. multisite).
                 *
                 * @param array{id:string,active:bool} $settings
                 */
                $settings = apply_filters( 'wpsc_gtm_settings', $settings );

		set_transient( self::TRANSIENT_KEY, $settings, self::TTL );
		return $settings;
	}

	/** Indique si l’injection est active. */
	public static function is_active(): bool {
		return self::get_settings()['active'];
	}

	/** Vide le cache (appelé quand une option change). */
	public static function delete_cache(): void {
		delete_transient( self::TRANSIENT_KEY );
	}
}
