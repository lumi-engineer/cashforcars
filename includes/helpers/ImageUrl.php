<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy domains that may be stored in uploaded image URLs.
 */
function ci_get_legacy_image_domains() {
	return array(
		'https://cashforcars.local',
		'http://cashforcars.local',
	);
}

/**
 * Normalize a stored image URL for display.
 */
function ci_normalize_image_url( $url ) {
	if ( empty( $url ) ) {
		return '';
	}

	$url = trim( $url );

	if ( strpos( $url, '/wp-content/' ) === 0 ) {
		return esc_url( home_url( $url ) );
	}

	$site_url = untrailingslashit( home_url() );

	foreach ( ci_get_legacy_image_domains() as $legacy_domain ) {
		if ( strpos( $url, $legacy_domain ) === 0 ) {
			$url = $site_url . substr( $url, strlen( untrailingslashit( $legacy_domain ) ) );
			break;
		}
	}

	return esc_url( $url );
}

/**
 * Convert an absolute uploads URL to a site-relative path for storage.
 */
function ci_store_image_url( $url ) {
	if ( empty( $url ) ) {
		return '';
	}

	$upload_dir = wp_upload_dir();

	if ( ! empty( $upload_dir['baseurl'] ) && strpos( $url, $upload_dir['baseurl'] ) === 0 ) {
		return str_replace( $upload_dir['baseurl'], '', $url );
	}

	foreach ( ci_get_legacy_image_domains() as $legacy_domain ) {
		if ( strpos( $url, $legacy_domain ) === 0 ) {
			return str_replace( $legacy_domain, '', $url );
		}
	}

	return $url;
}

/**
 * Normalize a JSON-encoded list of image paths/URLs.
 */
function ci_normalize_image_urls_json( $json ) {
	if ( empty( $json ) ) {
		return array();
	}

	$urls = json_decode( $json, true );
	if ( ! is_array( $urls ) ) {
		return array();
	}

	return array_map( 'ci_normalize_image_url', $urls );
}

/**
 * Render clickable uploaded image previews.
 */
function ci_render_image_previews( $json, $container_class = 'image-preview-container' ) {
	$urls = ci_normalize_image_urls_json( $json );

	if ( empty( $urls ) ) {
		return;
	}

	echo '<div class="' . esc_attr( $container_class ) . ' mt-5 mb-5">';
	foreach ( $urls as $image_url ) {
		echo '<div class="image-preview-item">';
		echo '<img style="height: 200px; width:200px;" src="' . esc_url( $image_url ) . '" data-ci-image="' . esc_attr( $image_url ) . '" alt="Uploaded Image" class="preview-image" role="button" tabindex="0">';
		echo '</div>';
	}
	echo '</div>';
}
