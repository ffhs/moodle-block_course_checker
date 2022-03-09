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
 * This class is a helper to retrieve detail information about a checker
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker;

use block_course_checker\model\check_result_interface;

class check_result implements check_result_interface {

    /**
     * @var bool
     */
    protected $successful = true;

    /**
     * @var string
     */
    protected $timestamp = null;

    /**
     * @var array
     */
    protected $details = [];

    /**
     * @var string|null
     */
    protected $link = null;

    /**
     * Tells if the check pass successfully or not
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
     * The timestamp for the checker.
     *
     * @return int|null
     */
    public function get_timestamp() {
        return $this->timestamp;
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
    public function set_successful(bool $value) {
        $this->successful = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return check_result
     */
    public function set_timestamp($value) {
        $this->timestamp = $value;

        return $this;
    }

    /**
     * Adds a timestamp to the check result
     */
    public function add_timestamp() {
        $this->timestamp = date("U");
    }


    /**
     * @inheritDoc
     */
    public function export_for_template(\renderer_base $output) {
        return [
                "successful" => $this->successful,
                "details" => $this->details,
                "link" => $this->link,
                "timestamp" => $this->timestamp
        ];
    }
}
