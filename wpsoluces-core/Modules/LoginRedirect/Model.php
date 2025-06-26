<?php
namespace WPSolucesCore\Modules\LoginRedirect;

defined( 'ABSPATH' ) || exit;

class Model {
       public const DEFAULT_SLUG = 'connect';
        public const OPTION_ACTIVE  = 'wpsc_lr_active';
        public const OPTION_SLUG    = 'wpsc_lr_slug';
        public const OPTION_FLUSHED = 'wpsc_lr_rewrite_flushed';

        public static function is_active(): bool {
                return (bool) get_option( self::OPTION_ACTIVE, 0 );
        }

       public static function slug(): string {
               $slug = trim( (string) get_option( self::OPTION_SLUG, self::DEFAULT_SLUG ) );
               return $slug !== '' ? $slug : self::DEFAULT_SLUG;
       }

}
