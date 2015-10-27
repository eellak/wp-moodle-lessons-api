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

function moocview_widget_init(){
     register_widget( 'Widget_MoodleCoursesView' );
}

function load_moocview_textdomain()
{
    load_plugin_textdomain('moodle-courses-view', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
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
	$all = json_decode($response['body']);
	$courses = array();
	foreach($all as $c)
	{
		if($c->categoryid>0)
			$courses[] = $c;
	}
	return $courses;
}

function get_moodle_course_content($moodle_site, $moodle_token, $course_id)
{
	$response = wp_remote_get($moodle_site.'?wstoken='.$moodle_token.'&wsfunction=core_course_get_contents&courseid='.$course_id.'&moodlewsrestformat=json');
	$content = json_decode($response['body']);
	$summary = "";
	if(isset($content[0]) && isset($content[0]->summary))
	{
		$summary = parse_images($content[0]->summary, 'course', $course_id);
	}
	return $summary;
}

function get_moodle_course_teachers($moodle_site, $moodle_token, $course_id)
{
	$args = array();
	$coursecapabilities = array();
	$capbs = array("mod/lesson:grade","mod/feedback:receivemail");
	$coursecapabilities[] = array('courseid'=> $course_id, 'capabilities' => $capbs);
	$args['body'] = array('wsfunction' =>'core_enrol_get_enrolled_users_with_capability', 'wstoken' => "$moodle_token", 'moodlewsrestformat' => 'json', 'coursecapabilities'=>$coursecapabilities);//array("mod/forum:viewdiscussion"));
	$response = wp_remote_post($moodle_site, $args);
	$respbody = json_decode($response['body']);
	$course_teachers = array();
	if(is_array($respbody) && isset($respbody[0]->users) && !empty($respbody[0]->users))
	{
		foreach($respbody[0]->users as $u)
		{
			$course_teachers[] = array('id'=>$u->id, 'name'=>$u->fullname, 'img'=>$u->profileimageurlsmall);
		}
	}
	return $course_teachers;
}

function get_moodle_categories_and_courses($moodle_site, $moodle_token, $catid = null)
{
	$cats = get_moodle_categories($moodle_site, $moodle_token);
	$courses = get_moodle_courses($moodle_site, $moodle_token);
	
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
		}
	}
	return $catsarr;
}

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
	$courses = get_moodle_categories_and_courses($moodle_ws_url, $moodle_site_token, $catid);
	
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
			$summary = empty($c->summary)? get_moodle_course_content($moodle_ws_url, $moodle_site_token, $c->id):parse_images($c->summary, 'course', $c->id);
			$out .= empty($summary)?  '<span class="emptysummary">'.__('No summary added to this course.','moodle-courses-view').'</span>':"<p>$summary</p>";
			$out .= '</div><div style="clear:both;"></div>';
			$teachers = get_moodle_course_teachers($moodle_ws_url, $moodle_site_token, $c->id);
			$out_table .= '<td>';
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

function moocview_scripts() 
{
	wp_enqueue_style( 'datatables-css', '//cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.css');
	wp_register_script( 'datatables', '//cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.js', array('jquery'));
	wp_enqueue_script( 'datatables');
	wp_register_script( 'moocview-page',plugins_url( 'js/moocview_page.js', __FILE__ ),array('jquery'));
	wp_enqueue_script( 'moocview-page');

}


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

function get_remote_image($imgurl, $context_prefix, $context_id, $force_get = true)
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
	/***moodle bug in paths of images in course summaries***/
	$imgurl = str_replace('/summary/0/','/summary/',$imgurl);	
	if($force_get || !is_file($localname))
	{
		$wpimg = wp_remote_get($imgurl."?token=$moodle_site_token");
		file_put_contents($localname, $wpimg['body']);
	}
	return $newimgurl;
}
?>