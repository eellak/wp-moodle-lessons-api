<?php
/**
 * @package Moodle Courses View * @version 0.1
 */
/*
Plugin Name: Moodle Courses View
Plugin URI: 
Description: A plugin to display the list of courses and course categories of a moodle installation.
Author: Angela Dimitriou
Version: 1.0
Author URI: http://www.dbnet.ntua.gr/~angela/
*/
$filepath = realpath (dirname(__FILE__));
require_once($filepath.'/lib.php');
require_once($filepath.'/admin.php');
require_once($filepath.'/widget.php');

if( is_admin() )
    $moocview_settings_page = new MooCViewSettingsPage();

add_action( 'widgets_init', 'moocview_widget_init');

//update_option('moocview_siteurl','http://moodle.test.noc.ntua.gr');
//update_option('moocview_sitewspath','/webservice/rest/server.php');
//update_option('moocview_sitetoken','8305f771642d61d11c526bc605dc6027');
?>
