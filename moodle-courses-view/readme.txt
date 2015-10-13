=== Plugin Name ===
Contributors: Angela Dimitriou
Tested up to: 4.3
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to display the list of courses and course categories of a moodle installation.

== Description ==

This plugin allows a user to add a widget in a wordpress site that lists categories, courses and limited course content hosted in a moodle site.

== Installation ==

Steps to enable web services in a moodle installation:

1. Login as moodle site administrator and go to the administration page
2. Enable web services (normally from /admin/search.php?query=enablewebservices)
3. From plugins menu item select "Web services"
	---> Manage protocols
	---> Activate REST protocol
4. Create a new web service from Plugins -> Web services -> External services:
	Add the following functions to the new service:

		core_course_get_contents 
		core_course_get_courses 
		core_course_get_categories 

After the service creation the rquired capabilities for accessing the service are listed. These are important for the permissions assigned to the user that must be created to use the service.
Normally they are the following:
	- webservice/rest:use 
	- moodle/course:update
	- moodle/course:viewhiddencourses
	- moodle/course:view
	- moodle/category:viewhiddencategories

5. Create a user with which the WP site will connet to get data from the service
6. Define a new role for which the above required capabilities will be set to "Allowed"
 	Users -> Permissions -> Define role:

7. Assign role to the new user
	Users -> Permissions -> assign system roles

8. Assign newly created user to the service's "authorized users"
	Plugins -> Web services -> External services -> Authorised users of new service : add new user
	(!Notice any missing capabilities)

9. Create a new token for the web service user. Copy the generated token for the plugin.
	Plugins --> Manage services --> Manage tokens

Plugin installation:

Download and uncompress the Moodle Courses View plugin in the plugins directory of wordpress. Activate it and complete the options required in the settings page. The information equired ar ethe moodle site hosting the web service, the service path (usually /webservice/rest/server.php) and the token created for the web service user.

== Changelog ==
