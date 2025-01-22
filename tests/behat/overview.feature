@mod @mod_oercollection
Feature: A teacher opens the plugin homepage for examination

Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "Add an activity or resource" "button"
    And I follow "Add a new OER Collection"
    And I set the following fields to these values:
    | OER Collection name | example1            |
    | Description         | example description |
    And I click on "id_showdescription" "checkbox"
    And I click on "Save and return to course" "button"
    And I should see "example1"
    Then I click on "Open course index" "button" 
        
    @javascript
    Scenario: the plugin homepage loads as intended
        When I follow "example1"
        Then I should see "Overview"
        And I should see "What are OER and what can you use them for?"
        And I should see "Edit resources"
        And I should see "Student preview"
        And I should see "Search in OERHub"
        And I should see "Available resources"

    @javascript
    Scenario: the plugin homepage resource button works
        When I follow "example1"
        #And I click on "Edit resources" "button"
        And I follow "Edit resources"
        Then I should see "With selected..."

    @javascript
    Scenario: the plugin homepage student preview button works
        When I follow "example1"
#        And I click on "Student preview" "button"
        And I follow "Student preview"
        Then I should see "Resources"

    @javascript
    Scenario: the plugin homepage Search button works
        When I follow "example1"
        # And I click on "Search in OERHub" "button"
        And I follow "Search in OERHub"
        Then I should see "Search:"


    