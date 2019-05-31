# FFHS course block checker
This plugin provides a framework that can check a course based on independent checkers. It will
help you find misconfiguration in your courses and follow your internal guidelines.
The checkers can be triggered manually an will be executed by the Moodle AdHoc task system.

## Requirements
 - You have to set up a cron to run the checkers
 - Moodle 3.5+
 - php-ext-curl should be on (for the link checker)

## Features
- Each checker can be executed separately (or all at once)
- Individual checkers can be deactivated
- A static date and a note can be stored
    - A list with edited and created activities since this date can be displayed
- The user will be notified by moodle when a check is done
- Individual settings are defined in each checker, rather than in settings.php
- This plugin provides interfaces and well defined result objects

## Available checkers (User stories)
### Reference settings
As Administrator,
I would like to be able to compare specific setting fields between a course and the reference course and configure which fields are compared
In order to allow me to add easy checks without updating the plugin.
 
### Attendance checker
As editing teacher,
I would like to see if in my course the attendance activity is created. There must be no sessions added to the activity.
In order to ensure teachers can take attendance during class and that no old sessions are copied. 

### Groups checker
As editing teacher,
I would like to see, if in my course in an assignment the option "students submit in groups" is set, whether the "group mode" activated, a grouping is created and selected, and corresponding groups exist and are allocated.
This allows me to ensure that the first submission of a student does not block the submission for all other students.

### External link checker
As editing teacher,
I would like to see if all the external links that are in the course are reachable,
In order to make sure there are no broken links in the course.
- Links that requires authentication are currently not supported (internal moodle links)

### Subheading checker
This checker looks into all label activities to control if they start with a h4 tag and a fontawesome icon.

## Changelog

### 2019031508 
Initial release from sprint 1 "Provide the framework for the checks and have at least one check functional".

## Support
Support for this plugin is not granted, nevertheless new useful checkers and issue notifications are highly appropriated

## Authors
- This plugin is founded and maintained by the Swiss Distance University of Applied Sciences (<a href="https://www.ffhs.ch">ffhs.ch</a>). 
- This plugin was developed by Liip AG and the FFHS.