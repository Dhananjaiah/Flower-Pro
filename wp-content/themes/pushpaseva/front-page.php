<?php
/**
 * Landing page template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main>

	<!-- Hero -->
	<section class="ps-hero">
		<div class="ps-container ps-hero__inner">
			<h1 class="ps-hero__headline">
				<?php esc_html_e( 'Fresh Flowers Delivered Every Week for Your Daily Pooja', 'pushpaseva' ); ?>
			</h1>
			<p class="ps-hero__subheading">
				<?php esc_html_e( 'Never miss your daily pooja again. Fresh flowers delivered to your apartment every week.', 'pushpaseva' ); ?>
			</p>
			<div class="ps-hero__cta">
				<a href="#packages" class="ps-btn ps-btn--primary"><?php esc_html_e( 'Subscribe Now', 'pushpaseva' ); ?></a>
				<a href="#packages" class="ps-btn ps-btn--ghost"><?php esc_html_e( 'View Packages', 'pushpaseva' ); ?></a>
			</div>
		</div>
	</section>

	<!-- Why Choose Us -->
	<section class="ps-section ps-why">
		<div class="ps-container">
			<h2 class="ps-section__title"><?php esc_html_e( 'Why Choose Us', 'pushpaseva' ); ?></h2>
			<div class="ps-why__grid">
				<div class="ps-why__item">
					<span class="ps-why__icon">🌸</span>
					<h3><?php esc_html_e( 'Fresh Flowers', 'pushpaseva' ); ?></h3>
				</div>
				<div class="ps-why__item">
					<span class="ps-why__icon">🚚</span>
					<h3><?php esc_html_e( 'Weekly Delivery', 'pushpaseva' ); ?></h3>
				</div>
				<div class="ps-why__item">
					<span class="ps-why__icon">💰</span>
					<h3><?php esc_html_e( 'Affordable Subscriptions', 'pushpaseva' ); ?></h3>
				</div>
				<div class="ps-why__item">
					<span class="ps-why__icon">🔒</span>
					<h3><?php esc_html_e( 'Easy Online Payments', 'pushpaseva' ); ?></h3>
				</div>
				<div class="ps-why__item">
					<span class="ps-why__icon">🏠</span>
					<h3><?php esc_html_e( 'No Daily Market Visits', 'pushpaseva' ); ?></h3>
				</div>
			</div>
		</div>
	</section>

	<!-- Packages -->
	<section class="ps-section ps-packages" id="packages">
		<div class="ps-container">
			<h2 class="ps-section__title"><?php esc_html_e( 'Subscription Packages', 'pushpaseva' ); ?></h2>

			<?php
			$products = wc_get_products( array(
				'status'  => 'publish',
				'limit'   => -1,
				'orderby' => 'price',
				'order'   => 'ASC',
			) );
			?>

			<?php if ( empty( $products ) ) : ?>
				<p style="text-align:center;"><?php esc_html_e( 'Packages coming soon.', 'pushpaseva' ); ?></p>
			<?php else : ?>
				<div class="ps-packages__grid">
					<?php foreach ( $products as $product ) : ?>
						<?php $items = pushpaseva_get_flower_items( $product->get_id() ); ?>
						<div class="ps-plan-card">
							<h3 class="ps-plan-card__name"><?php echo esc_html( $product->get_name() ); ?></h3>
							<div class="ps-plan-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?><span class="ps-plan-card__period">/<?php esc_html_e( 'month', 'pushpaseva' ); ?></span></div>

							<?php if ( $product->get_short_description() ) : ?>
								<p class="ps-plan-card__desc"><?php echo wp_kses_post( $product->get_short_description() ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $items ) ) : ?>
								<ul class="ps-plan-card__items">
									<?php foreach ( $items as $item ) : ?>
										<?php if ( empty( $item['en'] ) && empty( $item['te'] ) ) continue; ?>
										<li>
											<?php echo esc_html( $item['en'] ); ?>
											<?php if ( ! empty( $item['te'] ) ) : ?>
												<span class="ps-plan-card__te">(<?php echo esc_html( $item['te'] ); ?>)</span>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<form class="cart" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" enctype="multipart/form-data">
								<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="ps-btn ps-btn--primary ps-plan-card__cta">
									<?php esc_html_e( 'Subscribe', 'pushpaseva' ); ?>
								</button>
							</form>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- Delivery Area Check -->
	<section class="ps-section ps-delivery-check">
		<div class="ps-container ps-delivery-check__inner">
			<h2 class="ps-section__title"><?php esc_html_e( 'Check Delivery in Your Area', 'pushpaseva' ); ?></h2>
			<form id="ps-delivery-form" class="ps-delivery-check__form">
				<input type="text" id="ps-delivery-input" placeholder="<?php esc_attr_e( 'Apartment name, pincode, or city', 'pushpaseva' ); ?>" required>
				<button type="submit" class="ps-btn ps-btn--primary"><?php esc_html_e( 'Check', 'pushpaseva' ); ?></button>
			</form>
			<p id="ps-delivery-result" class="ps-delivery-check__result" hidden></p>
		</div>
	</section>

	<!-- How It Works -->
	<section class="ps-section ps-how" id="how-it-works">
		<div class="ps-container">
			<h2 class="ps-section__title"><?php esc_html_e( 'How It Works', 'pushpaseva' ); ?></h2>
			<div class="ps-how__steps">
				<div class="ps-how__step">
					<div class="ps-how__num">1</div>
					<h3><?php esc_html_e( 'Choose Package', 'pushpaseva' ); ?></h3>
				</div>
				<div class="ps-how__arrow">&rarr;</div>
				<div class="ps-how__step">
					<div class="ps-how__num">2</div>
					<h3><?php esc_html_e( 'Pay Online', 'pushpaseva' ); ?></h3>
				</div>
				<div class="ps-how__arrow">&rarr;</div>
				<div class="ps-how__step">
					<div class="ps-how__num">3</div>
					<h3><?php esc_html_e( 'Receive Weekly Flowers', 'pushpaseva' ); ?></h3>
				</div>
			</div>
		</div>
	</section>

	<!-- FAQ -->
	<section class="ps-section ps-faq" id="faq">
		<div class="ps-container ps-faq__inner">
			<h2 class="ps-section__title"><?php esc_html_e( 'Frequently Asked Questions', 'pushpaseva' ); ?></h2>

			<details class="ps-faq__item">
				<summary><?php esc_html_e( 'How often is delivery?', 'pushpaseva' ); ?></summary>
				<p><?php esc_html_e( 'Flowers are delivered weekly, based on your selected package.', 'pushpaseva' ); ?></p>
			</details>
			<details class="ps-faq__item">
				<summary><?php esc_html_e( 'Can I cancel or pause?', 'pushpaseva' ); ?></summary>
				<p><?php esc_html_e( 'Yes — reach out to us on WhatsApp and we will take care of it for you.', 'pushpaseva' ); ?></p>
			</details>
			<details class="ps-faq__item">
				<summary><?php esc_html_e( 'Can I change my address?', 'pushpaseva' ); ?></summary>
				<p><?php esc_html_e( 'Yes, contact us before your next delivery and we will update it.', 'pushpaseva' ); ?></p>
			</details>
			<details class="ps-faq__item">
				<summary><?php esc_html_e( 'What is the refund policy?', 'pushpaseva' ); ?></summary>
				<p><?php esc_html_e( 'If a delivery is missed on our end, we will credit it toward your next order.', 'pushpaseva' ); ?></p>
			</details>
		</div>
	</section>

</main>

<?php get_footer(); ?>
