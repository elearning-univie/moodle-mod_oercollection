@mod @mod_oercollection
Feature: A teacher uses the Search tab in OERHub

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
        
    # requires local mock data or a mock server
    @javascript
    Scenario: the OERHub search works as intended
        When I click on "Open course index" "button" 
        And I follow "example1"
        And I follow "Search in OERHub"
        And I set the following fields to these values:
        | searchstring | Video from 2020 |
        And I click on "Search" "button"
        And I should see "Video from 2020"
        And I follow "Open resource"
        Then the url should be "https://phaidra.univie.ac.at/detail/o:1134317"
  
    