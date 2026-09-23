<?php
/**
 * Plugin Name: DN Apps telemedicine entrance-animation neutralizer (staging)
 * Description: Kills the Auxin 'circle' page entrance animation VISUALLY on the
 *              telemedicine pages (8085 EN / 8125 zh-hant) while preserving the
 *              theme's transitionend-based scroll unlock. v2: also unlocks BODY
 *              (theme sets body overflow:hidden/height:100vh during circle) and
 *              completes the 'aux-page-animation-done' lifecycle via a 150ms
 *              collapsed transform transition + synthetic transitionend fallback.
 *              Targeted — all other pages keep the theme animation. 2026-08-22.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp', function () {
    if ( ! is_page( array( 8085, 8125 ) ) ) {
        return;
    }
    add_action( 'wp_head', function () {
        echo '<style id="dna-anim-fix">' . "\n"
            . '.csstransitions body.aux-page-animation-circle:not(.aux-page-animation-done) #inner-body{'
            . 'transform:perspective(1000px) translateZ(-1px) !important;'
            . 'clip-path:none !important;opacity:1 !important;height:auto !important;overflow:visible !important;'
            . 'transition:transform 150ms linear !important;'
            . '}' . "\n"
            . '.csstransitions body.aux-page-animation-circle.aux-page-show-circle:not(.aux-page-animation-done) #inner-body{'
            . 'transform:perspective(1000px) !important;'
            . '}' . "\n"
            . '.csstransitions body.aux-page-animation-circle:not(.aux-page-animation-done){'
            . 'overflow:visible !important;height:auto !important;'
            . '}' . "\n"
            . '.csstransitions .aux-page-show-circle .site-header-section{animation:none !important;}' . "\n"
            . '</style>' . "\n";
    }, 5 );
    add_action( 'wp_footer', function () {
        echo '<script>(function(){function force(){var b=document.body;if(!b||b.classList.contains("aux-page-animation-done"))return;var el=document.getElementById("inner-body");var ev=new Event("transitionend");try{Object.defineProperty(ev,"propertyName",{value:"transform"});}catch(e){}if(el)el.dispatchEvent(ev);}if(document.readyState==="complete"){setTimeout(force,400);}else{document.addEventListener("DOMContentLoaded",function(){setTimeout(force,400);});}})();</script>' . "\n";
    }, 99 );
} );
