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
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @author     2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\model\check_manager_interface;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;

require_once(__DIR__ . '/../locallib.php');

class plugin_manager implements check_manager_interface {
    // Enable this if you want to run the checks directly. This is helpful for debugging.
    const IMMEDIATE_RUN = false;
    // Enable this if you want to save the checks results after a run directly. This is helpful for debugging.
    const IMMEDIATE_SAVE_AFTER_RUN = false;
    // Enable this if you want to display the check result if this last one is deactivated.
    const DISPLAY_LAST_CHECK_RESULT_IF_DISABLED = true;

    // The checker filename.
    const PLUGIN_FILE = 'checker.php';
    // The renderer filename (this is an optional file).
    const PLUGIN_OUTPUT_FILE = 'renderer.php';
    // The dependency filename.
    const DEPENDENCY_FILE = 'dependency.php';
    // The interface tha the checker must implement (This is not verified yet).
    const PLUGIN_INTERFACE = 'block_course_checker\\model\\check_plugin_interface';
    // The plugin expected class, The token represent the folder_name.
    const PLUGIN_CLASS = "block_course_checker\checkers\\%s\\checker";
    // The renderer expected class, The token represent the folder_name.
    const PLUGIN_OUTPUT_CLASS = "block_course_checker\\checkers\\%s\\renderer";

    /**
     * A singleton instance of this class.
     *
     * @var \block_course_checker\plugin_manager
     */
    private static $instance;

    /**
     * @var array Cache of the instantiated checkers.
     */
    private static $plugins = [];

    /**
     * @var array Dependency informations of the instantiated checkers.
     */
    private static $dependency = [];

    /**
     * Force singleton
     */
    protected function __construct() {
    }

    /**
     * Don't allow to clone singleton
     */
    protected function __clone() {
    }

    /**
     * Factory method for this class .
     *
     * @return \block_course_checker\plugin_manager the singleton instance
     */
    public static function instance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Build a list of enabled plugins.
     *
     * @return check_plugin_interface[]
     */
    public function get_checkers_plugins() {
        // Use cache if set.
        if (!empty(self::$plugins)) {
            return self::$plugins;
        }
        $pluginroot = $this->get_checkers_folders();

        // Check that directory exists.
        if (!is_dir($pluginroot)) {
            debugging("Unable to open directory " . $pluginroot);
            return [];
        }

        // Iterate over each sub-plugin folder.
        $items = new \DirectoryIterator($pluginroot);
        foreach ($items as $item) {
            if ($item->isDot() or !$item->isDir()) {
                continue;
            }
            $filename = $item->getFilename();
            $filelocation = $pluginroot . "/" . $filename . "/" . self::PLUGIN_FILE;
            if (false === file_exists($filelocation)) {
                debugging(sprintf("Checker %s has a missing file: %s", $filename, $filelocation));
                continue;
            }

            $classname = sprintf(self::PLUGIN_CLASS, $filename);
            if (!class_exists($classname, true)) {
                debugging(sprintf("Checker %s has a missing class: %s", $filename, $classname));

                continue;
            }
            self::$plugins[$filename] = $this->get_checker($filename);
        }

        // Remove empty checkers.
        array_filter(self::$plugins, function($checker) {
            return $checker !== null;
        });
        return self::$plugins;
    }

    /**
     * Get the plugin checker for a specific check.
     *
     * @param string $checkername
     * @return check_plugin_interface|null
     */
    public function get_checker(string $checkername) {
        // Use the plugin if it has been instantiated.
        // Otherwise we just instantiate it, without caching for avoiding side effects with get_checkers_plugins.
        if (!empty(self::$plugins) && array_key_exists($checkername, self::$plugins)) {
            return self::$plugins[$checkername];
        }

        $pluginroot = $this->get_checkers_folders();
        $filelocation = $pluginroot . "/" . $checkername . "/" . self::PLUGIN_FILE;

        if (false === file_exists($filelocation)) {
            debugging(sprintf('File [%s] was not found for [%s] checker', $filelocation, $checkername));
            return null;
        }

        $classname = sprintf(self::PLUGIN_CLASS, $checkername);
        if (!class_exists($classname, true)) {
            debugging(sprintf("Checker %s has a missing class: %s", $checkername, $classname));
            return null;
        }

        // Skip checker if enabled but hide results.
        if (self::is_checker_hidden($checkername)) {
            return null;
        }

        return new $classname;
    }

    /**
     * Get the plugin renderer for a specific check, if it doesn't exist, fallback to the default one.
     *
     * @param string $checkername plugin name
     * @return global_plugin_renderer
     */
    public function get_renderer($checkername) {
        global $PAGE;
        $pluginroot = $this->get_checkers_folders();
        $filelocation = $pluginroot . "/" . $checkername . "/" . self::PLUGIN_OUTPUT_FILE;

        if (false === file_exists($filelocation)) {
            return $this->default_render();
        }

        $classname = sprintf(self::PLUGIN_OUTPUT_CLASS, $checkername);
        if (!class_exists($classname, true)) {
            debugging(sprintf("Checker %s has a missing class: %s", $checkername, $classname));
            return $this->default_render();
        }
        return new $classname($PAGE, RENDERER_TARGET_GENERAL);
    }

    /**
     * Get the checker group.
     *
     * @param string $checkername
     * @return string
     */
    public function get_group(string $checkername): string {
        $checker = $this->get_checker($checkername);
        return $checker !== null ? $checker->get_group() : "";
    }

    /**
     * @param \stdClass $course
     * @param array $lastchecksrecord
     * @return check_result_interface|array An array of result, indexed with the plugin/check name
     */
    public function run_checks($course, $lastchecksrecord) {
        $results = [];
        foreach ($this->get_checkers_plugins() as $checkername => $checker) {
            if ($this->is_checker_status($checkername, $course->id)) {
                $singleresult = $checker->run($course);
                $singleresult->add_timestamp();
                $results[$checkername] = $singleresult;
            } else {
                if ($lastchecksrecord != [] && self::DISPLAY_LAST_CHECK_RESULT_IF_DISABLED &&
                        array_key_exists($checkername, $lastchecksrecord['result'])) {
                    $results[$checkername] = $lastchecksrecord['result'][$checkername];
                }
            }
        }

        // For debug purpose.
        if (self::IMMEDIATE_SAVE_AFTER_RUN) {
            result_persister::instance()->save_checks($course->id, $results, [
                    "timestamp" => date("U")
            ]);
        }
        return $results;
    }

    /**
     * Return the path of the settings file for the specified checkername.
     *
     * @param string $checkername
     * @return string|null
     */
    public function get_checker_setting_file(string $checkername) {
        $pluginroot = $this->get_checkers_folders();
        $filelocation = $pluginroot . "/" . $checkername . "/settings.php";
        return file_exists($filelocation) ? $filelocation : null;
    }

    /**
     * Return the path of the edit_form file for the specified checkername.
     *
     * @param string $checkername
     * @return string|null
     */
    public function get_checker_edit_form_file(string $checkername) {
        $pluginroot = $this->get_checkers_folders();
        $filelocation = $pluginroot . "/" . $checkername . "/edit_form.php";
        return file_exists($filelocation) ? $filelocation : null;
    }

    /**
     * Return the dependency information for the specified checkername.
     *
     * @param string $checkername
     * @return array
     */
    public function get_checker_dependency_info(string $checkername) {
        // Use cache if set.
        if (!empty(self::$dependency[$checkername])) {
            return self::$dependency[$checkername];
        }

        $pluginroot = $this->get_checkers_folders();
        $filelocation = $pluginroot . "/" . $checkername . "/" . self::DEPENDENCY_FILE;

        if (file_exists($filelocation)) {
            $dependencyfile = require($filelocation);
            self::$dependency = block_course_checker_get_dependency_info($checkername, $dependencyfile);
        } else {
            self::$dependency[$checkername]['status'] = true;
        }

        return self::$dependency[$checkername];
    }

    /**
     * @param $course
     * @param string $checkname
     * @return check_result_interface[]
     */
    public function run_single_check($course, string $checkname): array {
        // Check that the checker exists.
        $checker = $this->get_checker($checkname);
        if (!$checker) {
            return [];
        }

        if (!$this->is_checker_status($checkname, $course->id)) {
            return [];
        }

        // Run the check.
        $result = $checker->run($course);
        $result->add_timestamp();
        $results = [$checkname => $result];
        return $results;
    }

    /**
     *
     * Returns if checker is enabled or not and depending on other settings.
     * - First checks if plugin is enabled.
     * - Then if enabled checks for problems with dependencies of 3rd party plugins.
     * - After handle checkers with advanced settings. The "checker_activedates" is only useful for template courses.
     *
     * @param string $checkername
     * @param int $courseid
     * @return bool
     * @throws \dml_exception
     */
    public function is_checker_status(string $checkername, int $courseid): bool {
        if (!get_config('block_course_checker', $checkername . '_status')) {
            return false;
        }

        if (!self::get_checker_dependency_info($checkername)) {
            return false;
        }

        $advanced = self::has_checker_advanced_settings($checkername);
        if (!empty($advanced->value)) {
            return self::has_checker_restrictions($courseid, $checkername, 'coursesregex');
        }

        return true;
    }

    /**
     * @param string $checkername
     * @return bool
     */
    public function is_checker_hidden(string $checkername): bool {
        return get_config('block_course_checker', $checkername . '_hidden');
    }

    /**
     * @param int $courseid
     * @return bool returns true if a least one check is enabled.
     * @throws \dml_exception
     */
    public function are_checkers_enabled(int $courseid): bool {
        foreach ($this->get_checkers_plugins() as $checkername => $checker) {
            if ($this->is_checker_status($checkername, $courseid)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $checkername
     * @return bool|object
     * @throws \dml_exception
     */
    public function has_checker_advanced_settings($checkername) {
        global $DB;

        $sql = "SELECT * FROM {config_plugins} WHERE name LIKE '$checkername%_adv'";

        return $DB->get_record_sql($sql, null, IGNORE_MULTIPLE);
    }

    /**
     * @param $courseid
     * @param $checkername
     * @param $setting
     * @return bool
     * @throws \dml_exception
     */
    public function has_checker_restrictions($courseid, $checkername, $setting) {
        $course = get_course($courseid);
        $regex = get_config('block_course_checker', $checkername . '_' . $setting);

        return preg_match($regex, $course->fullname);
    }

    /**
     * Get the folder where checkers must be located.
     *
     * @return string
     */
    private function get_checkers_folders() {
        return __DIR__ . "/checkers";
    }

    /**
     * @return global_plugin_renderer
     */
    private function default_render() {
        global $PAGE;
        return new global_plugin_renderer($PAGE, RENDERER_TARGET_GENERAL);
    }

    /**
     * Get group order: the order in which the groups are displayed.
     * TODO: consider putting this into the settings page with FFHSCC-23 to avoid hard coding this.
     *
     * @return array
     */
    public function get_group_order() {
        return array('group_course_settings' => 1, 'group_activities' => 2, 'group_blocks' => 3, 'group_links' => 4);
    }
}
