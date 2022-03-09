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
 * Interface containing the activity modnames in Moodle.
 *
 * @package    block_course_checker
 * @copyright  2020 FFHS <christoph.karlen@ffhs.ch>
 * @author     2020 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_checker\model;

interface mod_type_interface {
    // Module name for assignment activity in Moodle.
    const MOD_TYPE_ASSIGN = 'assign';
    // Module name for attendance in Moodle.
    const MOD_TYPE_ATTENDANCE = 'attendance';
    // Module name for book in Moodle.
    const MOD_TYPE_BOOK = 'book';
    // Module name for chat in Moodle.
    const MOD_TYPE_CHAT = 'chat';
    // Module name for choice in Moodle.
    const MOD_TYPE_CHOICE = 'choice';
    // Module name for choicegroup in Moodle.
    const MOD_TYPE_CHOICEGROUP = 'choicegroup';
    // Module name for database in Moodle.
    const MOD_TYPE_DATA = 'data';
    // Module name for feedback in Moodle.
    const MOD_TYPE_FEEDBACK = 'feedback';
    // Module name for folder in Moodle.
    const MOD_TYPE_FOLDER = 'folder';
    // Module name for forum in Moodle.
    const MOD_TYPE_FORUM = 'forum';
    // Module name for glossary in Moodle.
    const MOD_TYPE_GLOSSARY = 'glossary';
    // Module name for imscp in Moodle.
    const MOD_TYPE_IMSCP = 'imscp';
    // Module name for journal in Moodle.
    const MOD_TYPE_JOURNAL = 'journal';
    // Module name for label in Moodle.
    const MOD_TYPE_LABEL = 'label';
    // Module name for lesson in Moodle.
    const MOD_TYPE_LESSON = 'lesson';
    // Module name for lti in Moodle.
    const MOD_TYPE_LTI = 'lti';
    // Module name for page in Moodle.
    const MOD_TYPE_PAGE = 'page';
    // Module name for questionnaire in Moodle.
    const MOD_TYPE_QUESTIONNAIRE = 'questionnaire';
    // Module name for quiz in Moodle.
    const MOD_TYPE_QUIZ = 'quiz';
    // Module name for resource in Moodle.
    const MOD_TYPE_RESOURCE = 'resource';
    // Module name for url in Moodle.
    const MOD_TYPE_URL = 'url';
    // Module name for scheduler in Moodle.
    const MOD_TYPE_SCHEDULER = 'scheduler';
    // Module name for scorm in Moodle.
    const MOD_TYPE_SCORM = 'scorm';
    // Module name for survey in Moodle.
    const MOD_TYPE_SURVEY = 'survey';
    // Module name for wiki in Moodle.
    const MOD_TYPE_WIKI = 'wiki';
    // Module name for workshop in Moodle.
    const MOD_TYPE_WORKSHOP = 'workshop';
}
