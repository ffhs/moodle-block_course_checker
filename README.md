# Moodle Course Checker ![Moodle Plugin CI](https://github.com/ffhs/moodle-block_course_checker/workflows/Moodle%20Plugin%20CI/badge.svg) [![Coverage Status](https://coveralls.io/repos/github/ffhs/moodle-block_course_checker/badge.svg?branch=master)](https://coveralls.io/github/ffhs/moodle-block_course_checker?branch=master)

## A Moodle course checker plugin that improves the quality and eliminate human routine tasks in online courses

This plugin provides a framework that can check a course based on independent checkers. It will help you find misconfiguration in your courses and follow your internal guidelines.
The checkers can be triggered manually an will be executed by the Moodle AdHoc task system.

## Requirements

This plugin should be compatible with Moodle 3.7+

- You have to set up a cron to run the checkers
- `php-ext-curl` should be on (for the link checker)

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

See [MoodleDocs](https://docs.moodle.org/311/en/Installing_plugins) for details on installing Moodle plugins

## Documentation

This plugin provides interfaces and well defined result objects to extend this plugin. New useful checkers and issue notifications are highly appropriated

- Each checker can be executed separately (re-run or all at once)
- Individual checkers can be deactivated
- If checkers are deactivated you can show or hide the existing results
- Some checkers e.g `activedates` and `userdata` make only sense to be run in template courses. Therefore, they have an advanced setting where you can define a `regexp` which
  checks the course fullname if the checker will be shown or not.
- A static date and a note can be stored (human check)
    - A list with edited and created activities since this date will be displayed
    - This form can allowed only for given roles
- The user will be notified by Moodle when a check is done
- Individual settings are defined in each checker (`classes/checkers/checker_name/settings.php`), rather than in settings.php
- Individual edit_forms are defined in each checker (`classes/checkers/checker_name/edit_form.php`), rather than in edit_form.php
- Individual dependencies are defined in each checker (`classes/checkers/checker_name/dependency.php`)

### Available checkers

| Checker Name | User Story | Dependency :warning: |
|--------------|------------|----------------------|
|Attendance sessions|As editing teacher,<br>- I would like to see if in my course the attendance activity is created.<br><br><i>There must be no sessions added to the activity, to ensure teachers can take attendance during class and that no old sessions are copied.|[mod_attendance](https://moodle.org/plugins/mod_attendance)</i>|
|Group submission for assignments|As editing teacher,<br>- I would like to see, if in my course in an assignment the option "students submit in groups" is set, whether the "group mode" is activated.<br><br><i>A grouping is created and selected, and corresponding groups exist and are allocated.<br>This allows me to ensure that the first submission of a student does not block the submission for all other students.<i>| |
|Links in course summary and URL activities|As editing teacher,<br>- I would like to see if all the external links that are in the course are reachable<br>-I would like to be able to create my own domain whitelist per course<br><br><i>Links that requires authentication are currently not supported (e.g internal Moodle links)</i>| |
|Settings compared to reference course|As editing teacher,<br>- I would like to be able to compare specific coursesetting fields between a course and the reference course<br>- I would like to be able to compare the filter settings between a course and the reference course<br>-I would like to be able to compare courseformat options between a course and the reference course| |
|Label subheadings check|As editing teacher,<br>- I would like to see, if all labels starts with a h4 HTML tag and a FontAwesome icon| |
|Data activity with fieldsk|As editing teacher,<br>- I would like to see, if databases have fields defined| |
|Active dates in activity configurations|As editing teacher,<br>- I would like to see, if activities has timing configurations set| |
|Total mark in activity quiz|As editing teacher,<br>- I see that for quizzes, the maximum points correspond to the total number questions points| |
|Stored user data in activities|As editing teacher,<br>- I see that no user data is entered for activities that may contain user data (e.g. databases, forums, glossaries, wikis)<br><br><i>They will not be copied to the individual courses</i>| |
|Blocks exists|As editing teacher,<br>- I would like to be able to compare the present blocks between a course and the reference course| |

## Copyright

Copyright (C) 2019 <a href="https://www.liip.ch" target="_blank">Liip AG</a> the Swiss Moodle Partner.

Further developments and open-sourced by <a href="https://www.ffhs.ch" target="_blank">Swiss Distance University of Applied Sciences (FFHS)</a>.
