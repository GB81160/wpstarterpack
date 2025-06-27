<?php
namespace WPStarterPack\Modules\LoginRedirect;

defined( 'ABSPATH' ) || exit;

/**
 * Module LoginRedirect
 * — Déplace la page de connexion vers un slug personnalisé
 * — Bloque /wp-login.php et /wp-admin* pour les visiteurs
 * — Autorise le POST vers ce slug et la redirection transitoire
 * — Redirige l’utilisateur connecté vers /wp-admin/
 * — Flush + logout lors de l’activation/désactivation
 */
class Controller {

	private static ?string $page_hook = null;

	/* --------------------------------------------------------------------- */
	/* HOOKS                                                                 */
	/* --------------------------------------------------------------------- */
	public static function register(): void {

		/* ADMIN */
                add_action( 'admin_menu',            [ self::class, 'add_settings_page' ] );
                add_action( 'admin_init',            [ self::class, 'register_settings' ] );
                add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_admin_css' ] );
                add_action(
                        'update_option_' . Model::OPTION_ACTIVE,
                        [ self::class, 'flush_and_logout' ],
                        20, 2
                );
                add_action(
                        'add_option_' . Model::OPTION_ACTIVE,
                        [ self::class, 'flush_and_logout' ],
                        20, 2
                );
                add_action(
                        'update_option_' . Model::OPTION_SLUG,
                        [ self::class, 'flush_and_logout' ],
                        20, 2
                );
                add_action(
                        'add_option_' . Model::OPTION_SLUG,
                        [ self::class, 'flush_and_logout' ],
                        20, 2
                );

		/* FRONT / GLOBAL */
		add_action( 'init',          [ self::class, 'add_rewrite' ] );
		add_action( 'init',          [ self::class, 'block_default_endpoints' ], 0 );
		add_filter( 'login_url',     [ self::class, 'filter_login_url' ], 10, 3 );
		add_filter( 'site_url',      [ self::class, 'filter_login_post_url' ], 10, 4 );
		add_filter( 'login_redirect',[ self::class, 'force_admin_redirect' ], 10, 3 );
		add_action( 'template_redirect', [ self::class, 'serve_connect' ] );
	}

	/* ---------------------------------------------------------------------
	 * ADMIN
	 * ------------------------------------------------------------------ */
	public static function add_settings_page(): void {
		self::$page_hook = add_options_page(
			__( 'URL de connexion', 'wpstarterpack' ),
			'URL de connexion',
			'manage_options',
			'wpsp-lr',
			[ self::class, 'render_settings_page' ]
		);
	}

        public static function register_settings(): void {
                register_setting(
                        'wpsp_lr_group',
                        Model::OPTION_ACTIVE,
                        [
                                'type'              => 'boolean',
                                'sanitize_callback' => fn( $v ) => $v ? 1 : 0,
                                'default'           => 0,
                        ]
                );

                register_setting(
                        'wpsp_lr_group',
                        Model::OPTION_SLUG,
                       [
                                'type'              => 'string',
                                'sanitize_callback' => 'sanitize_title',
                                'default'           => Model::DEFAULT_SLUG,
                        ]
               );


               add_settings_section(
                       'wpsp_lr_section',
                       __( 'Déplacement de la page de connexion', 'wpstarterpack' ),
                       '__return_null',
                       'wpsp-lr'
               );

                add_settings_field(
                        'wpsp_lr_active',
                        __( 'Activer', 'wpstarterpack' ),
                        [ self::class, 'render_checkbox_field' ],
                        'wpsp-lr',
                        'wpsp_lr_section'
                );

                add_settings_field(
                        'wpsp_lr_slug',
                        __( 'Slug de connexion', 'wpstarterpack' ),
                        [ View::class, 'slug_field' ],
                        'wpsp-lr',
                        'wpsp_lr_section'
                );

        }

	public static function render_settings_page(): void { View::render_page(); }
	public static function render_checkbox_field(): void { View::checkbox_field(); }

	public static function enqueue_admin_css( string $hook ): void {
		if ( $hook === self::$page_hook ) {
			wp_add_inline_style( 'wp-admin', '.notice-success{border-left-color:#00a32a}' );
		}
	}

        /* ---------------------------------------------------------------------
         * FRONT / GLOBAL
         * ------------------------------------------------------------------ */
       /**
        * Renvoie le chemin de requête courant (/sans domaine).
        */
       private static function request_path(): string {
               return trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) ?? '', '/' );
       }

       public static function add_rewrite(): void {
               if ( ! Model::is_active() ) { return; }

                $slug = Model::slug();
                add_rewrite_rule( '^' . preg_quote( $slug, '#' ) . '/?$', 'wp-login.php', 'top' );

                if ( ! get_option( Model::OPTION_FLUSHED ) ) {
                        flush_rewrite_rules( false );
                        update_option( Model::OPTION_FLUSHED, 1 );
                }
	}

        public static function block_default_endpoints(): void {

                if ( ! Model::is_active() || is_user_logged_in() ) {
                        return;
                }


               $path = self::request_path();

		// /wp-login.php
		if ( preg_match( '#^wp-login\\.php$#i', $path ) ) {

			// Autorise la requête transitoire post-authentification
			if ( isset( $_GET['redirect_to'] ) ) {
				return;
			}

			self::send_404();
		}

		// /wp-admin* (hors admin-ajax & async-upload)
		if ( preg_match( '#^wp-admin(?:/|$)#i', $path )
		     && ! preg_match( '#^wp-admin/(?:admin-ajax\\.php|async-upload\\.php)$#i', $path ) ) {
			self::send_404();
		}
	}

	public static function filter_login_url( string $login, string $redirect, bool $force_reauth ): string {
                return Model::is_active() ? home_url( '/' . Model::slug() ) : $login;
	}

	/**
	 * Force l'action du formulaire de connexion à /connect.
	 *
	 * @param mixed $url
	 * @param mixed $path
	 * @param mixed $scheme (peut être null)
	 * @param mixed $context (peut être null)
	 */
	public static function filter_login_post_url( $url, $path, $scheme = null, $context = null ) {

		if ( ! Model::is_active() ) {
			return $url;
		}

		$is_login_post_ctx = $context === 'login_post' || $context === null;

                if ( $is_login_post_ctx && $path === 'wp-login.php' ) {
                        return home_url( '/' . Model::slug(), $scheme ?: 'login' );
		}

		return $url;
	}

	public static function force_admin_redirect( string $redirect_to, string $requested, $user ): string {

		if ( ! $user instanceof \WP_User ) {
			return $redirect_to;
		}

		if (
			empty( $redirect_to )
			|| strpos( $redirect_to, 'wp-login.php' ) !== false
                        || strpos( $redirect_to, '/' . Model::slug() ) !== false
		) {
			return admin_url();
		}

		return $redirect_to;
	}

	public static function serve_connect(): void {

		if ( ! Model::is_active() ) {
			return;
		}

               $req     = self::request_path();
                $connect = trim( wp_make_link_relative( home_url( '/' . Model::slug() ) ), '/' );

		if ( $req === $connect ) {
			global $error, $user_login;
			$error = $user_login = '';

			require_once ABSPATH . 'wp-login.php';
			exit;
		}
	}

	/* ---------------------------------------------------------------------
	 * Flush + logout lors activation/désactivation
	 * ------------------------------------------------------------------ */
       public static function flush_and_logout( $old, $new ): void {
               if ( $old === $new ) {
                       return;
               }

               flush_rewrite_rules( false );
               delete_option( Model::OPTION_FLUSHED );

               wp_logout();

               $target = Model::is_active()
                       ? home_url( '/' . Model::slug() )
                       : wp_login_url();

               wp_safe_redirect( $target );
               exit;
       }

	/* ---------------------------------------------------------------------
	 * Helper 404
	 * ------------------------------------------------------------------ */
	private static function send_404(): void {
		status_header( 404 );
		nocache_headers();

		if ( $template = get_404_template() ) {
			include $template;
		} else {
			echo '<h1>404 – Not Found</h1>';
		}
		exit;
	}
}
