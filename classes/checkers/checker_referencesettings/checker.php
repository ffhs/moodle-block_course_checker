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

/**
 * Checking the course settings compared to a reference course
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @author     2019 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\checkers\checker_referencesettings;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\check_result;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;
use block_course_checker\model\checker_config_trait;
use block_course_checker\resolution_link_helper;
use context_course;

class checker implements check_plugin_interface {
    use checker_config_trait;

    /** @var check_result */
    protected $result = null;

    const REFERENCE_COURSE = 'block_course_checker/referencecourseid';
    const REFERENCE_COURSE_DEFAULT = 1;
    const REFERENCE_COURSE_SETTINGS = 'block_course_checker/checker_referencesettings_checklist';
    const REFERENCE_COURSE_SETTINGS_DEFAULT = ['format' => 1];
    const REFERENCE_COURSE_FILTER_ENABLED = 'block_course_checker/checker_referencesettings_filter';
    const REFERENCE_COURSE_FILTER_ENABLED_DEFAULT = false;

    /** @var int $referencecourseid from checker settings */
    protected $referencecourseid;

    /** @var array $referencecourseid from checker settings */
    protected $referencesettings = [];

    /** @var array $referencefilterenabled from checker settings */
    protected $referencefilterenabled = false;

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
        $this->referencefilterenabled = $this->get_config(
                self::REFERENCE_COURSE_FILTER_ENABLED,
                self::REFERENCE_COURSE_FILTER_ENABLED_DEFAULT
        );
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

        // Check settings like Category, Format, Force Language. See plugin settings for complete list.
        $this->compare_default_course_settings($course, $referencecourse, $currentcourse);

        // Check if the course filters have the same settings as the template reference course.
        $this->compare_course_level_filters($currentcourse, $referencecourse);

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
     * Get the defaultsetting to use in the global settings.
     *
     * @return bool
     */
    public static function is_checker_enabled_by_default() {
        return true;
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
                'checker_referencesettings_comparison',
                'block_course_checker',
                ['settingvaluereference' => $referencecourse->$setting, 'settingvaluecurrent' => $currentcourse->$setting]);
    }

    /**
     * @param $filterinforeference
     * @param $filterinfocurrent
     * @return string
     * @throws \coding_exception
     */
    private function get_filter_comparison_string($filterinforeference, $filterinfocurrent): string {
        return get_string(
                'checker_referencefilter_comparison',
                'block_course_checker',
                [
                        'filtervaluereference' => $filterinforeference->localstate,
                        'filtervaluecurrent' => $filterinfocurrent->localstate
                ]
        );
    }

    /**
     * @param $course
     * @param \stdClass $referencecourse
     * @param \stdClass $currentcourse
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function compare_default_course_settings($course, \stdClass $referencecourse, \stdClass $currentcourse) {
        // Run comparison for every attribute.
        foreach ($this->referencesettings as $setting) {
            // Does the attribute exist on both courses?
            if (!property_exists($referencecourse, $setting) or !property_exists($currentcourse, $setting)) {
                $message = get_string(
                        'checker_referencesettings_settingismissing',
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
            $link = resolution_link_helper::get_link_to_course_edit_page($course);

            // What are the differences? (if any).
            $comparison = $this->get_comparison_string($setting, $referencecourse, $currentcourse);

            // When the settings are not equal.
            if ($referencecourse->$setting != $currentcourse->$setting) {
                $message = get_string(
                        'checker_referencesettings_failing',
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
                    'checker_referencesettings_success',
                    'block_course_checker',
                    ['setting' => $setting]);
            $this->result->add_detail([
                    "successful" => true,
                    "message" => $message . $comparison,
                    "target" => '',
                    "link" => $link
            ]);
        }
    }

    /**
     * @param \stdClass $currentcourse
     * @param \stdClass $referencecourse
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function compare_course_level_filters(\stdClass $currentcourse, \stdClass $referencecourse) {
        if (!$this->referencefilterenabled) {
            return;
        }

        // Get the course context for the current course and the reference course.
        $currentcontext = context_course::instance($currentcourse->id);
        $referencecontext = context_course::instance($referencecourse->id);

        // Get the list of available filters.
        $currentavailablefilters = filter_get_available_in_context($currentcontext);
        $referenceavailablefilters = filter_get_available_in_context($referencecontext);

        // Count occurring errors.
        $occurringfilterproblems = 0;

        // Get link to course filter page.
        $link = resolution_link_helper::get_link_to_course_filter_page($currentcontext);

        // Count all errors.
        foreach ($referenceavailablefilters as $filterkey => $referencefilterinfo) {
            if (!isset($currentavailablefilters[$filterkey])) {
                $message = get_string(
                        'checker_referencefilter_filternotsetincurrentcourse',
                        'block_course_checker',
                        ['filterkey' => $filterkey]);
                $this->result->add_detail([
                        "successful" => false,
                        "message" => $message,
                        "target" => '',
                        "link" => $link
                ])->set_successful(false);
                continue;
            }
            if ($currentavailablefilters[$filterkey]->localstate != $referencefilterinfo->localstate) {
                // What are the differences? (if any).
                $comparison = $this->get_filter_comparison_string($referencefilterinfo, $currentavailablefilters[$filterkey]);
                $message = get_string(
                        'checker_referencefilter_failing',
                        'block_course_checker',
                        ['filterkey' => $filterkey]);
                $this->result->add_detail([
                        "successful" => false,
                        "message" => $message . $comparison,
                        "target" => '',
                        "link" => $link
                ])->set_successful(false);
                $occurringfilterproblems++;
                continue;
            }
        }

        if ($occurringfilterproblems === 0) {
            $this->result->add_detail([
                    "successful" => true,
                    "message" => get_string('checker_referencefilter_success', 'block_course_checker'),
            ]);
        }
    }
}