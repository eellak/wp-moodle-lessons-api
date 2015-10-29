=== Moodle Courses View ===
Contributors: Angela Dimitriou
Tested to: 4.3
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to display in a wordpress site the list of courses and course categories of a moodle installation.

== Description ==

This plugin adds the capability of viewing courses and categories exracted from a moodle site. This functionality is offered both via a widget and a shortcode.

== Installation ==

= Steps to enable web services in a moodle installation =

1. Login as moodle site administrator and go to the administration page
2. Enable web services (normally from /admin/search.php?query=enablewebservices)
3. From plugins menu item select "Web services"
	---> Manage protocols
	---> Activate REST protocol
4. Create a new web service from Plugins -> Web services -> External services:
	a. Check the option "Can download files"
	b. Add the following functions to the new service:
		* core_course_get_contents 
		* core_course_get_courses 
		* core_course_get_categories 
		* core_enrol_get_enrolled_users_with_capability
	After the service creation, the rquired capabilities for accessing the service are listed. These are important for the permissions that need to be assigned to the user who will use the service.
	Normally they are the following:
	* webservice/rest:use 
	* moodle/course:update
	* moodle/course:viewhiddencourses
	* moodle/course:view
	* moodle/category:viewhiddencategories
	* moodle/course:viewparticipants
	* moodle/role:review
5. Create a user account with which the wordpress site will connect to the moodle site to retrieve data from the service
6. Define a new role. 
		Users -> Permissions -> Define role
	The above required capabilities must be set to "Allowed".
7. Assign the new role to the new user
		Users -> Permissions -> assign system roles
8. Assign newly created user to the service's "authorized users"
		Plugins -> Web services -> External services -> Authorised users of new service : add new user
	(!Notice any missing capabilities)
9. Create a new token for the web service user. 
		Plugins --> Manage services --> Manage tokens
	Copy the generated token for the plugin.

= Plugin installation =

Download and uncompress the Moodle Courses View plugin in the plugins directory of wordpress. Activate it and complete the options form in the settings page. The information required are:
 * the moodle site which hosts the web service
 * the service path (usually /webservice/rest/server.php) 
 * the token created for the web service user in the moodle site
 * the category view path of the moodle site
 * the category id parameter to be appended to the category view path of the moodle site
 * the course view path of the moodle site
 * the course id parameter to be appended to the category view path of the moodle site
 * the user view path of the moodle site
 * the user id parameter to be appended to the category view path of the moodle site

== Add moodle course list to the WP site ==

= Add widget = 

In any widget area add the Moodle Courses View widget
 * Define the title of the widget in the specified area
 * Select the list form among three choices:
   ** Show only course categories
   ** Show a list of courses without categories
   ** Show both categories with their courses
Except for the first case, a "More..." link will be displayed in the end of the list. For that link define:
	* the maximum number of courses to be displayed in total (second case) or per category (third case)
	* the URL where the "More..." link points to. Select one of:
		** pages in the current WP installation that contain the moodle courses list via the plugin's shortcode
		** the moodle site defined in the settings of the plugin in the admin saction
		** other...
	If "other..." is selected, define the other URL as well as the categoryid parameter that will be appended in the URL for redirecting to a specific category's view
	
= Add shortcode in a page or post =

The shortcode for the plugin is:
[moodle_detailed_courses] 

It accepts the parameer "viewtype", which can get one of the following values:
  * list: display courses as a list with title, summary and teachers
  * table: display courses in a datatable which permits sorting and searching
The default value is "list". Thus use the shortcode in one of the following ways:
	[moodle_detailed_courses] 
	[moodle_detailed_courses viewtype='list'] 
	[moodle_detailed_courses viewtype='table'] 

== Changelog ==

= 1.2 =
 * Improvements relative to reducing response times in retrieving moodle data

 = 1.1 =
 * Moodle courses view shortcode
 * Plugin internationalization (for some reason, the greek locale is not defined as el_GR in WP but simply as el, so an additional moodle-courses-view-el.mo is provided in the language files)
 
= 1.0 =
 * Moodle cousres view widget