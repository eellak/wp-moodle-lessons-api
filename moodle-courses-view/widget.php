<?php
require_once('lib.php');
class Widget_MoodleCoursesView extends WP_Widget
{
	private $moodle_site;
	private $moocview_site_ws_path;
	private $moodle_site_token;
	private $moodle_ws_url;
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
        extract( $args );
        echo $before_widget;
        $title = apply_filters('widget_title', $instance['title']);
        if( $title ) echo $before_title . $title . $after_title;
        
        if($instance['contenttype'] == '0')
        {
        	$catlist = get_moodle_categories($this->moodle_ws_url, $this->moodle_site_token);
        	foreach($catlist as $category)
			{
				echo "<a href='{$this->moodle_site}/course/index.php?categoryid={$category->id}'>{$category->name} ({$category->coursecount} courses)</a><br/>";
			}
        }
        elseif($instance['contenttype'] == '2')
        {
			$catcourses = get_moodle_categories_and_courses($this->moodle_ws_url, $this->moodle_site_token);
			foreach($catcourses as $cc)
			{
				$category = $cc['category'];
				echo "<a href='{$this->moodle_site}/course/index.php?categoryid={$category->id}'>{$category->name} ({$category->coursecount} courses)</a><br/>";
				foreach($cc['courses'] as $c)
				{
					echo $c->fullname."<br/>";			
				}
			}
        }
        else
        {
        	$courses = get_moodle_courses($this->moodle_ws_url, $this->moodle_site_token);
        	foreach($courses as $c)
			{
				echo "<a href='{$this->moodle_site}/course/view.php?id={$c->id}'>{$c->fullname}</a><br/>";
				echo "<br/>";			
			}
        }

	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		echo '<p>';
		echo '<label for="'.$this->get_field_id('title').'">'._e('Title').'</label>';
        echo '<input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text"';
        $t = isset($instance['title'])?$instance['title']:"";
        echo 'value="'.$t.'" />';
        echo "<p/><p>";
        echo '<label for="'.$this->get_field_id('contenttype').'">'._e('Type').'</label>';
        $selected = array("","","");
        $selected[$instance['contenttype']] = "selected";
        echo '<select id="'.$this->get_field_id('contenttype').'" name="'.$this->get_field_name('contenttype').'"><option value="0" '.$selected[0].'>Categories</option><option value="1" '.$selected[1].'>Courses</option><option value="2" '.$selected[2].'>Both</option></select>';
        echo '</p>';
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
        $instance['title'] = $new_instance['title'];
        $instance['contenttype'] = $new_instance['contenttype'];
        return $instance;
	}
}
?>
