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

use block_course_checker\plugin_manager;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/locallib.php');

/**
 * Form for editing HTML block instances.
 *
 * @package     block_course_checker
 * @copyright   2020 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_checker_edit_form extends block_edit_form {
    
    /**
     * @var array $checkerEditForms
     */
    protected $checkerEditForms = [];
    
    /**
     * block_course_checker_edit_form constructor.
     *
     * @param $actionurl
     * @param $block
     * @param $page
     */
    function __construct($actionurl, $block, $page) {
        $this->checkerEditForms = $this->get_checker_edit_forms();
        parent::__construct($actionurl, $block, $page);
    }
    
    /**
     * @return array
     */
    protected function get_checker_edit_forms(){
        $checkerEditForms = [];
        // Get checker plugins.
        $manager = plugin_manager::instance();
        foreach ($manager->get_checkers_plugins() as $checkername => $plugin) {
            $classname = $checkername . '_edit_form';
        
            // Include the checker's edit form file.
            $checkerEditForm = call_user_func(function() use ($checkername, $manager, $classname) {
                $editformfile = $manager->get_checker_edit_form_file($checkername);
                if (null == $editformfile) {
                    return null;
                }
                require($editformfile);
    
                // Create new edit form class, if the class exists.
                if (!class_exists($classname)) {
                    return null;
                }
                
                $checkerEditForm = new $classname();
                $checkerEditForm->checkername = $checkername;
                $checkerEditForm->truecheckername = get_string($checkername, 'block_course_checker');
            
                return $checkerEditForm;
            });
            
            if(null == $checkerEditForm){
                continue;
            }
            
            $checkerEditForms[] = $checkerEditForm;
        }
        return $checkerEditForms;
    }
    
    /**
     * @param object $mform
     * @return mixed|void
     */
    protected function specific_definition($mform) {
        foreach ($this->checkerEditForms as $checkerEditForm){
            // Load the checkers specific definition.
            $mform->addElement('header', $checkerEditForm->checkername. '_header', $checkerEditForm->truecheckername);
            return $checkerEditForm->specific_definition($mform);
        }
    }
}
