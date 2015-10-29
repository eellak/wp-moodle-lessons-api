<?php
$moodle_site = get_option('moocview_siteurl');
$moocview_site_ws_path = get_option('moocview_sitewspath'); 
$moodle_site_token = get_option('moocview_sitetoken');
$moodle_ws_url = $moodle_site.$moocview_site_ws_path;

$moocview_category_view_path = get_option('moocview_category_view_path');
$moocview_category_id_par = get_option('moocview_category_id_par'); 
$moocview_course_view_path = get_option('moocview_course_view_path');
$moocview_course_id_par = get_option('moocview_course_id_par'); 
$moocview_user_view_path = get_option('moocview_user_view_path');
$moocview_user_id_par = get_option('moocview_user_id_par'); 

$moocview_shortcode_already_run = false;
$moocview_shortcode_output = "";

/**
 * Initialize MooCView widget
 * @return void 
*/
function moocview_widget_init(){
     register_widget( 'Widget_MoodleCoursesView' );
}

/**
 * Localize MooCView plugin void
*/
function load_moocview_textdomain()
{
    load_plugin_textdomain('moodle-courses-view', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}

/**
 * Get course categories from moodle site. 
 * @param string $moodle_site the moodle site from where to get course categories
 * @param string $moodle_token the token of the moodle web service user 
 * @return array course categories 
*/
function get_moodle_categories($moodle_site, $moodle_token)
{
	$response = wp_remote_get($moodle_site.'?wstoken='.$moodle_token.'&wsfunction=core_course_get_categories&moodlewsrestformat=json');
	$categories= json_decode($response['body']);
	return $categories;
}

/**
 * Get course categories from moodle site. 
 * @param string $moodle_site the moodle site from where to get course categories
 * @param string $moodle_token the token of the moodle web service user 
 * @return array course categories 
*/
function get_moodle_courses($moodle_site, $moodle_token)
{
	$response = wp_remote_get($moodle_site.'?wstoken='.$moodle_token.'&wsfunction=core_course_get_courses&moodlewsrestformat=json');
	$all = json_decode($response['body']);
	$courses = array();
	foreach($all as $c)
	{
		if($c->categoryid>0)
			$courses[] = $c;
	}
	return $courses;
}

/**
 * Get course details in the form of summary from moodle site. 
 * @param string $moodle_site the moodle site from where to get course categories
 * @param string $moodle_token the token of the moodle web service user 
 * @param integer $course_id the moodle course id for which to retrieve details 
 * @return string course description 
*/
function get_moodle_course_content($moodle_site, $moodle_token, $course_id)
{
	$args = array();
	$options = array(array('name'=>'sectionnumber', 'value'=>0),array('name'=>'excludemodules','value'=>true));
	$args['body'] = array('wsfunction' =>'core_course_get_contents', 'wstoken' => "$moodle_token", 'moodlewsrestformat' => 'json', 'courseid'=>$course_id, 'options'=>$options);
	$response = wp_remote_post($moodle_site, $args);
	
	
	$content = json_decode($response['body']);
	er($content);
	$summary = "";
	if(isset($content[0]) && isset($content[0]->summary))
	{
		$summary = parse_images($content[0]->summary, 'course', $course_id);
	}
	return $summary;
}

/**
 * Get course teachers from moodle site. 
 * @param string $moodle_site the moodle site from where to get course categories
 * @param string $moodle_token the token of the moodle web service user 
 * @param array $course_ids array containing moodle course ids of courses to retrieve teachers from 
 * @return array for each course the array contains an array of ('id', 'name', 'img') records per teacher
*/
function get_moodle_course_teachers($moodle_site, $moodle_token, $course_ids)
{
	$args = array();
	$coursecapabilities = array();
	$capbs = array("mod/lesson:grade","mod/feedback:receivemail");
	foreach($course_ids as $cid)
	{
		$coursecapabilities[] = array('courseid'=> $cid, 'capabilities' => $capbs);
	}
	$options = array(array('name'=>'userfields', 'value'=>'id, fullname, profileimageurl, profileimageurlsmall'));
	$args['body'] = array('wsfunction' =>'core_enrol_get_enrolled_users_with_capability', 'wstoken' => "$moodle_token", 'moodlewsrestformat' => 'json', 'coursecapabilities'=>$coursecapabilities, 'options'=>$options);//array("mod/forum:viewdiscussion"));
	$response = wp_remote_post($moodle_site, $args);
	$respbody = json_decode($response['body']);
	$course_teachers = array();
	if(is_array($respbody))
	foreach($respbody as $k => $v)
	{
		if(isset($v->users) && !empty($v->users))
		{
			if(!isset($course_teachers[$v->courseid]))
			{
				foreach($v->users as $u)
				{
					$course_teachers[$v->courseid][$u->id] = array('id'=>$u->id, 'name'=>$u->fullname, 'img'=>$u->profileimageurlsmall, 'capabilities'=>1);
				}
			}
			else
			{
				foreach($v->users as $u)
				{
					$course_teachers[$v->courseid][$u->id]['capabilities']++;
				}
			}
		}
	}
	foreach($course_teachers as $c => $us)
	{
		foreach($us as $uid => $u)
		{
			if($u['capabilities'] < count($capbs))
				unset($us[$uid]);	
		}
	}
	return $course_teachers;
}

/**
 * Get categories with their courses from moodle site. 
 * @param string $moodle_site the moodle site from where to get course categories
 * @param string $moodle_token the token of the moodle web service user 
 * @param integer $catid the moodle category id 
 * @return array an array ('courses', 'cids') containing in 'courses' for each category the corresponding courses and in 'cids' the list af all courses ids 
*/
function get_moodle_categories_and_courses($moodle_site, $moodle_token, $catid = null)
{
	$cats = get_moodle_categories($moodle_site, $moodle_token);
	$courses = get_moodle_courses($moodle_site, $moodle_token);
	$courseids = array();
	foreach($cats as $ca)
	{
		if(is_null($catid) || $ca->id == $catid)
		{
			$catsarr[$ca->id]['category'] = $ca;
			$catsarr[$ca->id]['courses'] = array();
		}
	}
	foreach($courses as $co)
	{
		if(key_exists('category',$catsarr[$co->categoryid]))
		{
			$catsarr[$co->categoryid]['courses'][] = $co;
			$courseids[] = $co->id;
		}
	}
	return array('courses' => $catsarr, 'cids' => $courseids);
}

/**
 * Wrapper function for actual shortcode function. Some plugins call do_shortcode in the wp_head 
 * causing shortcodes being executed twice. This function prevents shortcode from being executed multiple times.
 * @param array $atts the shortcode parameters
 * @return string the output of the shortcode
*/
function detailed_course_list_shcode($atts)
{
	global $moocview_shortcode_already_run, $moocview_shortcode_output;
	if(!$moocview_shortcode_already_run)
	{
		$moocview_shortcode_already_run = true;
		$moocview_shortcode_output = detailed_course_list($atts);
	}
	return $moocview_shortcode_output;
}

/**
 * Shortcode function of MooCView plugin. Returns a list of categories, courses and their teachers and descriptions. 
 * Depending on the viewtype parameter of the shortcode, the data is returmed formatted as a list according to css/moocview-page.css 
 * or as a datatable capable of being searched and sorted.
 * @param array $atts the shortcode parameters
 * @return string the output of the shortcode
*/
function detailed_course_list($atts)
{
	global $moodle_ws_url, $moodle_site_token, $moodle_site, $moocview_category_view_path, $moocview_category_id_par, $moocview_course_view_path, $moocview_course_id_par, $moocview_user_view_path, $moocview_user_id_par;
	add_action( 'wp_enqueue_scripts', 'moocview_scripts' );
	wp_register_style( 'moocview-page-css',plugins_url( 'css/moocview-page.css', __FILE__ ));
	wp_enqueue_style( 'moocview-page-css');
	
	$pars = shortcode_atts( array('viewtype' => 'list'), $atts);
	$viewtype = $pars['viewtype'];
	
	$catid = isset($_GET['categoryid'])?  intval($_GET['categoryid']):null;
	$out = "";
	$out_table = "<table id='detailed_courselist'>";
	$out_table .= '<thead><th>'.__('Category','moodle-courses-view').'</th><th>'.__('Course','moodle-courses-view').'</th><th>'.__('Teacher(s)','moodle-courses-view').'</th></thead><tbody>';
	$coursesinfo = get_moodle_categories_and_courses($moodle_ws_url, $moodle_site_token, $catid);
	$course_teachers = get_moodle_course_teachers($moodle_ws_url, $moodle_site_token, $coursesinfo['cids']);
	
	$courses = $coursesinfo['courses'];
	foreach($courses as $catid => $catcourse)
	{
		$out .= '<div class="categorycourses">';
		$out .= '<div class="categorytitle">';
		$langCourses = ($catcourse['category']->coursecount == 1)? __('course','moodle-courses-view'):__('courses','moodle-courses-view');
		$out .= "<a style='font-weight:bold;' href='{$moodle_site}{$moocview_category_view_path}?{$moocview_category_id_par}={$catcourse['category']->id}'>{$catcourse['category']->name}</a> ({$catcourse['category']->coursecount} $langCourses)";
		$out .= "</div>";		
		foreach($catcourse['courses'] as $c)
		{
			$out .= '<div class="course">';
			$out .= '<div class="coursetitle">';
			$out_table .= '<tr>';
			$out .= "<a href='{$moodle_site}{$moocview_course_view_path}?{$moocview_course_id_par}={$c->id}'>{$c->fullname}</a>";
			$out_table .= '<td>'."<a style='font-weight:bold;' href='{$moodle_site}{$moocview_category_view_path}?{$moocview_category_id_par}={$catcourse['category']->id}'>{$catcourse['category']->name}</a>".'</td>';
			$out_table .= '<td>'."<a href='{$moodle_site}{$moocview_course_view_path}?{$moocview_course_id_par}={$c->id}'>{$c->fullname}</a>".'</td>';
			$out .= '</div>';
			$out .= "<div class='summary'>";
			//Remove expensive moodle web service call, which is performed per course
			//$summary = empty($c->summary)? get_moodle_course_content($moodle_ws_url, $moodle_site_token, $c->id):parse_images($c->summary, 'course', $c->id);
			$summary = parse_images($c->summary, 'course', $c->id);
			$out .= empty($summary)?  '<span class="emptysummary">'.__('No summary added to this course.','moodle-courses-view').'</span>':"<p>$summary</p>";
			$out .= '</div><div style="clear:both;"></div>';
			
			$out_table .= '<td>';
			$teachers = $course_teachers[$c->id];
			if(count($teachers)>0)
			{
				$lbl = (count($teachers) == 1)? __('Teacher','moodle-courses-view'):__('Teachers','moodle-courses-view');
				$out .= '<div class="teacherlist"> '.$lbl.':&nbsp;';
				$i = 0;
				foreach($teachers as $t)
				{
					if($i>0)
					{
						$out_table .= '</br>';
					}
					$profileimgurl = "";
					if(!empty($t['img']))
					{ 
						$profileimgurl = get_remote_image($t['img'],'user', $t['id']);
					}
					$profile_img = (empty($profileimgurl))? "":'<img src="'.$profileimgurl.'" title="'.__('profile image of','moodle-courses-view') .' '.$t['name'].'"/>';
					$out .= '<a href="'.$moocview_user_view_path.'?'.$moocview_user_id_par.'='.$t['id'].'"><span class="teacher">'.$profile_img.$t['name'].'</span></a>';
					$out_table .= '<a href="'.$moocview_user_view_path.'?'.$moocview_user_id_par.'='.$t['id'].'">'.$t['name'].'</a>';
				}
				$out .= '</div>';
			}
			$out_table .= '</td>';
			$out .= '</div>';
		}
		$out .= "</div>";
		$out_table .= '</tr>';
	}
	$out_table .= '</tbody></table>';
	if($viewtype == 'table')
		return $out_table;
	else
		return $out;
}

/**
 * Let wordpress load MooCView stylesheets and scripts 
 * @return void
*/
function moocview_scripts() 
{
	wp_enqueue_style( 'datatables-css', '//cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.css');
	wp_register_script( 'datatables', '//cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.js', array('jquery'));
	wp_enqueue_script( 'datatables');
	wp_register_script( 'moocview-page',plugins_url( 'js/moocview_page.js', __FILE__ ),array('jquery'));
	wp_enqueue_script( 'moocview-page');
}

/**
 * A function to parse text for <img> tags and get from moodle the corresponding images during server side processing. 
 * The function stores locally the images which only the web service user via the web service token 
 * has permission to download from the moodle site.
 * @param string $text the text to be parsed
 * @param string $context_prefix the type of resourse the images correspond to, typically 'course'|'user'
 * @param integer $context_id the moodle resource id, typically course id or user id, which the images correspond to
 * @return string the input text having the <img> 'src' attributes, pointing to the moodle site, replaced with local wordpress URLs
*/
function parse_images($text, $context_prefix, $context_id)
{
	preg_match_all("|<img[^>]+src=\"(.*)\".*>|U",$text,$images, PREG_SET_ORDER);
	foreach($images as $img)
	{
		$newimgurl = get_remote_image($img[1], $context_prefix, $context_id);
		$text = str_replace($img[1],$newimgurl,$text);
	}
	return $text;

}

/**
 * A function to retrieve images from the remote moodle site.
 * @param string $imgurl the img url in the moodle site
 * @param string $context_prefix the type of resourse the image corresponds to, typically 'course'|'user'
 * @param integer $context_id the moodle resource id, typically course id or user id, which the image corresponds to
 * @param boolean $force_get a parameter to force image retrieval, if it is already stored locally
 * @return string the new img url in the local wordpress site
*/
function get_remote_image($imgurl, $context_prefix, $context_id, $force_get = false)
{
	global $moodle_site_token;
	$upload_dir = wp_upload_dir(); 
	$moocview_upload_dir = trailingslashit( $upload_dir['basedir'] ) . 'moocview/'; 	
	if(!is_dir($moocview_upload_dir)) 
		mkdir($moocview_upload_dir, 0777);
	
	$imgname_parts = explode("/", $imgurl);
	$imgname = array_pop($imgname_parts);
	$newimgurl = $upload_dir['baseurl'].'/moocview/'.$context_prefix."_".$context_id."_".$imgname;
	
	$localname = $moocview_upload_dir.$context_prefix."_".$context_id."_".$imgname;
	/*** Moodle bug: extra /0/ subpath in image paths of course summaries***/
	$imgurl = str_replace('/summary/0/','/summary/',$imgurl);	
	if($force_get || !is_file($localname))
	{
		$wpimg = wp_remote_get($imgurl."?token=$moodle_site_token");
		file_put_contents($localname, $wpimg['body']);
	}
	return $newimgurl;
}
?>