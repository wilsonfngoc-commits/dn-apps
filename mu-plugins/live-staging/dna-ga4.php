<?php
/**
 * Plugin Name: DN Apps GA4 telemedicine tracking (staging)
 * Description: gtag G-KVG8BQ85VC (DoctorNow Home GA4) on homepage (root / + /zh-hant/), telemedicine
 *              + download pages (EN/TC); download-intent + App Store/Google Play outbound-click
 *              events for DN App Search Ads.
 *              2026-08-22 REAPPLY (Wilson): telemedicine 8085/8125 restored to GA4 scope — additive,
 *              dna-anim-fix.php kept untouched. Rollback: mu-plugins/backup-2026-08-22-revert/
 *              dna-ga4.php.pre-reapply (tele-excluded version).
 *              2026-08-25 ADD homepage 6148 + zh-hant 7219 (CBO task: DN App Search Ads land on
 *              root URL — conversion tracking unblocked). Page IDs: homepage 6148/7219,
 *              telemedicine 8085/8125, download 8015/8049.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function dna_ga4_is_target() {
	global $post;
	if ( ! $post || empty( $post->ID ) ) {
		return false;
	}
	return in_array( (int) $post->ID, array( 6148, 7219, 8085, 8125, 8015, 8049 ), true );
}

add_action( 'wp_head', 'dna_ga4_head', 1 );
function dna_ga4_head() {
	if ( ! dna_ga4_is_target() ) {
		return;
	}
	?>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-KVG8BQ85VC"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'G-KVG8BQ85VC');
</script>
	<?php
}

add_action( 'wp_footer', 'dna_ga4_events', 99 );
function dna_ga4_events() {
	if ( ! dna_ga4_is_target() ) {
		return;
	}
	?>
<script>
document.addEventListener('click', function (e) {
	var a = e.target && e.target.closest ? e.target.closest('a') : null;
	if (!a) return;
	var href = (a.getAttribute('href') || '') + (a.href || '');
	// App Store / Google Play outbound (download pages)
	if (href.indexOf('apps.apple.com') !== -1) { gtag('event', 'dn_download_app_store', {event_category: 'outbound', transport_type: 'beacon'}); return; }
	if (href.indexOf('play.google.com') !== -1) { gtag('event', 'dn_download_google_play', {event_category: 'outbound', transport_type: 'beacon'}); return; }
	// Download button on telemedicine pages (leads to /download/)
	if (href.indexOf('/download/') !== -1) { gtag('event', 'dn_download_intent', {event_category: 'download'}); return; }
});
</script>
	<?php
}
