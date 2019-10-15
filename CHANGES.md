# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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