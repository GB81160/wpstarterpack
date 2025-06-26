<?php
namespace WPSolucesCore\Modules\LoginRedirect;

defined( 'ABSPATH' ) || exit;

class Model {
        public const OPTION_ACTIVE  = 'wpsc_lr_active';
        public const OPTION_FLUSHED = 'wpsc_lr_rewrite_flushed';

        public static function is_active(): bool {
                return (bool) get_option( self::OPTION_ACTIVE, 0 );
        }
}
