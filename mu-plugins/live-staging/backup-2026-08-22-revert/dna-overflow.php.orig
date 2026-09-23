<?php
/**
 * Plugin Name: DN Apps mobile render fixes (staging)
 * Description: (1) FOUC guard with !important (wins the cascade vs Phlox's
 *              aux-page-* visibility rules), <noscript> fallback for no-JS;
 *              (2) kill Auxin #inner-body page-load animation; (3) html
 *              overflow-x clip. Cosmetic, 2026-08-21.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
add_action( 'wp_head', function () {
	echo '<style>#inner-body{transform:none !important;animation:none !important;transition:none !important;visibility:hidden !important}#inner-body.dna-ready{visibility:visible !important}html{overflow-x:clip}@supports not (overflow:clip){html{overflow-x:hidden}}</style>' . "\n";
	echo '<noscript><style>#inner-body{visibility:visible !important}</style></noscript>' . "\n";
}, 1 );
add_action( 'wp_footer', function () {
	echo '<script>(function(){function show(){var el=document.getElementById("inner-body");if(el)el.classList.add("dna-ready");}if(document.readyState==="complete"||document.readyState==="interactive"){setTimeout(show,0);}else{document.addEventListener("DOMContentLoaded",function(){setTimeout(show,0);});}setTimeout(show,3000);})();</script>' . "\n";
}, 99 );
