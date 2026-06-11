<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CI_Quote_Status {

	const OFFERED  = 1;
	const ACCEPTED = 2;
	const CANCELED = 3;

	const IMAGES_NONE     = 'none';
	const IMAGES_PENDING  = 'pending';
	const IMAGES_APPROVED = 'approved';
	const IMAGES_REJECTED = 'rejected';

	const REVIEW_NONE     = 'none';
	const REVIEW_PENDING  = 'pending';
	const REVIEW_APPROVED = 'approved';
	const REVIEW_REJECTED = 'rejected';

	public static function migrate_legacy_rows( $wpdb, $quotes_table ) {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$quotes_table}
				SET status = %d,
					images_status = %s,
					title_review = %s,
					car_review = %s
				WHERE status = 4",
				self::OFFERED,
				self::IMAGES_PENDING,
				self::REVIEW_PENDING,
				self::REVIEW_PENDING
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$quotes_table}
				SET status = %d,
					images_status = %s,
					title_review = %s,
					car_review = %s
				WHERE status = 5",
				self::OFFERED,
				self::IMAGES_APPROVED,
				self::REVIEW_APPROVED,
				self::REVIEW_APPROVED
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$quotes_table}
				SET status = %d,
					images_status = %s,
					title_review = %s,
					car_review = COALESCE(NULLIF(car_review, ''), %s)
				WHERE status = 6",
				self::OFFERED,
				self::IMAGES_REJECTED,
				self::REVIEW_REJECTED,
				self::REVIEW_PENDING
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$quotes_table}
				SET status = %d,
					images_status = %s,
					car_review = %s,
					title_review = COALESCE(NULLIF(title_review, ''), %s)
				WHERE status = 7",
				self::OFFERED,
				self::IMAGES_REJECTED,
				self::REVIEW_REJECTED,
				self::REVIEW_PENDING
			)
		);
	}

	public static function normalize_row( $row ) {
		if ( ! $row ) {
			return $row;
		}

		if ( empty( $row->images_status ) ) {
			$row->images_status = self::IMAGES_NONE;
		}
		if ( empty( $row->title_review ) ) {
			$row->title_review = self::REVIEW_NONE;
		}
		if ( empty( $row->car_review ) ) {
			$row->car_review = self::REVIEW_NONE;
		}

		return $row;
	}

	public static function can_accept_offer( $row ) {
		$row = self::normalize_row( $row );
		return (int) $row->status === self::OFFERED
			&& $row->images_status === self::IMAGES_APPROVED;
	}

	public static function customer_needs_upload( $row ) {
		$row = self::normalize_row( $row );

		if ( (int) $row->status !== self::OFFERED ) {
			return false;
		}

		if ( $row->images_status === self::IMAGES_APPROVED ) {
			return false;
		}

		if ( $row->images_status === self::IMAGES_PENDING ) {
			return false;
		}

		return true;
	}

	public static function customer_waiting_review( $row ) {
		$row = self::normalize_row( $row );
		return (int) $row->status === self::OFFERED
			&& $row->images_status === self::IMAGES_PENDING;
	}

	public static function customer_can_reupload( $row ) {
		$row = self::normalize_row( $row );
		return (int) $row->status === self::OFFERED
			&& $row->images_status === self::IMAGES_REJECTED;
	}

	public static function admin_can_review_images( $row ) {
		$row = self::normalize_row( $row );
		return (int) $row->status === self::OFFERED
			&& in_array( $row->images_status, array( self::IMAGES_PENDING, self::IMAGES_REJECTED, self::IMAGES_APPROVED ), true )
			&& ( ! empty( $row->title_images ) || ! empty( $row->car_images ) );
	}

	public static function sync_images_status( $title_review, $car_review ) {
		if ( $title_review === self::REVIEW_REJECTED || $car_review === self::REVIEW_REJECTED ) {
			return self::IMAGES_REJECTED;
		}

		if ( $title_review === self::REVIEW_APPROVED && $car_review === self::REVIEW_APPROVED ) {
			return self::IMAGES_APPROVED;
		}

		if ( $title_review === self::REVIEW_PENDING || $car_review === self::REVIEW_PENDING ) {
			return self::IMAGES_PENDING;
		}

		return self::IMAGES_NONE;
	}

	public static function get_status_badge_html( $row ) {
		$row = self::normalize_row( $row );
		$html = '';

		switch ( (int) $row->status ) {
			case self::ACCEPTED:
				$html = '<span class="badge bg-success">Accepted</span>';
				break;
			case self::CANCELED:
				$html = '<span class="badge bg-danger">Canceled</span>';
				break;
			default:
				$html = '<span class="badge bg-info text-dark">Offered</span>';
				$html .= '<br><small>' . esc_html( self::get_images_status_label( $row->images_status ) ) . '</small>';
		}

		return $html;
	}

	public static function customer_show_title_upload( $row ) {
		$row = self::normalize_row( $row );

		if ( (int) $row->status !== self::OFFERED || self::customer_waiting_review( $row ) || self::can_accept_offer( $row ) ) {
			return false;
		}

		if ( $row->images_status === self::IMAGES_REJECTED ) {
			return $row->title_review === self::REVIEW_REJECTED;
		}

		return true;
	}

	public static function customer_show_car_upload( $row ) {
		$row = self::normalize_row( $row );

		if ( (int) $row->status !== self::OFFERED || self::customer_waiting_review( $row ) || self::can_accept_offer( $row ) ) {
			return false;
		}

		if ( $row->images_status === self::IMAGES_REJECTED ) {
			return $row->car_review === self::REVIEW_REJECTED;
		}

		return true;
	}

	public static function get_images_status_label( $images_status ) {
		switch ( $images_status ) {
			case self::IMAGES_PENDING:
				return 'Images pending review';
			case self::IMAGES_APPROVED:
				return 'Images approved';
			case self::IMAGES_REJECTED:
				return 'Images rejected - re-upload required';
			default:
				return 'Awaiting customer images';
		}
	}
}
