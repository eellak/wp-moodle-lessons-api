<?php
/**
 * @package Moodle Courses View * @version 0.1
 */
/*
Plugin Name: Moodle Courses View
Plugin URI: 
Description: A plugin to display the list of courses and course categories of a moodle installation.
Text Domain: moodle-courses-view
Author: Angela Dimitriou
Version: 1.1
Author URI: http://www.dbnet.ntua.gr/~angela/
*/
$filepath = realpath (dirname(__FILE__));
require_once($filepath.'/lib.php');
require_once($filepath.'/admin.php');
require_once($filepath.'/widget.php');
if( is_admin() )
    $moocview_settings_page = new MooCViewSettingsPage();
//add_action('init', 'load_moocview_textdomain');
add_action('plugins_loaded','load_moocview_textdomain');
//load_moocview_textdomain();
add_action( 'wp_enqueue_scripts', 'moocview_scripts' );
add_action( 'widgets_init', 'moocview_widget_init');
add_shortcode('moodle_detailed_courses','detailed_course_list');
?>