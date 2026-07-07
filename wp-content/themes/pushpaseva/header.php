<?php
/**
 * Header template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="ps-header">
	<div class="ps-container ps-header__inner">
		<a class="ps-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php bloginfo( 'name' ); ?>
		</a>
		<nav class="ps-header__nav">
			<a href="#packages"><?php esc_html_e( 'Packages', 'pushpaseva' ); ?></a>
			<a href="#how-it-works"><?php esc_html_e( 'How It Works', 'pushpaseva' ); ?></a>
			<a href="#faq"><?php esc_html_e( 'FAQ', 'pushpaseva' ); ?></a>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a class="ps-header__cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
					<?php esc_html_e( 'Cart', 'pushpaseva' ); ?>
					(<?php echo absint( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?>)
				</a>
			<?php endif; ?>
		</nav>
	</div>
</header>
