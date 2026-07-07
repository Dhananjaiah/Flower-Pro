<?php
/**
 * Footer template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<footer class="ps-footer">
		<div class="ps-container ps-footer__inner">
			<div class="ps-footer__col">
				<div class="ps-logo ps-logo--light"><?php bloginfo( 'name' ); ?></div>
				<p><?php esc_html_e( 'Fresh pooja flowers, delivered weekly to your apartment.', 'pushpaseva' ); ?></p>
			</div>
			<div class="ps-footer__col">
				<h4><?php esc_html_e( 'Contact', 'pushpaseva' ); ?></h4>
				<p>
					<a href="https://wa.me/919999999999" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp Us', 'pushpaseva' ); ?></a><br>
					<a href="tel:+919999999999">+91 99999 99999</a>
				</p>
			</div>
			<div class="ps-footer__col">
				<h4><?php esc_html_e( 'Policies', 'pushpaseva' ); ?></h4>
				<p>
					<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy Policy', 'pushpaseva' ); ?></a><br>
					<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'pushpaseva' ); ?></a>
				</p>
			</div>
		</div>
		<div class="ps-container ps-footer__bottom">
			&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'pushpaseva' ); ?>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>
