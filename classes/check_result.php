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
namespace block_course_checker;

defined('MOODLE_INTERNAL') || die();

use block_course_checker\model\check_result_interface;
use renderer_base;
use stdClass;

class check_result implements check_result_interface {

    /**
     * @var bool
     */
    protected $successful = true;
    /**
     * @var array
     */
    protected $details = [];

    /**
     * @var string|null
     */
    protected $link = null;

    /**
     * Tels if the check pass successfully or not
     *
     * @return bool
     */
    public function is_successful(): bool {
        return $this->successful;
    }

    /**
     * Return the details of a check
     * This is an array of \stdClass containing:
     * - success: bool Is the check successful
     * - message: string a message description
     * - link: string|null The link to fix this issue or a null string.
     *
     * @return array
     */
    public function get_details(): array {
        return $this->details;
    }

    /**
     * The link to solve this problem. Or a null string.
     *
     * @return string|null
     */
    public function get_link() {
        return $this->link;
    }

    /**
     * @param array $details
     * @return check_result
     */
    public function set_details(array $details = []) {
        $this->details = $details;

        return $this;
    }

    /**
     * @param $detail mixed
     * @return check_result
     */
    public function add_detail($detail) {
        $this->details[] = $detail;

        return $this;
    }

    /**
     * @param string|null $link
     * @return check_result
     */
    public function set_link(string $link = null) {
        $this->link = $link;

        return $this;
    }

    /**
     * @param bool $value
     * @return check_result
     */
    public function set_successful(bool $value){
        $this->successful = $value;

        return $this;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        return [
                "successful" => $this->successful,
                "details" => $this->details,
                "link" => $this->link
        ];
    }
}