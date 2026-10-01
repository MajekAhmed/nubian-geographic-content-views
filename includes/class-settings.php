<?php
/**
 * Plugin settings screen (minimal).
 *
 * One screen, one purpose: tell the plugin which page is the Articles page so
 * the archive view can also render when that page is the WordPress Posts page
 * (Settings → Reading). Page-template routing is unaffected — assigning the
 * "NGCV — Articles Archive" page template to a static page remains the primary
 * route; this setting only enables the is_home() fallback.
 *
 * Registers no content, no taxonomy, no REST route, and no cron. It writes a
 * single integer option and nothing else.
 *
 * @package nubian-geographic-content-views
 */

defined( 'ABSPATH' ) || exit;

final class NGCV_Settings {

	/**
	 * Option name holding the selected Articles page ID (0 = none).
	 *
	 * @var string
	 */
	const OPTION_ARTICLES_PAGE = 'ngcv_articles_page_id';

	/**
	 * Option name holding the archive hero image attachment ID (0 = none).
	 *
	 * @var string
	 */
	const OPTION_HERO_IMAGE = 'ngcv_archive_hero_image_id';

	/**
	 * Settings-screen slug.
	 *
	 * @var string
	 */
	const SCREEN_SLUG = 'ngcv-content-views';

	/**
	 * Settings option group.
	 *
	 * @var string
	 */
	const OPTION_GROUP = 'ngcv_content_views';

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register the settings screen under Settings.
	 *
	 * @return void
	 */
	public static function register_menu() {
		add_options_page(
			__( 'NGCV Content Views', 'nubian-geographic-content-views' ),
			__( 'NGCV Content Views', 'nubian-geographic-content-views' ),
			'manage_options',
			self::SCREEN_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the option and the field (WordPress Settings API).
	 *
	 * @return void
	 */
	public static function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_ARTICLES_PAGE,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize_page_id' ),
				'default'           => 0,
			)
		);

		register_setting(
			self::OPTION_GROUP,
			self::OPTION_HERO_IMAGE,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize_image_id' ),
				'default'           => 0,
			)
		);

		add_settings_section(
			'ngcv_section_routing',
			'',
			'__return_false',
			self::SCREEN_SLUG
		);

		add_settings_field(
			'ngcv_field_articles_page',
			__( 'Articles page', 'nubian-geographic-content-views' ),
			array( __CLASS__, 'render_field_articles_page' ),
			self::SCREEN_SLUG,
			'ngcv_section_routing',
			array( 'label_for' => self::OPTION_ARTICLES_PAGE )
		);

		add_settings_field(
			'ngcv_field_hero_image',
			__( 'Archive hero image', 'nubian-geographic-content-views' ),
			array( __CLASS__, 'render_field_hero_image' ),
			self::SCREEN_SLUG,
			'ngcv_section_routing'
		);
	}

	/**
	 * Sanitize the stored value: a positive integer pointing at an existing,
	 * published page — or 0. Never trusts raw input.
	 *
	 * @param mixed $value Raw option value.
	 * @return int
	 */
	public static function sanitize_page_id( $value ) {
		$page_id = absint( $value );
		if ( ! $page_id ) {
			return 0;
		}

		$post = get_post( $page_id );
		if ( ! $post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
			return 0;
		}

		return $page_id;
	}

	/**
	 * Validated Articles page ID (render-time re-check; 0 when unset or the
	 * page no longer exists as a published page).
	 *
	 * @return int
	 */
	public static function articles_page_id() {
		static $validated = null;
		if ( null !== $validated ) {
			return $validated;
		}

		$page_id   = absint( get_option( self::OPTION_ARTICLES_PAGE, 0 ) );
		$validated = 0;
		if ( $page_id ) {
			$post = get_post( $page_id );
			if ( $post && 'page' === $post->post_type && in_array( $post->post_status, array( 'publish', 'private' ), true ) ) {
				$validated = $page_id;
			}
		}

		/**
		 * Filter the validated Articles page ID (0 = fallback disabled).
		 *
		 * @param int $validated Page ID.
		 */
		return (int) apply_filters( 'ngcv_articles_page_setting', $validated );
	}

	/**
	 * Sanitize the hero image: a positive integer pointing at an existing
	 * image attachment — or 0. Never trusts raw input.
	 *
	 * @param mixed $value Raw option value.
	 * @return int
	 */
	public static function sanitize_image_id( $value ) {
		$image_id = absint( $value );
		if ( ! $image_id ) {
			return 0;
		}

		return wp_attachment_is_image( $image_id ) ? $image_id : 0;
	}

	/**
	 * Validated hero image attachment ID (render-time re-check; 0 when unset
	 * or the attachment no longer is a valid image).
	 *
	 * @return int
	 */
	public static function hero_image_id() {
		static $validated = null;
		if ( null !== $validated ) {
			return $validated;
		}

		$image_id  = absint( get_option( self::OPTION_HERO_IMAGE, 0 ) );
		$validated = $image_id && wp_attachment_is_image( $image_id ) ? $image_id : 0;

		/**
		 * Filter the validated archive hero image attachment ID (0 = unset).
		 *
		 * @param int $validated Attachment ID.
		 */
		return (int) apply_filters( 'ngcv_archive_hero_image_setting', $validated );
	}

	/**
	 * Media Library scripts: only on the NGCV settings screen.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'settings_page_' . self::SCREEN_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'ngcv-admin-settings',
			NGCV_PLUGIN_URL . 'assets/js/admin-settings.js',
			array( 'media-editor' ),
			NGCV_VERSION,
			true
		);
	}

	/**
	 * Render the settings screen.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( __( 'NGCV Content Views', 'nubian-geographic-content-views' ) ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::SCREEN_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the Articles page dropdown.
	 *
	 * The dropdown selects the NGCV configured archive page. It is explicit
	 * and predictable: the official WordPress Posts page is always a valid
	 * NGCV archive target on its own (no template required); a static page
	 * carrying the NGCV page template is the primary static route. The three
	 * concepts (page_for_posts, configured archive page, templated static
	 * page) may converge on the same page but are never confused.
	 *
	 * @return void
	 */
	public static function render_field_articles_page() {
		$selected = absint( get_option( self::OPTION_ARTICLES_PAGE, 0 ) );
		?>
		<?php
		wp_dropdown_pages(
			array(
				'post_type'        => 'page',
				'post_status'      => array( 'publish', 'private' ),
				'selected'         => $selected,
				'name'             => self::OPTION_ARTICLES_PAGE,
				'id'               => self::OPTION_ARTICLES_PAGE,
				'show_option_none' => __( '— Select —', 'nubian-geographic-content-views' ),
				'option_none_value' => '0',
				'echo'             => 1,
			)
		);
		?>
		<p class="description">
			<?php echo esc_html( __( 'Select the NGCV archive page. The official WordPress Posts page (Settings → Reading) always uses the NGCV archive design on its own. Use this setting to choose which page supplies the archive title, introduction and hero when the Posts page is shown — including its Polylang translation in the current language. Static pages should use the "NGCV — Articles Archive" page template instead.', 'nubian-geographic-content-views' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Render the Archive hero image field (media picker).
	 *
	 * @return void
	 */
	public static function render_field_hero_image() {
		$hero_id = absint( get_option( self::OPTION_HERO_IMAGE, 0 ) );
		$hero_id = $hero_id && wp_attachment_is_image( $hero_id ) ? $hero_id : 0;
		$preview = $hero_id ? (string) wp_get_attachment_image_url( $hero_id, 'medium' ) : '';
		?>
		<input
			type="hidden"
			id="<?php echo esc_attr( self::OPTION_HERO_IMAGE ); ?>"
			name="<?php echo esc_attr( self::OPTION_HERO_IMAGE ); ?>"
			value="<?php echo esc_attr( $hero_id ); ?>"
		>
		<div id="ngcv-hero-picker" class="ngcv-hero-picker">
			<div id="ngcv-hero-preview" class="ngcv-hero-preview" data-alt="<?php echo esc_attr( __( 'Archive hero preview', 'nubian-geographic-content-views' ) ); ?>">
				<?php if ( $hero_id && $preview ) : ?>
					<img src="<?php echo esc_url( $preview ); ?>" alt="<?php echo esc_attr__( 'Archive hero preview', 'nubian-geographic-content-views' ); ?>">
				<?php endif; ?>
			</div>
			<p>
				<button type="button" class="button" id="ngcv-hero-select"
					data-modal-title="<?php echo esc_attr__( 'Choose the archive hero image', 'nubian-geographic-content-views' ); ?>"
					data-modal-button="<?php echo esc_attr__( 'Use this image', 'nubian-geographic-content-views' ); ?>"
				><?php echo esc_html( __( 'Select image', 'nubian-geographic-content-views' ) ); ?></button>
				<button type="button" class="button" id="ngcv-hero-change"
					data-modal-title="<?php echo esc_attr__( 'Choose the archive hero image', 'nubian-geographic-content-views' ); ?>"
					data-modal-button="<?php echo esc_attr__( 'Use this image', 'nubian-geographic-content-views' ); ?>"
				><?php echo esc_html( __( 'Change image', 'nubian-geographic-content-views' ) ); ?></button>
				<button type="button" class="button-link-delete" id="ngcv-hero-remove"><?php echo esc_html( __( 'Remove image', 'nubian-geographic-content-views' ) ); ?></button>
			</p>
		</div>
		<p class="description">
			<?php echo esc_html( __( 'Optional. Overrides the Articles page featured image in the archive hero band. Leave empty to fall back to the page featured image, and to the plain navy background when neither exists. The image applies to all languages.', 'nubian-geographic-content-views' ) ); ?>
		</p>
		<?php
	}
}
