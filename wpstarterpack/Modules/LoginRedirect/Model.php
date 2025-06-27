<?php
namespace WPStarterPack\Modules\LoginRedirect;

defined( 'ABSPATH' ) || exit;

class Model {
       public const DEFAULT_SLUG = 'connect';
        public const OPTION_ACTIVE  = 'wpsp_lr_active';
        public const OPTION_SLUG    = 'wpsp_lr_slug';
        public const OPTION_FLUSHED = 'wpsp_lr_rewrite_flushed';

        public static function is_active(): bool {
                return (bool) get_option( self::OPTION_ACTIVE, 0 );
        }

       public static function slug(): string {
               $slug = trim( (string) get_option( self::OPTION_SLUG, self::DEFAULT_SLUG ) );
               return $slug !== '' ? $slug : self::DEFAULT_SLUG;
       }

}
