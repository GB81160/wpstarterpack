<<<<<<< codex/corriger-erreur-de-syntaxe-dans-view.php
<?php
namespace WPSolucesCore\Modules\LoginRedirect;

defined( 'ABSPATH' ) || exit;

/**
 * Vue : écran Réglages → URL de connexion
 */
class View {

	/**
	 * Page complète
	 */
    public static function render_page(): void { ?>
		<div class="wrap">
			<h1><?php esc_html_e( 'URL de connexion', 'wpsoluces' ); ?></h1>

			<p class="notice notice-info" style="padding:12px 15px;">
				<?php
                                printf(
                                        /* translators: %s = nouvelle URL de connexion */
                                        esc_html__(
                                                'Une fois la fonction activée, les pages %1$s et %2$s afficheront un code 404 pour les visiteurs. '
                                                . 'La nouvelle adresse de connexion sera : %3$s',
                                                'wpsoluces'
                                        ),
					'<code>/wp-login.php</code>',
					'<code>/wp-admin</code>',
					'<code>' . esc_url( home_url( '/connect' ) ) . '</code>'
				);
				?>
			</p>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpsc_lr_group' );
				do_settings_sections( 'wpsc-lr' );
				submit_button();
				?>
			</form>
		</div>
	<?php }

	/**
	 * Case à cocher Activer / Désactiver
	 */
    public static function checkbox_field(): void { ?>
		<label>
			<input type="checkbox"
			       name="<?php echo esc_attr( Model::OPTION_ACTIVE ); ?>"
			       value="1"
			       <?php checked( Model::is_active() ); ?> />
			<?php esc_html_e( 'Activer le déplacement de la connexion vers /connect', 'wpsoluces' ); ?>
		</label>
	<?php }
}
=======
 <?php
 namespace WPSolucesCore\Modules\LoginRedirect;
 
 defined( 'ABSPATH' ) || exit;
 
 /**
  * Vue : écran Réglages → URL de connexion
  */
 class View {
 
 	/**
 	 * Page complète
 	 */
 	public static function render_page(): void { ?>
 		<div class="wrap">
 			<h1><?php esc_html_e( 'URL de connexion', 'wpsoluces' ); ?></h1>
 
 			<p class="notice notice-info" style="padding:12px 15px;">
 				<?php
-				printf(
-					/* translators: %s = nouvelle URL de connexion */
-					esc_html__(
-						'Une fois la fonction activée, les pages %1$s et %2$s afficheront un code 404 pour les visiteurs. 
-						La nouvelle adresse de connexion sera : %3$s',
-						'wpsoluces'
-					),
-					'<code>/wp-login.php</code>',
-					'<code>/wp-admin</code>',
-					'<code>' . esc_url( home_url( '/connect' ) ) . '</code>'
-				);
-				?>
-			</p>
+                                printf(
+                                        esc_html__(
+                                                'Une fois la fonction activée, les pages %1$s et %2$s afficheront un code 404 pour les visiteurs.',
+                                                'wpsoluces'
+                                        ),
+                                        '<code>/wp-login.php</code>',
+                                        '<code>/wp-admin</code>'
+                                );
+                        ?>
+                        </p>
+
+                        <p>
+                                <?php
+                                printf(
+                                        esc_html__( 'Adresse de connexion actuelle : %s', 'wpsoluces' ),
+                                        '<code>' . esc_url( home_url( '/' . Model::slug() ) ) . '</code>'
+                                );
+                                ?>
+                        </p>
 
 			<form method="post" action="options.php">
 				<?php
 				settings_fields( 'wpsc_lr_group' );
 				do_settings_sections( 'wpsc-lr' );
 				submit_button();
 				?>
 			</form>
 		</div>
 	<?php }
 
 	/**
 	 * Case à cocher Activer / Désactiver
 	 */
-	public static function checkbox_field(): void { ?>
-		<label>
-			<input type="checkbox"
-			       name="<?php echo esc_attr( Model::OPTION_ACTIVE ); ?>"
-			       value="1"
-			       <?php checked( Model::is_active() ); ?> />
-			<?php esc_html_e( 'Activer le déplacement de la connexion vers /connect', 'wpsoluces' ); ?>
-		</label>
-	<?php }
+        public static function checkbox_field(): void { ?>
+                <label>
+                        <input type="checkbox"
+                               name="<?php echo esc_attr( Model::OPTION_ACTIVE ); ?>"
+                               value="1"
+                               <?php checked( Model::is_active() ); ?> />
+                        <?php esc_html_e( 'Activer le déplacement de la connexion', 'wpsoluces' ); ?>
+                </label>
+        <?php }
+
+        /** Champ slug */
+        public static function slug_field(): void {
+                printf(
+                        '<span class="wpsc-url-prefix">%s</span><input type="text" name="%s" value="%s" class="regular-text" placeholder="connect" />',
+                        esc_url( home_url( '/' ) ),
+                        esc_attr( Model::OPTION_SLUG ),
+                        esc_attr( Model::slug() )
+                );
+        }
+
 }
>>>>>>> dev
