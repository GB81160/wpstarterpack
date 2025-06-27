<?php
namespace WPStarterPack\Modules\DuplicatePost;

defined( 'ABSPATH' ) || exit;

/**
 * Module DuplicatePost
 * – Ajoute un lien « Dupliquer » sur les articles / pages
 * – Clone titre, contenu, métadonnées, taxonomies
 * – Enregistre la copie en brouillon puis revient à la liste (pas à l’éditeur)
 * – Pas de page de réglages
 */
class Controller {

	/* ---------------------------------------------------------------------
	 * Hooks
	 * ------------------------------------------------------------------ */
	public static function register(): void {

		// Lien « Dupliquer » dans les listes WP
		add_filter( 'post_row_actions', [ self::class, 'add_link' ], 10, 2 );
		add_filter( 'page_row_actions', [ self::class, 'add_link' ], 10, 2 );

		// Action admin
		add_action( 'admin_action_wpsp_duplicate_post', [ self::class, 'handle_duplicate' ] );

		// Message de confirmation
		add_action( 'admin_notices', [ self::class, 'admin_notice' ] );
	}

	/* ---------------------------------------------------------------------
	 * Affiche le lien « Dupliquer »
	 * ------------------------------------------------------------------ */
	public static function add_link( array $actions, \WP_Post $post ): array {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg(
				[
					'action' => 'wpsp_duplicate_post',
					'post'   => $post->ID,
				],
				admin_url( 'admin.php' )
			),
			'wpsp_duplicate_post_' . $post->ID
		);

		$actions['wps_dup'] = '<a href="' . esc_url( $url ) . '">' .
			esc_html__( 'Dupliquer', 'wpstarterpack' ) . '</a>';

		return $actions;
	}

	/* ---------------------------------------------------------------------
	 * Duplique le contenu
	 * ------------------------------------------------------------------ */
	public static function handle_duplicate(): void {

		$post_id = intval( $_GET['post'] ?? 0 );

		if (
			! $post_id ||
			! current_user_can( 'edit_posts' ) ||
			! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpsp_duplicate_post_' . $post_id )
		) {
			wp_die( __( 'Action non autorisée.', 'wpstarterpack' ) );
		}

		$orig = get_post( $post_id );
		if ( ! $orig ) {
			wp_redirect( admin_url( 'edit.php' ) );
			exit;
		}

                /* 1. Nouveau brouillon */
                $suffix = apply_filters( 'wpsp_duplicate_title_suffix', ' (copie)' );
                $new_id = wp_insert_post( [
                        'post_title'   => $orig->post_title . $suffix,
                        'post_content' => $orig->post_content,
                        'post_excerpt' => $orig->post_excerpt,
                        'post_status'  => 'draft',
                        'post_type'    => $orig->post_type,
                        'post_author'  => get_current_user_id(),
			'post_parent'  => $orig->post_parent,
			'menu_order'   => $orig->menu_order,
			'comment_status' => $orig->comment_status,
			'ping_status'    => $orig->ping_status,
		] );

		if ( is_wp_error( $new_id ) ) {
			wp_die( $new_id->get_error_message() );
		}

		/* 2. Taxonomies */
		foreach ( get_object_taxonomies( $orig->post_type ) as $taxonomy ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
			wp_set_object_terms( $new_id, $terms, $taxonomy );
		}

                /* 3. Métadonnées */
                foreach ( get_post_meta( $post_id ) as $key => $values ) {
                        foreach ( $values as $value ) {
                                add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
                        }
                }

                /* Image mise en avant */
                if ( $thumb = get_post_thumbnail_id( $post_id ) ) {
                        set_post_thumbnail( $new_id, $thumb );
                }

		/* 4. Retour à la liste avec paramètre de confirmation */
		$list_url = ( $orig->post_type === 'post' )
			? admin_url( 'edit.php' )
			: admin_url( 'edit.php?post_type=' . $orig->post_type );

		wp_safe_redirect( add_query_arg( [ 'wpsp_duplicated' => 1 ], $list_url ) );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Affiche un message « Copie créée »
	 * ------------------------------------------------------------------ */
	public static function admin_notice(): void {

		if ( isset( $_GET['wpsp_duplicated'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' .
			     esc_html__( 'Copie créée avec succès. Le brouillon est prêt.', 'wpstarterpack' ) .
			     '</p></div>';
		}
	}
}
