<?php
/**
 * PushpaSeva theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PUSHPASEVA_VERSION', '0.1.0' );

require_once get_stylesheet_directory() . '/inc/rest-api.php';

/**
 * Theme setup.
 */
function pushpaseva_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'custom-logo' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'pushpaseva' ),
	) );
}
add_action( 'after_setup_theme', 'pushpaseva_setup' );

/**
 * Enqueue styles and scripts.
 */
function pushpaseva_assets() {
	wp_enqueue_style(
		'pushpaseva-google-fonts',
		'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'pushpaseva-main',
		get_stylesheet_directory_uri() . '/assets/css/main.css',
		array(),
		PUSHPASEVA_VERSION
	);

	wp_enqueue_script(
		'pushpaseva-main',
		get_stylesheet_directory_uri() . '/assets/js/main.js',
		array(),
		PUSHPASEVA_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'pushpaseva_assets' );

/**
 * ------------------------------------------------------------------
 * Bilingual flower items (English / Telugu) on WooCommerce products
 * ------------------------------------------------------------------
 * Stored as post meta `_pushpaseva_flower_items`, an array of
 * associative arrays: [ 'en' => 'Marigold', 'te' => 'Banthi' ].
 */

function pushpaseva_flower_items_meta_box() {
	add_meta_box(
		'pushpaseva_flower_items',
		__( 'Flower Items (English / Telugu)', 'pushpaseva' ),
		'pushpaseva_render_flower_items_meta_box',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'pushpaseva_flower_items_meta_box' );

function pushpaseva_render_flower_items_meta_box( $post ) {
	$items = get_post_meta( $post->ID, '_pushpaseva_flower_items', true );
	if ( ! is_array( $items ) || empty( $items ) ) {
		$items = array( array( 'en' => '', 'te' => '' ) );
	}

	wp_nonce_field( 'pushpaseva_save_flower_items', 'pushpaseva_flower_items_nonce' );
	?>
	<p><?php esc_html_e( 'Add each flower/item included in this plan, with its English and Telugu name. These show on the plan card on the landing page.', 'pushpaseva' ); ?></p>
	<table class="widefat" id="pushpaseva-flower-items-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'English name', 'pushpaseva' ); ?></th>
				<th><?php esc_html_e( 'Telugu name', 'pushpaseva' ); ?></th>
				<th style="width:40px;"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $items as $item ) : ?>
				<tr>
					<td><input type="text" class="widefat" name="pushpaseva_flower_en[]" value="<?php echo esc_attr( $item['en'] ?? '' ); ?>" placeholder="e.g. Marigold" /></td>
					<td><input type="text" class="widefat" name="pushpaseva_flower_te[]" value="<?php echo esc_attr( $item['te'] ?? '' ); ?>" placeholder="e.g. Banthi (బంతి)" /></td>
					<td><button type="button" class="button pushpaseva-remove-row">&times;</button></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p><button type="button" class="button" id="pushpaseva-add-row"><?php esc_html_e( '+ Add item', 'pushpaseva' ); ?></button></p>
	<script>
	(function () {
		document.getElementById('pushpaseva-add-row').addEventListener('click', function () {
			var tbody = document.querySelector('#pushpaseva-flower-items-table tbody');
			var row = tbody.rows[0].cloneNode(true);
			row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
			tbody.appendChild(row);
		});
		document.getElementById('pushpaseva-flower-items-table').addEventListener('click', function (e) {
			if (e.target.classList.contains('pushpaseva-remove-row')) {
				var tbody = document.querySelector('#pushpaseva-flower-items-table tbody');
				if (tbody.rows.length > 1) {
					e.target.closest('tr').remove();
				}
			}
		});
	})();
	</script>
	<?php
}

function pushpaseva_save_flower_items( $post_id ) {
	if ( ! isset( $_POST['pushpaseva_flower_items_nonce'] ) ||
		! wp_verify_nonce( $_POST['pushpaseva_flower_items_nonce'], 'pushpaseva_save_flower_items' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$en_names = isset( $_POST['pushpaseva_flower_en'] ) ? (array) $_POST['pushpaseva_flower_en'] : array();
	$te_names = isset( $_POST['pushpaseva_flower_te'] ) ? (array) $_POST['pushpaseva_flower_te'] : array();

	$items = array();
	foreach ( $en_names as $i => $en ) {
		$en = sanitize_text_field( $en );
		$te = sanitize_text_field( $te_names[ $i ] ?? '' );
		if ( '' === $en && '' === $te ) {
			continue;
		}
		$items[] = array( 'en' => $en, 'te' => $te );
	}

	update_post_meta( $post_id, '_pushpaseva_flower_items', $items );
}
add_action( 'save_post_product', 'pushpaseva_save_flower_items' );

/**
 * Get the bilingual flower items for a product.
 *
 * @param int $product_id
 * @return array[] Array of [ 'en' => string, 'te' => string ]
 */
function pushpaseva_get_flower_items( $product_id ) {
	$items = get_post_meta( $product_id, '_pushpaseva_flower_items', true );
	return is_array( $items ) ? $items : array();
}

/**
 * ------------------------------------------------------------------
 * WooCommerce checkout: Apartment Name + Flat Number
 * ------------------------------------------------------------------
 */

function pushpaseva_checkout_fields( $fields ) {
	$fields['billing']['billing_apartment_name'] = array(
		'label'       => __( 'Apartment / Community Name', 'pushpaseva' ),
		'placeholder' => __( 'e.g. Green Meadows', 'pushpaseva' ),
		'required'    => true,
		'class'       => array( 'form-row-wide' ),
		'priority'    => 25,
	);

	$fields['billing']['billing_flat_number'] = array(
		'label'       => __( 'Flat / Door Number', 'pushpaseva' ),
		'placeholder' => __( 'e.g. B-304', 'pushpaseva' ),
		'required'    => true,
		'class'       => array( 'form-row-wide' ),
		'priority'    => 26,
	);

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'pushpaseva_checkout_fields' );

function pushpaseva_save_checkout_fields( $order_id ) {
	if ( ! empty( $_POST['billing_apartment_name'] ) ) {
		update_post_meta( $order_id, '_billing_apartment_name', sanitize_text_field( $_POST['billing_apartment_name'] ) );
	}
	if ( ! empty( $_POST['billing_flat_number'] ) ) {
		update_post_meta( $order_id, '_billing_flat_number', sanitize_text_field( $_POST['billing_flat_number'] ) );
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'pushpaseva_save_checkout_fields' );

function pushpaseva_admin_order_fields( $order ) {
	$apartment = get_post_meta( $order->get_id(), '_billing_apartment_name', true );
	$flat      = get_post_meta( $order->get_id(), '_billing_flat_number', true );
	if ( $apartment ) {
		echo '<p><strong>' . esc_html__( 'Apartment / Community', 'pushpaseva' ) . ':</strong> ' . esc_html( $apartment ) . '</p>';
	}
	if ( $flat ) {
		echo '<p><strong>' . esc_html__( 'Flat / Door No.', 'pushpaseva' ) . ':</strong> ' . esc_html( $flat ) . '</p>';
	}
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'pushpaseva_admin_order_fields' );
