<?php
/**
 * Default multi-column template for the AlphaListing plugin
 *
 * This template will be given the variable `$a_z_query` which is an instance
 * of `AlphaListing`.
 *
 * You can override this template by copying this file into your theme
 * directory.
 *
 * @package alphalisting
 */

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * This value indicates the number of posts to require before a second column
 * is created. However, due to the design of web browsers, the posts will flow
 * evenly between the available columns. E.g. if you have 11 items, a value of
 * 10 here will create two columns with 6 items in the first column and 5 items
 * in the second column.
 */
$a_z_listing_minpercol = 10;
?>

<div id="<?php $a_z_query->the_instance_id(); ?>" style="<?php $a_z_query->get_customized_column_styles(); ?>" class="az-listing">
	<div class="az-letters-wrap">
		<div class="az-letters">
			<?php $a_z_query->the_letters(); ?>
		</div>
	</div>
	<?php if ( $a_z_query->have_letters() ) : ?>
	<div class="items-outer">
		<div class="items-inner">
			<?php
			while ( $a_z_query->have_letters() ) :
				$a_z_query->the_letter();
				?>
				<?php if ( $a_z_query->have_items() ) : ?>
					<?php
					$a_z_listing_item_count  = $a_z_query->get_the_letter_items_count();
					$a_z_listing_num_columns = max(
						1,
						min(
							16,
							ceil( $a_z_listing_item_count / $a_z_listing_minpercol )
						)
					);
					?>
					<div class="letter-section" id="<?php $a_z_query->the_letter_id(); ?>">
						<h2 class="letter-title">
							<span>
								<?php $a_z_query->the_letter_title(); ?>
							</span>
						</h2>
						<?php $a_z_listing_column_class = "max-$a_z_listing_num_columns-columns"; ?>
						<ul class="az-columns <?php echo esc_attr( $a_z_listing_column_class ); ?>">
							<?php
							while ( $a_z_query->have_items() ) :
								$a_z_query->the_item();
								?>
								<li>
									<a href="<?php $a_z_query->the_permalink(); ?>">
										<?php $a_z_query->the_title(); ?>
									</a>
								</li>
							<?php endwhile; ?>
						</ul>

						<?php if ( apply_filters( 'alphalisting_show_back_to_top', true, $a_z_query ) ) : ?>
							<div class="back-to-top">
								<a href="#<?php $a_z_query->the_instance_id(); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
										<path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z"/>
									</svg>
									<?php esc_html_e( 'Back to top', 'alphalisting' ); ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
					<?php
				endif;
			endwhile;
			?>
		</div>
	</div>
	<?php else : ?>
		<p><?php esc_html_e( 'There are no posts included in this index.', 'alphalisting' ); ?></p>
	<?php endif; ?>
</div>
