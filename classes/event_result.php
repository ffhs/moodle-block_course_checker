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
 * This class is an helper to convert "event" to a format suitable for Mustache.
 * It will load the course modinfo and try to match it with the event to retrieve data from it.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

use block_course_checker\model\event_result_interface;
use pix_icon;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

class event_result implements event_result_interface {
    /**
     * Record from the database.
     *
     * @var object
     */
    private $record;
    /**
     * The course id.
     *
     * @var int
     */
    private $courseid;

    /**
     * Get the cm_info object based on the current record.
     *
     * @return \cm_info|null
     */
    protected function get_mod() {
        $modname = $this->record->modulename;
        $instanceid = $this->record->instance_id;
        try {
            $modinfo = get_fast_modinfo($this->courseid);
        } catch (\moodle_exception $e) {
            debugging($e->getMessage());
            return null;
        }
        foreach ($modinfo->get_instances() as $instances) {
            foreach ($instances as $mod) {
                if ($mod->modname === $modname && $instanceid == $mod->instance) {
                    return $mod;
                }
            }
        }
        return null;
    }

    /**
     * @param string $property Name of the property to get.
     * @param mixed $default Default value
     * @return mixed|null
     */
    protected function get_mod_property($property, $default = null) {
        $mod = self::get_mod();
        return $mod !== null && isset($mod->{$property}) ? $mod->{$property} : $default;
    }

    /**
     * event_result constructor.
     *
     * @param int $courseid The course id.
     * @param object $record the event record.
     */
    public function __construct(int $courseid, $record) {
        $this->courseid = $courseid;
        $this->record = (object) $record;
    }

    /**
     * @inheritDoc
     */
    public function get_link() {
        return $this->get_mod_property("url");
    }

    /**
     * @inheritDoc
     */
    public function get_name() {
        return $this->get_mod_property("name", $this->record->name);
    }

    /**
     * @inheritDoc
     */
    public function get_timestamp() {
        return $this->record->timestamp;
    }

    /**
     * Render the mod icon.
     *
     * @param renderer_base $output
     * @return string
     * @throws \coding_exception
     */
    protected function get_icon(renderer_base $output): string {
        // See \format_singleactivity::navigation_add_activity to view the icon logic.
        $icon = $this->get_mod_property("icon");
        $iconcomponent = $this->get_mod_property("iconcomponent");
        $modfullname = $this->get_mod_property("modfullname");

        // Get the mod icon if any.
        if ($icon && $iconcomponent) {
            $pixicon = new pix_icon($icon, $modfullname, $iconcomponent);
            return $output->render($pixicon);
        }

        // Fallback to the generic icon.
        if ($modfullname) {
            $pixicon = new pix_icon('icon', $modfullname, $this->get_mod_property("modname"));
            return $output->render($pixicon);
        }

        return "";
    }

    /**
     * @inheritDoc
     */
    public function export_for_template(renderer_base $output) {

        // Try to render the icon.
        try {
            $icon = $this->get_icon($output);
        } catch (\coding_exception $e) {
            debugging($e->getMessage());
            $icon = "";
        }

        return [
                "name" => $this->get_name(),
                "link" => $this->get_link(),
                "timestamp" => $this->get_timestamp(),
                "icon" => $icon
        ];
    }
}