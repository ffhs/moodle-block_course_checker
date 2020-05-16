@block @block_course_checker
Feature: Enable block course checker in a course
  In order to enable the course checker block in a course
  As a teacher
  I can add course checker block to a course

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username |
      | teacher1 |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | C1     | editingteacher |

  Scenario: Add course checker as teacher on course page
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add the "Course checker" block
    Then I should see "This course has never been checked automatically" in the "Course checker" "block"
