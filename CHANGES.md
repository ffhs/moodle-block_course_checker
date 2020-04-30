# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v3.8-r1 (Build: 202005xx00) + v3.7-r2 (Build: 2019121801)] - 2020-05-xx
### Added
- Add `userdata` checker to check `databases`, `forums`, `glossaries` and `wikis` for entries ([issue #21](https://github.com/ffhs/moodle-block_course_checker/issues/21))
- Add `quiz` checker to check if the `maximumgrade` value is set correct ([issue #22](https://github.com/ffhs/moodle-block_course_checker/issues/22))
- Add method `is_checker_enabled_by_default` to set the global `checker_<checkername>_status` setting
  - This was done in due of FFHS-specific checkers, e.g `activedates` and `userdata` ([issue #19](https://github.com/ffhs/moodle-block_course_checker/issues/19))
- Add `block configuration` where settings can be defined per checker in `classes/checkers/<checker_name>/edit_form.php` ([issue #26](https://github.com/ffhs/moodle-block_course_checker/issues/26), [issue #30](https://github.com/ffhs/moodle-block_course_checker/issues/30))
- Enable `manual check date form` only for given roles ([issue #20](https://github.com/ffhs/moodle-block_course_checker/issues/20))
- Extend `link` checker, now course summary, book chapters and wiki pages will be crawled ([issue #13](https://github.com/ffhs/moodle-block_course_checker/issues/13))
- Add `activedates` checker to check if any `timing` configuration is set in activities ([issue #19](https://github.com/ffhs/moodle-block_course_checker/issues/19))
- Extend `referencesettings` checker with a `filter comparison` ([issue #24](https://github.com/ffhs/moodle-block_course_checker/issues/24))
- Add `database` checker to check if there are activities `without fields` ([issue #25](https://github.com/ffhs/moodle-block_course_checker/issues/25))

### Changed
- Adapt the `referencesettings message` when the check is successful to be more consistent ([issue #24](https://github.com/ffhs/moodle-block_course_checker/issues/24))
- Improved the `instances loop` to fetch given activities ([issue #25](https://github.com/ffhs/moodle-block_course_checker/issues/25))
- Improved the `resolutionlink` method ([issue #27](https://github.com/ffhs/moodle-block_course_checker/issues/25))
- Improved the `link` checker to get better results, e.g. `User-Agent` and `file handler` ([issue #12](https://github.com/ffhs/moodle-block_course_checker/issues/12), [issue #15](https://github.com/ffhs/moodle-block_course_checker/issues/15))

### Fixed
- Removed `hardcoded link color` in checkers full notification ([commit 81c0158](https://github.com/ffhs/moodle-block_course_checker/commit/81c015835972f8616406d2417d5b1aaa7aa759a3)) 

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
