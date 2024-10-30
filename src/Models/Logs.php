<?php

namespace MailTree\Models;

use MailTree\GeneralHelper;

class Logs {

	public static function getTotalPages( $postsPerPage = false) {
		if (false == $postsPerPage) {
			/**  This overwrites the WP screen options initially to always show 5 entries. Saving the WP screen options from WP backend overwrites the set 5 */
			$postsPerPage = GeneralHelper::$logsPerPage;
		}


		return ceil(self::getTotalAmount() / $postsPerPage);
	}

	public static function getFirst( $args = []) {
		/** This gets called in Logger.php and serves as argument for: mailtree_mail_success is triggered when a message is sent and logged successfully. It has a single argument that is an array containing the log */
		$result = self::get($args);
		return isset($result[0]) ? $result[0] : false;
	}

	/**
	 * The Cache Getter
	 *
	 * @param array $args
	 * @return array|null|object
	 */
	public static function get( $args = []) {
		global $wpdb;

		if (!isset($args['ignore_cache']) || false == $args['ignore_cache']) {
			$cachedValue = Cache::get($args);

			if (null != $cachedValue) {
				return $cachedValue;
			}
		}

		/**
		 * Set default arguments and combine with
		 * those passed in get/post and passed directly
		 * to the function
		 */
		$defaults = [
			'orderby' => 'time',
			'posts_per_page' => GeneralHelper::$logsPerPage,
			'paged' => 1,
			'order' => 'DESC',
			'date_time_format' => 'human',
			'post_status' => 'any',
			'post__in' => [],
			'subject' => null,
			's' => null
		];

		$args = array_merge($defaults, $args);

		/**
		 * Sanitise each value in the array
		 */
		array_walk_recursive($args, 'mailtree\GeneralHelper::sanitiseForQuery');

		$sql = 'SELECT id, time, email_to, subject, message,
            status, error, backtrace_segment, attachments,
            additional_headers
            FROM ' . $wpdb->prefix . GeneralHelper::$tableName . ' ';

		$whereClause = false;

		if (!empty($args['post__in'])) {
			$whereClause = true;
			$sql .= 'WHERE id IN(' . GeneralHelper::arrayToString($args['post__in']) . ') ';
		}

		if (null != $args['subject'] && null == $args['s']) {
			$args['s'] = $args['subject'];
		}

		if (null != $args['s']) {
			if (true == $whereClause) {
				$sql .= 'AND ';
			} else {
				$sql .= 'WHERE ';
				$whereClause = true;
			}

			$sql .= "(subject LIKE '%" . $args['s'] . "%') OR ";
			$sql .= "(message LIKE '%" . $args['s'] . "%') OR ";
			$sql .= "(email_to LIKE '%" . $args['s'] . "%') OR ";
			$sql .= "(attachments LIKE '%" . $args['s'] . "%') OR ";
			$sql .= "(additional_headers LIKE '%" . $args['s'] . "%') ";
		}

		if ('any' != $args['post_status']) {
			if (true == $whereClause) {
				$sql .= 'AND ';
			} else {
				$sql .= 'WHERE ';
				$whereClause = true;
			}

			switch ($args['post_status']) {
				case ( 'successful' ):
					$sql .= 'status = 1 ';
					break;
				case ( 'failed' ):
					$sql .= 'status = 0 ';
					break;
			}
		}

		$sql .= 'ORDER BY ' . $args['orderby'] . ' ' . $args['order'] . ' ';

		if (-1 != $args['posts_per_page']) {
			$sql .= 'LIMIT ' . $args['posts_per_page'] . '
               OFFSET ' . ( $args['posts_per_page'] * ( $args['paged'] - 1 ) );
		}

		$results = self::dbResultTransform($wpdb->get_results($sql, ARRAY_A), $args);

		// NOTE Cache results.
		if (!isset($args['ignore_cache']) || false == $args['ignore_cache']) {
			Cache::set($args, $results);
		}
		return $results;
	}

	private static function dbResultTransform( $results, $args = []) {
		foreach ($results as &$result) {
			$result['status'] = (bool) $result['status'];
			$result['attachments'] = json_decode($result['attachments'], true);
			$result['additional_headers'] = json_decode($result['additional_headers'], true);
			$result['attachment_file_paths'] = [];

			if (is_string($result['additional_headers'])) {
				$result['additional_headers'] = explode(PHP_EOL, $result['additional_headers']);
			}

			$result['timestamp'] = $result['time'];
			$result['time'] = 'human' == $args['date_time_format'] ? GeneralHelper::getHumanReadableTimeFromNow($result['timestamp']) : gmdate($args['date_time_format'], $result['timestamp']);
			$result['is_html'] = GeneralHelper::doesArrayContainSubString($result['additional_headers'], 'text/html');
			$result['message'] = stripslashes(htmlspecialchars_decode($result['message']));

			if (!empty($result['attachments'])) {
				foreach ($result['attachments'] as &$attachment) {
					if (-1 == $attachment['id']) {
						$attachment['note'] = GeneralHelper::$attachmentNotInMediaLib;
						continue;
					}

					$attachment['src'] = GeneralHelper::$attachmentNotImageThumbnail;
					$attachment['url'] = wp_get_attachment_url($attachment['id']);
					$result['attachment_file_paths'][] = get_attached_file($attachment['id']);

					$isImage = strpos(get_post_mime_type($attachment['id']), 'image') !== false ? true : false;

					if (true == $isImage) {
						$attachment['src'] = $attachment['url'];
					}
				}
			}
		}
		return $results;
	}

	public static function getTotalAmount() {
		global $wpdb;

		return $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'mailtree_logs');
	}

	public static function delete( $ids) {
		global $wpdb;

		if ( is_array( $ids ) && 'db' == end($ids) ) {
				array_pop($ids);
		}

		$ids = GeneralHelper::arrayToString($ids);
		$ids = GeneralHelper::sanitiseForQuery($ids);

		$wpdb->query($wpdb->prepare(
					'DELETE FROM '
					. $wpdb->prefix . 'mailtree_logs
                      WHERE id IN (%1s)', $ids));
	}

	public static function truncate() {
		global $wpdb;

		$wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'mailtree_logs');
	}
}
