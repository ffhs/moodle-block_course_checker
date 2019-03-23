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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\model\check_manager_interface;
use block_course_checker\model\check_plugin_interface;
use block_course_checker\model\check_result_interface;

class plugin_manager implements check_manager_interface {
    // Enable this if you want to run the checks directly. This is helpful for debugging.
    const IMMEDIATE_RUN = false;
    // Enable this if you want to save the checks results after a run directly. This is helpful for debugging.
    const IMMEDIATE_SAVE_AFTER_RUN = false;

    // The checker filename.
    const PLUGIN_FILE = 'checker.php';
    // The renderer filename (this is an optional file).
    const PLUGIN_OUTPUT_FILE = 'renderer.php';
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
    protected function get_checkers_plugins() {
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
            $pluginname = $item->getFilename();
            $filelocation = $pluginroot . "/" . $pluginname . "/" . self::PLUGIN_FILE;
            if (false === file_exists($filelocation)) {
                debugging(sprintf("Checker %s has a missing file: %s", $pluginname, $filelocation));
                continue;
            }

            $classname = sprintf(self::PLUGIN_CLASS, $pluginname);
            if (!class_exists($classname, true)) {
                debugging(sprintf("Checker %s has a missing class: %s", $pluginname, $classname));

                continue;
            }
            self::$plugins[$pluginname] = $this->get_checker($pluginname);
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
     * @param string $pluginname
     * @return check_plugin_interface|null
     */
    public function get_checker(string $pluginname) {
        // Use the plugin if it has been instantiated.
        // Otherwise we just instantiate it, without caching for avoiding side effects with get_checkers_plugins.
        if (!empty(self::$plugins) && array_key_exists($pluginname, self::$plugins)) {
            return self::$plugins[$pluginname];
        }

        $pluginroot = $this->get_checkers_folders();
        $filelocation = $pluginroot . "/" . $pluginname . "/" . self::PLUGIN_FILE;

        if (false === file_exists($filelocation)) {
            debugging(sprintf('File [%s] was not found for [%s] checker', $filelocation, $pluginname));
            return null;
        }

        $classname = sprintf(self::PLUGIN_CLASS, $pluginname);
        if (!class_exists($classname, true)) {
            debugging(sprintf("Checker %s has a missing class: %s", $pluginname, $classname));
            return null;
        }

        return new $classname;
    }

    /**
     * Get the plugin renderer for a specific check, if it doesn't exist, fallback to the default one.
     *
     * @param string $pluginname plugin name
     * @return global_plugin_renderer
     */
    public function get_renderer($pluginname) {
        global $PAGE;
        $pluginroot = $this->get_checkers_folders();
        $filelocation = $pluginroot . "/" . $pluginname . "/" . self::PLUGIN_OUTPUT_FILE;

        if (false === file_exists($filelocation)) {
            return $this->default_render();
        }

        $classname = sprintf(self::PLUGIN_OUTPUT_CLASS, $pluginname);
        if (!class_exists($classname, true)) {
            debugging(sprintf("Checker %s has a missing class: %s", $pluginname, $classname));
            return $this->default_render();
        }
        return new $classname($PAGE, RENDERER_TARGET_GENERAL);
    }

    /**
     * Get the checker group.
     *
     * @param string $pluginname
     * @return string
     */
    public function get_group(string $pluginname): string {
        $checker = $this->get_checker($pluginname);
        return $checker !== null ? $checker->get_group() : "";
    }

    /**
     * @param \stdClass $course
     * @return check_result_interface|array An array of result, indexed with the plugin/check name
     */
    public function run_checks($course) {
        $results = [];
        foreach ($this->get_checkers_plugins() as $pluginname => $plugin) {
            $results[$pluginname] = $plugin->run($course);
        }

        // For debug purpose.
        if (self::IMMEDIATE_SAVE_AFTER_RUN) {
            result_persister::instance()->save_checks($course->id, $results);
        }
        return $results;
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
     *
     * @return array
     */
    public function get_group_order() {
        return array('group_course_settings' => 1, 'group_links' => 2);
    }
}