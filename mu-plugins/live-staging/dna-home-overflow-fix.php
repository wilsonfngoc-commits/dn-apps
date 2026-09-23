<?php
/**
 * Plugin Name: DN Apps homepage mobile overflow fix (staging)
 * Description: Mobile pinch-zoom white-space lock (Wilson, reproduced staging + prod 2026-08-25).
 *              Root cause: horizontal page overflow on mobile (scrollWidth 545px @ 390px viewport).
 *              ① hero phone-mockup widget (elementor-element-68b89618, aux-scroll-anim — 650px
 *              absolute at left:-105px, img iphone_mockup-01-3.png) bleeds 155px past right edge;
 *              ② Element Pack carousel "outside arrows" (elementor-element-0622ce2, bdt-navigation
 *              prev/next sit ±20px outside the 350px widget → next arrow right edge 430px) bleeds
 *              40px (prod-only; masked by ① until ① fixed).
 *              Fix: clip hero section (74fe363) + carousel widget (0622ce2) on ≤767px — zero
 *              visible change (bleed is off-screen by design; carousel swipe still works on touch).
 *              Covers EN (6148) + zh-hant (7219) homepages. Rollback: delete this file.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_head', 'dna_home_overflow_css', 20 );
function dna_home_overflow_css() {
	if ( ! is_front_page() && ! is_page( array( 6148, 7219 ) ) ) {
		return;
	}
	?>
<style id="dna-home-overflow-fix">
@media (max-width: 767px) {
	.elementor-element-74fe363,
	.elementor-element-0622ce2 { overflow-x: hidden; }
}
</style>
	<?php
}
