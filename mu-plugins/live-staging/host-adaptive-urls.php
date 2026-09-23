<?php
/**
 * Host-adaptive home/siteurl for local staging (DN Apps).
 * Allows viewing via Cloudflare tunnel / localhost without breaking canonical redirects.
 * Mirrors DNH/AC staging pattern: pre_option filters return scheme://request-host.
 */
if ( isset( $_SERVER['HTTP_HOST'] ) ) {
    $scheme = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) ? 'https' : 'http';
    add_filter( 'pre_option_home', function( $v ) use ( $scheme ) {
        return $scheme . '://' . $_SERVER['HTTP_HOST'];
    }, 999 );
    add_filter( 'pre_option_siteurl', function( $v ) use ( $scheme ) {
        return $scheme . '://' . $_SERVER['HTTP_HOST'];
    }, 999 );
}
