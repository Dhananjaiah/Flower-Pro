<?php
/**
 * Idempotent seed script for PushpaSeva's à la carte individual flower
 * items (sold per pack, alongside the monthly subscription plans).
 *
 * Run on the server via WP-CLI from the WordPress root:
 *   wp eval-file bin/seed-flowers.php --allow-root
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

$flowers = array(
	array( 'sku' => 'flower-marigold', 'name' => 'Marigold (Banthi)', 'price' => 40, 'unit' => '250g' ),
	array( 'sku' => 'flower-jasmine', 'name' => 'Jasmine (Malle puvvulu)', 'price' => 80, 'unit' => '250g' ),
	array( 'sku' => 'flower-rose', 'name' => 'Rose (Gulabilu)', 'price' => 70, 'unit' => '250g' ),
	array( 'sku' => 'flower-chrysanthemum', 'name' => 'Chrysanthemum (Chamanthi)', 'price' => 45, 'unit' => '250g' ),
	array( 'sku' => 'flower-garland', 'name' => 'Flower Garland (Puvvula Dhanda)', 'price' => 120, 'unit' => '1 piece' ),
	array( 'sku' => 'flower-betel-leaves', 'name' => 'Betel Leaves (Thamalapakulu)', 'price' => 20, 'unit' => '1 bundle' ),
	array( 'sku' => 'flower-cotton-wicks', 'name' => 'Cotton Wicks (Vathulu)', 'price' => 15, 'unit' => '1 pack' ),
	array( 'sku' => 'flower-coconut', 'name' => 'Coconut (Kobbari Kaya)', 'price' => 25, 'unit' => '1 piece' ),
);

foreach ( $flowers as $flower ) {
	$existing_id = wc_get_product_id_by_sku( $flower['sku'] );

	if ( $existing_id ) {
		$product = wc_get_product( $existing_id );
		WP_CLI::log( "Updating existing product: {$flower['name']} (SKU {$flower['sku']}, ID {$existing_id})" );
	} else {
		$product = new WC_Product_Simple();
		WP_CLI::log( "Creating new product: {$flower['name']} (SKU {$flower['sku']})" );
	}

	$product->set_name( $flower['name'] );
	$product->set_sku( $flower['sku'] );
	$product->set_regular_price( (string) $flower['price'] );
	$product->set_catalog_visibility( 'visible' );
	$product->set_status( 'publish' );
	$product->set_manage_stock( false );
	$product->set_sold_individually( false );

	$product_id = $product->save();

	update_post_meta( $product_id, '_pushpaseva_product_type', 'individual' );
	update_post_meta( $product_id, '_pushpaseva_unit_label', $flower['unit'] );

	WP_CLI::success( "Saved '{$flower['name']}' as product #{$product_id}" );
}

WP_CLI::success( 'Seed complete.' );
