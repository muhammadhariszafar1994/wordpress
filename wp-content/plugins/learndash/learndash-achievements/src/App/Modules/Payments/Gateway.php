<?php
/**
 * Payment gateway for Achievements.
 *
 * @since 2.0.1
 *
 * @package LearnDash\Achievements
 */

namespace LearnDash\Achievements\Modules\Payments;

use Learndash_Transaction_Meta_DTO;
use LearnDash\Achievements\Database;
use LearnDash\Core\Models\Product;
use LearnDash\Core\Utilities\Cast;
use WP_Post;

/**
 * Payment gateway for Achievements.
 *
 * @since 2.0.1
 */
class Gateway extends \Learndash_Payment_Gateway {
	/**
	 * Gateway name.
	 *
	 * @var string
	 */
	private static string $gateway_name = 'achievements-points';

	/**
	 * Course or group post object.
	 *
	 * @var WP_Post|null
	 */
	private $post;

	/**
	 * Constructor.
	 *
	 * @param WP_Post $post Course or group post object.
	 */
	public function __construct( WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * Returns the gateway name.
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return self::$gateway_name;
	}

	/**
	 * Returns the gateway label.
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public static function get_label(): string {
		return __( 'Achievement Points', 'learndash-achievements' );
	}


	/**
	 * Adds hooks from gateway classes.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function add_extra_hooks(): void {
	}

	/**
	 * Enqueues scripts.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
	}

	/**
	 * Creates a session/order/subscription or prepares payment options on backend.
	 *
	 * @since 2.0.1
	 *
	 * @return void Json response.
	 */
	public function setup_payment(): void {
	}

	/**
	 * Configures gateway.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	protected function configure(): void {
	}

	/**
	 * Returns true if everything is configured and payment gateway can be used, otherwise false.
	 *
	 * @since 2.0.1
	 *
	 * @return bool
	 */
	public function is_ready(): bool {
		return true;
	}

	/**
	 * Returns true it's a test mode, otherwise false.
	 *
	 * @since 2.0.1
	 *
	 * @return bool
	 */
	protected function is_test_mode(): bool {
		return false;
	}

	/**
	 * Returns payment button HTML markup.
	 *
	 * @since 2.0.1
	 *
	 * @param array<mixed> $params Payment params.
	 * @param WP_Post      $post   Post being processing.
	 *
	 * @return string Payment button HTML markup.
	 */
	protected function map_payment_button_markup( array $params, WP_Post $post ): string {
		$button_text = __( 'Checkout', 'learndash-achievements' );

		ob_start();
		?>
		<form method="post">
			<?php
			wp_nonce_field(
				'achievements_redeem_' . $post->ID,
				'_achievements_nonce'
			);
			?>
			<input type="hidden" name="course_id" value="<?php echo esc_attr( (string) $post->ID ); ?>"/>
			<input type="submit" value="<?php echo esc_attr( $button_text ); ?>" class="btn-join ld--ignore-inline-css"/>
		</form>
		<?php
		$button = ob_get_clean();

		return (string) $button;
	}

	/**
	 * Maps transaction meta.
	 *
	 * @since 2.0.1
	 *
	 * @param mixed   $data    Data.
	 * @param Product $product Product.
	 *
	 * @throws \Learndash_DTO_Validation_Exception Transaction data validation exception.
	 *
	 * @return Learndash_Transaction_Meta_DTO
	 */
	protected function map_transaction_meta( $data, Product $product ): Learndash_Transaction_Meta_DTO {
		return Learndash_Transaction_Meta_DTO::create();
	}

	/**
	 * Handles the webhook.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function process_webhook(): void {
	}


	/**
	 * Returns the gateway label for checkout activities.
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public function get_checkout_label(): string {
		if ( empty( $this->post ) ) {
			return __( 'Pay with points', 'learndash-achievements' );
		}

		$price = learndash_get_setting( $this->post->ID, 'achievements_buy_course_course_price' );

		// can show up the button here.
		$label = sprintf(
			// translators: 1$: point price.
			esc_attr_x(
				'Pay with %1$s',
				'payment_button',
				'learndash-achievements'
			),
			sprintf(
				// translators: singular point plural points.
				_n(
					'%d point',
					'%d points',
					absint( $price ),
					'learndash-achievements'
				),
				Cast::to_string( $price )
			)
		);

		return $label;
	}

	/**
	 * Returns the gateway meta HTML that appears near the payment selector.
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public function get_checkout_meta_html(): string {
		$current_points = Database::get_user_points( get_current_user_id() );

		$html = '<span>' . sprintf(
			// translators: singular point plural points.
			_n(
				'%d Point Available',
				'%d Points Available',
				absint( $current_points ),
				'learndash-achievements'
			),
			$current_points
		) . '</span>';

		return $html;
	}

	/**
	 * Returns the gateway info text for checkout activities.
	 *
	 * @since 2.0.1
	 *
	 * @param string $product_type Type of product being purchased.
	 *
	 * @return string
	 */
	public function get_checkout_info_text( string $product_type ): string {
		return '';
	}
}
