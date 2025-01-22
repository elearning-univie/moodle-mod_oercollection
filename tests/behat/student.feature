@mod @mod_oercollection
Feature: A student uses the OERHub plugin

Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | id |
      | teacher1 | Teacher   | 1        | teacher1@example.com | 1 |
      | student1 | Student   | 1        | student1@example.com | 2 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

    @javascript
    Scenario: Student is able to see the content only on student page
        When I click on "Add an activity or resource" "button"
        And I follow "Add a new OER Collection"
        And I set the following fields to these values:
        | OER Collection name | example1            |
        | Description         | example description |
        And I click on "id_showdescription" "checkbox"
        And I click on "Save and display" "button"
        Then I should see "Overview"
        And I wait "10" seconds
        And I log out
        When I log in as "student1"
        And I am on "Course 1" course homepage
        And I should see "example1"
        And I should see "example description"
        And I follow "example1"
        And I should see "Find out more about the open educational resources displayed here."
        And I visit "/mod/oercollection/oercollectionteacherview.php?id=218000" 
        # Teacher view link may be environment specific
        Then I should not see "Overview"
