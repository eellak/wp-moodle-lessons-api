<?php
require_once('lib.php');
wp_register_script( 'moocview-widget',plugins_url( 'js/moocview_widget.js', __FILE__ ),array('jquery'));
wp_enqueue_script( 'moocview-widget');

class Widget_MoodleCoursesView extends WP_Widget
{
	private $moodle_site;
	private $moocview_site_ws_path;
	private $moodle_site_token;
	private $moodle_ws_url;
	private $moocview_category_view_path;
	private $moocview_category_id_par; 
	private $moocview_course_view_path;
	private $moocview_course_id_par; 
	private $moocview_user_view_path;
	private $moocview_user_id_par; 
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// widget actual processes
		parent::__construct("","Moodle Courses View");
		$this->moodle_site = get_option('moocview_siteurl');
		$this->moocview_site_ws_path = get_option('moocview_sitewspath'); 
		$this->moodle_site_token = get_option('moocview_sitetoken');
		$this->moodle_ws_url = $this->moodle_site.$this->moocview_site_ws_path;
		$this->moocview_category_view_path = get_option('moocview_category_view_path');
		$this->moocview_category_id_par = get_option('moocview_category_id_par'); 
		$this->moocview_course_view_path = get_option('moocview_course_view_path');
		$this->moocview_course_id_par = get_option('moocview_course_id_par'); 
		$this->moocview_user_view_path = get_option('moocview_user_view_path');
		$this->moocview_user_id_par = get_option('moocview_user_id_par'); 
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		//Get args and output the title
        wp_register_style( 'moocview-widget-css',plugins_url( 'css/moocview-widget.css', __FILE__ ));
		wp_enqueue_style( 'moocview-widget-css');

		extract( $args );
        echo $before_widget;
        $title = apply_filters('widget_title', $instance['title']);
        if( $title ) echo $before_title . $title . $after_title;
        
        if($instance['contenttype'] == '0')
        {
        	$catlist = get_moodle_categories($this->moodle_ws_url, $this->moodle_site_token);
        	foreach($catlist as $category)
			{
				$langCourses = ($category->coursecount == 1)? __('course','moodle-courses-view'):__('courses','moodle-courses-view');
				echo "<div class='category'><a href='{$this->moodle_site}{$this->moocview_category_view_path}?{$this->moocview_category_id_par}={$category->id}'>{$category->name}</a> <br/>({$category->coursecount} $langCourses)</div>";
			}
        }
        elseif($instance['contenttype'] == '2')
        {
			$catcourses = get_moodle_categories_and_courses($this->moodle_ws_url, $this->moodle_site_token);
			foreach($catcourses as $cc)
			{
				$category = $cc['category'];
				echo "<div class='catcourses'>";
				$langCourses = ($category->coursecount == 1)? __('course','moodle-courses-view'):__('courses','moodle-courses-view');
				echo "<div class='cattitle'><a href='{$this->moodle_site}{$this->moocview_category_view_path}?{$this->moocview_category_id_par}={$category->id}'>{$category->name}</a> <br/>({$category->coursecount} $langCourses)</div>";
				if($category->coursecount>0)
				{
					echo "<ul class='courselist'>";
				}
				$i = 0;
				foreach($cc['courses'] as $c)
				{
					$i++;
					if($i <= $instance['maxcatcourses'] || ($instance['maxcatcourses'] + 1) == $category->coursecount)
					{
						echo "<li><a href='{$this->moodle_site}{$this->moocview_course_view_path}?{$this->moocview_course_id_par}={$c->id}'>{$c->fullname}</li>";			
					}
					else
					{	
						echo "<li class='morelink'><a href='{$instance['morelinkurl']}?{$instance['categoryparam']}={$category->id}'>".__('More ...','moodle-courses-view')."</li>";
						break;
					}
				}
				if($category->coursecount>0)
				{
					echo "</ul>";
				}
				echo "</div>";
			}
		}
        else
        {
        	$courses = get_moodle_courses($this->moodle_ws_url, $this->moodle_site_token);
        	$i = 0;
			echo "<div class='catcourses'>";
			echo "<ul class='maincourselist'>";
			foreach($courses as $c)
			{
				$i++;
				if($i <= $instance['maxcatcourses'])
				{
					echo "<li><a href='{$this->moodle_site}{$this->moocview_course_view_path}?{$this->moocview_course_id_par}={$c->id}'>{$c->fullname}</a></li>";			
				}
				else
				{	
					echo "<li class='morelink'><a href='{$instance['morelinkurl']}'>".__('More ...','moodle-courses-view')."</li>";
					break;
				}
			}
			echo "</ul></div>";
        }
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		echo '<script type="text/javascript">
			var contentTypeLbl = new Array();
			contentTypeLbl.push("");  
			contentTypeLbl.push("'.__('Maximum courses','moodle-courses-view').'"); 
			contentTypeLbl.push("'.__('Maximum courses','moodle-courses-view').'"+" "+"'.__('per category','moodle-courses-view').'");
		</script>';

		echo '<p>';
		echo '<label for="'.$this->get_field_id('title').'" style="margin-right:2px;">'.__('Title','moodle-courses-view').'</label>';
        echo '<input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text"';
        $v = isset($instance['title'])?$instance['title']:"";
        echo 'value="'.$v.'" />';
        echo "<p/>";
        
		echo '<p><label for="'.$this->get_field_id('contenttype').'" style="margin-right:2px;">'.__('Type','moodle-courses-view').'</label>';
        $selected = array("","","");
        $selected[$instance['contenttype']] = "selected";
        echo '<select class="moocview-contenttype-class" id="'.$this->get_field_id('contenttype').'" name="'.$this->get_field_name('contenttype').'"><option value="0" '.$selected[0].'>'.__('categories','moodle-courses-view').'</option><option value="1" '.$selected[1].'>'.__('courses','moodle-courses-view').'</option><option value="2" '.$selected[2].'>'.__('both','moodle-courses-view').'</option></select>';
        echo "<p/>";
		
		$display_style = ($instance['contenttype'] == 0)? "style='display:none;'":""; 
		echo "<p class='moocview-pmaxcatcourses-class' $display_style>";
		$maxcourses_lbl = __('Maximum courses','moodle-courses-view').' ';
		$maxcourses_lbl .= ($instance['contenttype'] == 2)? __('per category','moodle-courses-view'):'';
        echo '<label for="'.$this->get_field_id('maxcatcourses').'">'.$maxcourses_lbl.'</label>';
        echo '<input class="widefat" id="'.$this->get_field_id('maxcatcourses').'" name="'.$this->get_field_name('maxcatcourses').'" type="text"';
        $maxcourses = isset($instance['maxcatcourses'])?$instance['maxcatcourses']:"5";
        echo 'value="'.$maxcourses.'" />';
		echo '</p>';
		
		$display_style = ($instance['contenttype'] == 0)? "style='display:none;'":""; 
		echo "<p class='moocview-pmorelink-class' $display_style>";
		echo '<label for="'.$this->get_field_id('morelink').'">'.__('Link for detailed course list','moodle-courses-view').'</label><br/>';
        echo '<select class="moocview-morelink-class" id="'.$this->get_field_id('morelink').'" name="'.$this->get_field_name('morelink').'">';
		$pages = get_pages(); 
		foreach ( $pages as $page ) 
		{
			if(has_shortcode($page->post_content, 'moodle_detailed_courses'))
			{
				$selected = ($instance['morelink'] == get_page_link( $page->ID ))? 'selected':'';
				$option = '<option value="' . get_page_link( $page->ID ) . '" '.$selected.'>';
				$option .= $page->post_title;
				$option .= '</option>';
				echo $option;
			}
		}
		$selected = ($instance['morelink'] ==  $this->moodle_site)? 'selected':'';
		echo '<option value="' . $this->moodle_site . '" '.$selected.'>'.$this->moodle_site.'</option>';
		$selected = ($instance['morelink'] ==  'other')? 'selected':'';
		echo '<option value="' ."other". '" '.$selected.'>'.__('Other...','moodle-courses-view').'</option>';
		echo '</select></p>';
		
		$display_style = ($instance['contenttype'] == 0 || $instance['morelink'] != 'other')? "style='display:none;'":""; 
		echo "<p class='moocview-pmorelinkurl-class' $display_style>";
		echo '<input class="widefat" id="'.$this->get_field_id('morelinkurl').'" name="'.$this->get_field_name('morelinkurl').'" type="text"';
        $v = isset($instance['morelinkurl'])? $instance['morelinkurl']:"";
        echo 'value="'.$v.'" placeholder="http://"/>';
        echo "</p>";
		
		$display_style = ($instance['contenttype'] == 2 && $instance['morelink'] == 'other')? "":"style='display:none;'"; 
		echo "<p class='moocview-categoryparam-class' $display_style>";
		echo '<label for="'.$this->get_field_id('categoryparam').'">'.__('Course category parameter','moodle-courses-view').'</label><br/>';
        echo '<input class="widefat" id="'.$this->get_field_id('categoryparam').'" name="'.$this->get_field_name('categoryparam').'" type="text"';
        $v = (isset($instance['categoryparam']) && $instance['morelink'] == 'other' && !empty($instance['morelinkurl']) )? $instance['categoryparam']:"";
        echo 'value="'.$v.'" placeholder="catid"/>';
        echo "</p>";
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['contenttype'] = (abs(intval($new_instance['contenttype']))>2)? "0":abs(intval($new_instance['contenttype']));
        $instance['maxcatcourses'] = (abs(intval($new_instance['maxcatcourses'])) == 0)? "1":abs(intval($new_instance['maxcatcourses']));
		$instance['morelink'] = sanitize_text_field($new_instance['morelink']);
		$instance['morelinkurl'] = esc_url($new_instance['morelinkurl']);
		$instance['categoryparam'] = sanitize_text_field($new_instance['categoryparam']);
		if($new_instance['morelink'] == $this->moodle_site)
		{
			$instance['morelinkurl'] = $this->moodle_site.'/course/index.php';
			$instance['categoryparam'] = 'categoryid';
		}
		elseif($new_instance['morelink'] != 'other')
		{
			$instance['categoryparam'] = 'categoryid';
		}
		elseif($instance['contenttype'] == 2 && $instance['morelink'] == 'other' && empty($instance['morelinkurl']))
		{
			$instance['morelink'] = $this->moodle_site;
			$instance['morelinkurl'] = $this->moodle_site.'/course/index.php';
			$instance['categoryparam'] = 'categoryid';
		}
        return $instance;
	}
}
?>