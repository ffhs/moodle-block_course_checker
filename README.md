# Moodle Course Checker [![Build Status](https://travis-ci.org/ffhs/moodle-block_course_checker.svg?branch=master)](https://travis-ci.org/ffhs/moodle-block_course_checker)
This plugin provides a framework that can check a course based on independent checkers. It will
help you find misconfiguration in your courses and follow your internal guidelines.
The checkers can be triggered manually an will be executed by the Moodle AdHoc task system.

## Requirements
This plugin should be compatible with Moodle 3.5+.
 - You have to set up a cron to run the checkers
 - php-ext-curl should be on (for the link checker)
 
## Installation
Install the plugin like any other plugin to folder `blocks/course_checker`.

Use git to install this plugin: 
```bash
cd /var/www/html/moodle
git clone https://github.com/ffhs/moodle-block_course_checker.git blocks/course_checker
echo '/blocks/course_checker/' >> .git/info/exclude
```

Then complete upgrade over CLI:
```bash
sudo -u apache /usr/bin/php admin/cli/upgrade.php
```
or GUI (Site administration -> Notifications).

See [MoodleDocs](https://docs.moodle.org/37/en/Installing_plugins) for details on installing Moodle plugins

## Documentation
This plugin provides interfaces and well defined result objects to extend this plugin. New useful checkers and issue notifications are highly appropriated
- Each checker can be executed separately (or all at once)
- Individual checkers can be deactivated
- If checkers are deactivated you can show or hide the existing results
- A static date and a note can be stored (human check)
    - A list with edited and created activities since this date will be displayed
- The user will be notified by Moodle when a check is done
- Individual settings are defined in each checker (`classes/checkers/checker_name/settings.php`), rather than in settings.php
- Individual dependencies are defined in each checker (`classes/checkers/checker_name/dependency.php`)

### Available checkers
| Checker Name | User Story | Dependency :warning: |
|--------------|------------|----------------------|
|Attendance Sessions|As editing teacher,<br>I would like to see if in my course the attendance activity is created. There must be no sessions added to the activity.<br>In order to ensure teachers can take attendance during class and that no old sessions are copied.|[mod_attendance](https://moodle.org/plugins/mod_attendance)|
|Group Submission|As editing teacher,<br>I would like to see, if in my course in an assignment the option "students submit in groups" is set, whether the "group mode" activated, a grouping is created and selected, and corresponding groups exist and are allocated.<br>This allows me to ensure that the first submission of a student does not block the submission for all other students.| |
|Links|As editing teacher,<br>I would like to see if all the external links that are in the course are reachable,<br>In order to make sure there are no broken links in the course.<br>- Links that requires authentication are currently not supported (internal moodle links)| |
|Reference Settings|As Administrator,<br> I would like to be able to compare specific setting fields between a course and the reference course and configure which fields are compared<br>In order to allow me to add easy checks without updating the plugin.| |
|Label Subheadings|As editing teacher,<br>I would like to see, if all label activities starts with a h4 tag and a fontawesome icon.| |

## Copyright
Copyright (C) 2019 <a href="https://www.liip.ch" target="_blank">Liip AG</a> the Swiss Moodle Partner.

Further developments and open-sourced by <a href="https://www.ffhs.ch" target="_blank">Swiss Distance University of Applied Sciences (FFHS)</a>.