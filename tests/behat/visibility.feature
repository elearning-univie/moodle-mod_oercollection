@mod @mod_oercollection
Feature: A teacher adjusts the visibility of the activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher   | 1 | teacher1@example.com |
      | student1 | Student   | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  @javascript
  Scenario: Teacher hides the content, then makes it visible again
    When I click on "Add an activity or resource" "button"
    And I follow "Add a new OER Collection"
    And I set the following fields to these values:
    | OER Collection name | example1 |
    | Description | example description |
    And I click on "id_showdescription" "checkbox"
    And I click on "Save and return to course" "button"
    Then I should see "example1"
    And I should see "example description"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "example1"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Open course index" "button"
    And I follow "example1"
    And I follow "Settings"
    And I click on "collapseElement-1" "button"
    And I set the following fields to these values:
    | Availability | Hide on course page |
    And I click on "Save and return to course" "button"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I should not see "example1"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "example1"
    And I follow "Settings"
    And I click on "collapseElement-1" "button"
    And I set the following fields to these values:
    | Availability | Show on course page |
    And I click on "Save and return to course" "button"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "example1"
