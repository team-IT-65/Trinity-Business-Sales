<?php

namespace Wptool\adminDash\services;

use Wptool\adminDash\exceptions\AdminDashException;
use Wptool\adminDash\exceptions\CourseRequestFailedException;
use Wptool\adminDash\utils\Configuration;

class CourseService {

	const COURSE_PROGRESS_OPTION_NAME = 'wp_admin_dash_course_progress';

	/**
	 * Updates course progress with passed video_id.
	 *
	 * @param string $video_id
	 * @return array
	 */
	public function update_course_progress( $video_id ) {
		$progress = $this->get_course_progress();

		// don't update if already watched
		if ( array_search( $video_id, $progress, true ) !== false ) {
			return $progress;
		}

		$progress[] = $video_id;

		if ( ! update_option( self::COURSE_PROGRESS_OPTION_NAME, $progress ) ) {
			throw new CourseRequestFailedException();
		}

		return $progress;
	}

	public function get_course_progress() {
		return get_option( self::COURSE_PROGRESS_OPTION_NAME, array() );
	}
}
