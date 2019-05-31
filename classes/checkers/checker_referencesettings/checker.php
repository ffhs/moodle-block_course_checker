<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace block_course_checker\checkers\checker_referencesettings;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\checker_config_trait;

/**
 * Checking the course settings compared to a reference course
 *
 * @package block_course_checker
 */
class checker implements check_plugin_interface {
    use checker_config_trait;

    /** @var check_result */
    protected $result = null;

    const REFERENCE_COURSE = 'block_course_checker/referencecourseid';
    const REFERENCE_COURSE_DEFAULT = 1;
    const REFERENCE_COURSE_SETTINGS = 'block_course_checker/checker_referencesettings_checklist';
    const REFERENCE_COURSE_SETTINGS_DEFAULT = ['format' => 1];

    /** @var int $referencecourseid from checker settings */
    protected $referencecourseid;

    /** @var int $referencecourseid from checker settings */
    protected $referencesettings;

    /**
     * Initialize checker by setting it up with the configuration
     *
     */
    public function init() {
        // Load settings.
        $this->referencecourseid = (int) $this->get_config(self::REFERENCE_COURSE, self::REFERENCE_COURSE_DEFAULT);
        $this->referencesettings = explode(',', $this->get_config(
                self::REFERENCE_COURSE_SETTINGS,
                self::REFERENCE_COURSE_SETTINGS_DEFAULT
        ));
    }

    /**
     * Runs the check
     *
     * @param \stdClass $course
     * @return check_result|check_result_interface
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function run($course) {
        // Get active setting checks from configuration.
        $this->init();

        // Initialize check result array.
        $this->result = new check_result();

        // Get current and referencecourse configuration.
        $currentcourse = $course;
        $referencecourse = get_course($this->referencecourseid);

        // Run comparison for every attribute.
        foreach ($this->referencesettings as $setting) {
            // Does the attribute exist on both courses?
            if (!property_exists($referencecourse, $setting) or !property_exists($currentcourse, $setting)) {
                $message = get_string(
                        'referencesettings_settingismissing',
                        'block_course_checker',
                        ['setting' => $setting]);
                $this->result->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => '',
                        "link" => ''
                ])->set_successful(false);
                continue;
            }

            // Get link to course edit page.
            $link = $this->get_link_to_course_edit_page($course);

            // What are the differences? (if any).
            $comparison = $this->get_comparison_string($setting, $referencecourse, $currentcourse);

            // When the settings are not equal.
            if ($referencecourse->$setting != $currentcourse->$setting) {
                $message = get_string(
                        'referencesettings_failing',
                        'block_course_checker',
                        ['setting' => $setting]);
                $this->result->add_detail([
                        "successful" => false,
                        "message" => $message . $comparison,
                        "target" => '',
                        "link" => $link
                ])->set_successful(false);
                continue;
            }

            // When everything is okay.
            $message = get_string(
                    'referencesettings_success',
                    'block_course_checker',
                    ['setting' => $setting]);
            $this->result->add_detail([
                    "successful" => true,
                    "message" => $message . $comparison,
                    "target" => '',
                    "link" => $link
            ]);
        }

        // Return the check results.
        return $this->result;
    }

    /**
     * Get the group defined for this check.
     * This is used to display checks from the same group together.
     *
     * @return string
     */
    public static function get_group() {
        return 'group_course_settings';
    }

    /**
     * @param $setting
     * @param \stdClass $referencecourse
     * @param \stdClass $currentcourse
     * @return string
     * @throws \coding_exception
     */
    private function get_comparison_string($setting, \stdClass $referencecourse, \stdClass $currentcourse): string {
        return get_string(
                'referencesettings_comparison',
                'block_course_checker',
                ['settingvaluereference' => $referencecourse->$setting, 'settingvaluecurrent' => $currentcourse->$setting]);
    }

    /**
     * @param $course
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function get_link_to_course_edit_page($course): string {
        $link = (new \moodle_url('/course/edit.php', [
                'id' => $course->id
        ]))->out_as_local_url(false);
        return $link;
    }
}
