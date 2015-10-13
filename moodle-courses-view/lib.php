<?php
function moocview_widget_init(){
     register_widget( 'Widget_MoodleCoursesView' );
}

function get_moodle_categories($moodle_site, $moodle_token)
{
	$response = wp_remote_get($moodle_site.'?wstoken='.$moodle_token.'&wsfunction=core_course_get_categories&moodlewsrestformat=json');
	$categories= json_decode($response['body']);
	return $categories;
}

function get_moodle_courses($moodle_site, $moodle_token)
{
	$response = wp_remote_get($moodle_site.'?wstoken='.$moodle_token.'&wsfunction=core_course_get_courses&moodlewsrestformat=json');
	$courses = json_decode($response['body']);
	return $courses;
}

function get_moodle_course_content($moodle_site, $moodle_token, $course_id)
{
	$response = wp_remote_get($moodle_site.'?wstoken='.$moodle_token.'&wsfunction=core_course_get_content&courseid='.$course_id.'&moodlewsrestformat=json');
	$content = json_decode($response['body']);
	return $content;
}

function get_moodle_categories_and_courses($moodle_site, $moodle_token)
{
	$cats = get_moodle_categories($moodle_site, $moodle_token);
	
	$courses = get_moodle_courses($moodle_site, $moodle_token);
	foreach($cats as $ca)
	{
		$catsarr[$ca->id]['category'] = $ca;
		$catsarr[$ca->id]['courses'] = array();
	}
	foreach($courses as $co)
	{
		$catsarr[$co->categoryid]['courses'][] = $co;
		if(!key_exists('category',$catsarr[$co->categoryid]))
		{
			$catsarr[$co->categoryid]['category'] = (object) array('id' => $co->categoryid, 'name' => 'Not categorized', 'coursecount' => 1);
		}
	}
	return $catsarr;
}

?>
