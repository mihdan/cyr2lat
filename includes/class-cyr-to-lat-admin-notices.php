<?php
/**
 * Admin Notices.
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_Admin_Notices
 *
 * @class Cyr_To_Lat_Admin_Notices
 */
class Cyr_To_Lat_Admin_Notices {

	/**
	 * Admin notices array.
	 *
	 * @var array
	 */
	private $notices = array();

	/**
	 * Cyr_To_Lat_Admin_Notices constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
	}

	/**
	 * Add admin notice.
	 *
	 * @param string $message Message to show.
	 * @param string $class   Message class: notice notice-success notice-error notice-warning notice-info
	 *                        is-dismissible.
	 */
	public function add_notice( $message, $class = 'notice' ) {
		$this->notices[] = array(
			'message' => $message,
			'class'   => $class,
		);
	}

	/**
	 * Show all notices.
	 */
	public function show_notices() {
		foreach ( $this->notices as $notice ) {
			?>
			<div class="<?php echo esc_attr( $notice['class'] ); ?>">
				<p>
					<strong>
						<?php echo wp_kses_post( $notice['message'] ); ?>
					</strong>
				</p>
			</div>
			<?php
		}
	}
}
