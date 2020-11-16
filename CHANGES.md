# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v3.10-r1] (Build: 2020111600) - 2020-11-16
## Added
- Release version for Moodle 3.10

## [v3.9-r2 (Build: 2020062401) + v3.8-r4 (Build: 2020050503) + v3.7-r5 (Build: 2019121804)] - 2020-07-24
### Changed
- Improve `checker_links_whitelist` to enable URL paths and custom port ([issue #39](https://github.com/ffhs/moodle-block_course_checker/issues/39), [pull request #40](https://github.com/ffhs/moodle-block_course_checker/pull/40))

## [v3.9-r1 (Build: 2020062400) + v3.8-r3 (Build: 2020050502) + v3.7-r4 (Build: 2019121803)] - 2020-06-24
### Added
- Simple behat scenario `addblockinstance` ([issue #35](https://github.com/ffhs/moodle-block_course_checker/issues/35))

### Changed
- Reworked `re-run` buttons ([commit 7067c97](https://github.com/ffhs/moodle-block_course_checker/commit/7067c979387d3d957568ed4afe3f40dc7099f8cf))
- Adapt `user_has_role_in_course` method ([issue #37](https://github.com/ffhs/moodle-block_course_checker/issues/37))

### Fixed
- Bugfix in `checker_userdata` to fetch glossary ([commit dac81cb](https://github.com/ffhs/moodle-block_course_checker/commit/dac81cb3bbaa680698c57536ec3d89db2a87ce91))
- Fix double coded local `URLs` ([issue #36](https://github.com/ffhs/moodle-block_course_checker/issues/36))
- Get rid of notice when `link_whitelist` is not set in course block ([issue #34](https://github.com/ffhs/moodle-block_course_checker/issues/34))

## [v3.8-r2 (Build: 2020050501) + v3.7-r3 (Build: 2019121802)] - 2020-05-08
### Changed
- Renamed `checker_link` to `checker_links` in due of AMOS problems ([issue #33](https://github.com/ffhs/moodle-block_course_checker/issues/33))

### Fixed
- Hardcoded lang string ([issue #33](https://github.com/ffhs/moodle-block_course_checker/issues/33))

## [v3.8-r1 (Build: 2020050500) + v3.7-r2 (Build: 2019121801)] - 2020-05-05
### Added
- Add `blocks` checker to check if the same blocks are present
- Extend `link` checker, now in each course can be defined a own `domain whitelist` ([issue #30](https://github.com/ffhs/moodle-block_course_checker/issues/30))
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
- The `domain whitelist` input textarea now allows URLs and not only domains ([issue #30](https://github.com/ffhs/moodle-block_course_checker/issues/30))
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
