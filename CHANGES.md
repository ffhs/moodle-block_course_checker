# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v3.7-r2 (Build: )] + [ (Build: )] - 
### Added
- It's now possible to define a block configuration for every checker `classes/checkers/checker_name/edit_form.php`
- New data checker to check if there are database activities without fields
- New activedates checker to see if there are any active dates in activities
- The referencesettings-checker can now be used to compare filter

### Changed
- The link-checker has been improved in order to get better results
- The link-checker has now more possibilities to configure - User Agent
- The link-checker now checks book-chapters and  

## [v3.7-r1 (Build: 2019121800)] + [v3.6-r5 (Build: 2019071004)] - 2019-12-18
### Changed
- cURL request follows now three redirects

### Fixed
- Regex for `get_urls_from_text()` - allow dash at the end ([issue #10](https://github.com/ffhs/moodle-block_course_checker/issues/10))
- Show last activity change in block ([issue #9](https://github.com/ffhs/moodle-block_course_checker/issues/9))

## [v3.6-r4 (Build: 2019071003)] - 2019-10-30
### Added
- 3rd party plugin dependency handling ([issue #1](https://github.com/ffhs/moodle-block_course_checker/issues/1))
- Functionality to show/hide results if checker is disabled ([issue #6](https://github.com/ffhs/moodle-block_course_checker/issues/6))

## [v3.6-r3 (Build: 2019071002)] - 2019-10-15
### Changed
- Language strings according [MoodleDocs](https://docs.moodle.org/dev/Plugin_contribution_checklist#Strings) ([issue #3](https://github.com/ffhs/moodle-block_course_checker/issues/3))
- Change db field types ([issue #4](https://github.com/ffhs/moodle-block_course_checker/issues/4))

### Fixed
- Remove duplicated class `date_picker_input` ([issue #5](https://github.com/ffhs/moodle-block_course_checker/issues/5))

## [v3.6-r2 (Build: 2019071001)] - 2019-10-14
### Added
- Add capability `block/course_checker:view`

### Fixed
- Add `name` attribute to coursemodule delete event
- Fix `check_url` if host is `null`

## [v3.6-r1 (Build: 2019071001)] - 2019-07-10
- Initial release