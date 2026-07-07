<?php
/**
 * Custom public REST API for the PushpaSeva mobile app.
 *
 * Deliberately does NOT use WooCommerce's REST API keys — those are
 * store-manager-level credentials and would have to be embedded in the
 * shipped app, where anyone can extract them from the app package and
 * get full read/write access to every order and customer in the store.
 *
 * Instead these routes expose exactly the capabilities the app needs
 * (list plans/flowers, create an order from a cart, check an order's
 * status) — the same capabilities any anonymous visitor already has via
 * the public checkout form, just as JSON.
 *
 * Products are distinguished by the `_pushpaseva_product_type` meta:
 * 'subscription' (the 3 monthly plans) or 'individual' (à la carte
 * flowers, sold per pack — see `_pushpaseva_unit_label`, e.g. "250g").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {

	register_rest_route( 'pushpaseva/v1', '/plans', array(
		'methods'             => 'GET',
		'callback'            => 'pushpaseva_api_get_plans',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'pushpaseva/v1', '/flowers', array(
		'methods'             => 'GET',
		'callback'            => 'pushpaseva_api_get_flowers',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'pushpaseva/v1', '/orders', array(
		'methods'             => 'POST',
		'callback'            => 'pushpaseva_api_create_order',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'pushpaseva/v1', '/orders/(?P<id>\d+)', array(
		'methods'             => 'GET',
		'callback'            => 'pushpaseva_api_get_order_status',
		'permission_callback' => '__return_true',
		'args'                => array(
			'id' => array(
				'validate_callback' => function ( $param ) {
					return is_numeric( $param );
				},
			),
		),
	) );
} );

/**
 * GET /pushpaseva/v1/plans
 * Only products tagged as subscription plans.
 */
function pushpaseva_api_get_plans( WP_REST_Request $request ) {
	$products = pushpaseva_get_products_by_type( 'subscription' );

	$plans = array();
	foreach ( $products as $product ) {
		$plans[] = array(
			'id'          => $product->get_id(),
			'name'        => $product->get_name(),
			'price'       => (float) $product->get_price(),
			'currency'    => get_woocommerce_currency(),
			'description' => wp_strip_all_tags( $product->get_short_description() ),
			'items'       => array_values( array_filter(
				pushpaseva_get_flower_items( $product->get_id() ),
				function ( $item ) {
					return ! empty( $item['en'] ) || ! empty( $item['te'] );
				}
			) ),
		);
	}

	return new WP_REST_Response( $plans, 200 );
}

/**
 * GET /pushpaseva/v1/flowers
 * Only products tagged as individual (à la carte) items.
 */
function pushpaseva_api_get_flowers( WP_REST_Request $request ) {
	$products = pushpaseva_get_products_by_type( 'individual' );

	$flowers = array();
	foreach ( $products as $product ) {
		$flowers[] = array(
			'id'         => $product->get_id(),
			'name'       => $product->get_name(),
			'price'      => (float) $product->get_price(),
			'currency'   => get_woocommerce_currency(),
			'unit_label' => get_post_meta( $product->get_id(), '_pushpaseva_unit_label', true ) ?: 'pack',
		);
	}

	return new WP_REST_Response( $flowers, 200 );
}

/**
 * Helper: published, purchasable products with a given
 * _pushpaseva_product_type meta value.
 */
function pushpaseva_get_products_by_type( $type ) {
	return wc_get_products( array(
		'status'     => 'publish',
		'limit'      => -1,
		'orderby'    => 'price',
		'order'      => 'ASC',
		'meta_key'   => '_pushpaseva_product_type',
		'meta_value' => $type,
	) );
}

/**
 * POST /pushpaseva/v1/orders
 *
 * Body: {
 *   items: [{ product_id, quantity }, ...],
 *   name, email, phone, apartment_name, flat_number,
 *   address_1, city, state, pincode
 * }
 */
function pushpaseva_api_create_order( WP_REST_Request $request ) {
	// Basic per-IP rate limit: 10 order attempts per hour.
	$ip       = $request->get_header( 'x-forwarded-for' ) ?: ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
	$rate_key = 'pushpaseva_order_rl_' . md5( $ip );
	$attempts = (int) get_transient( $rate_key );
	if ( $attempts >= 10 ) {
		return new WP_Error( 'rate_limited', 'Too many attempts. Please try again later.', array( 'status' => 429 ) );
	}
	set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

	$items_param = $request->get_param( 'items' );
	$name        = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email       = sanitize_email( (string) $request->get_param( 'email' ) );
	$phone       = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$apartment   = sanitize_text_field( (string) $request->get_param( 'apartment_name' ) );
	$flat        = sanitize_text_field( (string) $request->get_param( 'flat_number' ) );
	$address_1   = sanitize_text_field( (string) $request->get_param( 'address_1' ) );
	$city        = sanitize_text_field( (string) $request->get_param( 'city' ) );
	$state       = sanitize_text_field( (string) $request->get_param( 'state' ) );
	$pincode     = sanitize_text_field( (string) $request->get_param( 'pincode' ) );

	if ( ! is_array( $items_param ) || empty( $items_param ) ) {
		return new WP_Error( 'invalid_request', 'items must be a non-empty array of { product_id, quantity }.', array( 'status' => 400 ) );
	}
	if ( '' === $name || '' === $phone || ! is_email( $email ) ) {
		return new WP_Error( 'invalid_request', 'name, phone, and a valid email are required.', array( 'status' => 400 ) );
	}
	if ( '' === $apartment || '' === $flat || '' === $address_1 || '' === $city || '' === $pincode ) {
		return new WP_Error( 'invalid_request', 'apartment_name, flat_number, address_1, city, and pincode are required.', array( 'status' => 400 ) );
	}

	// Validate every line item up front before creating anything.
	$line_items = array();
	foreach ( $items_param as $raw_item ) {
		$product_id = absint( $raw_item['product_id'] ?? 0 );
		$quantity   = max( 1, min( 20, absint( $raw_item['quantity'] ?? 1 ) ) );

		$product = wc_get_product( $product_id );
		if ( ! $product || 'publish' !== $product->get_status() || ! $product->is_purchasable() ) {
			return new WP_Error( 'invalid_product', "Product {$product_id} is not available.", array( 'status' => 400 ) );
		}

		$line_items[] = array( 'product' => $product, 'quantity' => $quantity );
	}

	try {
		$order = wc_create_order();
		foreach ( $line_items as $line ) {
			$order->add_product( $line['product'], $line['quantity'] );
		}

		$name_parts = explode( ' ', $name, 2 );
		$order->set_billing_first_name( $name_parts[0] );
		$order->set_billing_last_name( $name_parts[1] ?? '' );
		$order->set_billing_email( $email );
		$order->set_billing_phone( $phone );
		$order->set_billing_address_1( $address_1 );
		$order->set_billing_city( $city );
		$order->set_billing_state( $state );
		$order->set_billing_postcode( $pincode );
		$order->set_billing_country( 'IN' );

		update_post_meta( $order->get_id(), '_billing_apartment_name', $apartment );
		update_post_meta( $order->get_id(), '_billing_flat_number', $flat );
		update_post_meta( $order->get_id(), '_pushpaseva_source', 'app' );

		$order->set_payment_method( 'razorpay' );
		$order->calculate_totals();
		$order->set_status( 'pending' );
		$order->save();

		return new WP_REST_Response( array(
			'order_id'  => $order->get_id(),
			'order_key' => $order->get_order_key(),
			'pay_url'   => $order->get_checkout_payment_url(),
		), 201 );
	} catch ( Exception $e ) {
		return new WP_Error( 'order_failed', 'Could not create order.', array( 'status' => 500 ) );
	}
}

/**
 * GET /pushpaseva/v1/orders/{id}?key={order_key}
 */
function pushpaseva_api_get_order_status( WP_REST_Request $request ) {
	$order_id  = absint( $request->get_param( 'id' ) );
	$order_key = sanitize_text_field( (string) $request->get_param( 'key' ) );

	$order = wc_get_order( $order_id );
	if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) ) {
		return new WP_Error( 'not_found', 'Order not found.', array( 'status' => 404 ) );
	}

	return new WP_REST_Response( array(
		'order_id' => $order->get_id(),
		'status'   => $order->get_status(),
		'is_paid'  => $order->is_paid(),
		'total'    => (float) $order->get_total(),
		'currency' => $order->get_currency(),
	), 200 );
}
