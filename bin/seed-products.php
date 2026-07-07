<?php
/**
 * Idempotent seed script for PushpaSeva's initial 3 subscription plans.
 *
 * Run on the server via WP-CLI from the WordPress root:
 *   wp eval-file bin/seed-products.php --allow-root
 *
 * Safe to re-run: matches existing products by SKU and updates them
 * instead of creating duplicates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

$plans = array(
	array(
		'sku'   => 'plan-basic',
		'name'  => 'Basic',
		'price' => 299,
		'desc'  => 'A simple weekly flower set for everyday pooja.',
		'items' => array(
			array( 'en' => 'Marigold', 'te' => 'Banthi (బంతి)' ),
			array( 'en' => 'Jasmine', 'te' => 'Malle puvvulu (మల్లె పువ్వులు)' ),
			array( 'en' => 'Betel Leaves', 'te' => 'Thamalapakulu (తమలపాకులు)' ),
			array( 'en' => 'Cotton Wicks', 'te' => 'Vathulu (వత్తులు)' ),
		),
	),
	array(
		'sku'   => 'plan-standard',
		'name'  => 'Standard',
		'price' => 499,
		'desc'  => 'Everything in Basic, plus more variety for regular pooja needs.',
		'items' => array(
			array( 'en' => 'Marigold', 'te' => 'Banthi (బంతి)' ),
			array( 'en' => 'Jasmine', 'te' => 'Malle puvvulu (మల్లె పువ్వులు)' ),
			array( 'en' => 'Betel Leaves', 'te' => 'Thamalapakulu (తమలపాకులు)' ),
			array( 'en' => 'Cotton Wicks', 'te' => 'Vathulu (వత్తులు)' ),
			array( 'en' => 'Chrysanthemum', 'te' => 'Chamanthi (చామంతి)' ),
			array( 'en' => 'Rose', 'te' => 'Gulabilu (గులాబీలు)' ),
			array( 'en' => 'Coconut', 'te' => 'Kobbari Kaya (కొబ్బరి కాయ)' ),
		),
	),
	array(
		'sku'   => 'plan-premium',
		'name'  => 'Premium',
		'price' => 799,
		'desc'  => 'Everything in Standard, plus garlands and priority delivery.',
		'items' => array(
			array( 'en' => 'Marigold', 'te' => 'Banthi (బంతి)' ),
			array( 'en' => 'Jasmine', 'te' => 'Malle puvvulu (మల్లె పువ్వులు)' ),
			array( 'en' => 'Betel Leaves', 'te' => 'Thamalapakulu (తమలపాకులు)' ),
			array( 'en' => 'Cotton Wicks', 'te' => 'Vathulu (వత్తులు)' ),
			array( 'en' => 'Chrysanthemum', 'te' => 'Chamanthi (చామంతి)' ),
			array( 'en' => 'Rose', 'te' => 'Gulabilu (గులాబీలు)' ),
			array( 'en' => 'Coconut', 'te' => 'Kobbari Kaya (కొబ్బరి కాయ)' ),
			array( 'en' => 'Flower Garland', 'te' => 'Puvvula Dhanda (పువ్వుల దండ)' ),
			array( 'en' => 'Priority Delivery', 'te' => '' ),
		),
	),
);

foreach ( $plans as $plan ) {
	$existing_id = wc_get_product_id_by_sku( $plan['sku'] );

	if ( $existing_id ) {
		$product = wc_get_product( $existing_id );
		WP_CLI::log( "Updating existing product: {$plan['name']} (SKU {$plan['sku']}, ID {$existing_id})" );
	} else {
		$product = new WC_Product_Simple();
		WP_CLI::log( "Creating new product: {$plan['name']} (SKU {$plan['sku']})" );
	}

	$product->set_name( $plan['name'] );
	$product->set_sku( $plan['sku'] );
	$product->set_regular_price( (string) $plan['price'] );
	$product->set_short_description( $plan['desc'] );
	$product->set_catalog_visibility( 'visible' );
	$product->set_status( 'publish' );
	$product->set_manage_stock( false );
	$product->set_sold_individually( true );

	$product_id = $product->save();

	update_post_meta( $product_id, '_pushpaseva_flower_items', $plan['items'] );

	WP_CLI::success( "Saved '{$plan['name']}' as product #{$product_id}" );
}

WP_CLI::success( 'Seed complete.' );
