@api
Feature: Redirect paths from old site

Scenario: Traffic ticker
  Given I visit "/index.aspx?page=1918"
  Then I should see "Real-time traffic ticker"
  And the url should match "traffic-ticker"

Scenario: External URL
  Given I visit "/dem"
  Then I should see "emergency"

Scenario: Legacy document is redirected to previous.lexingtonky.gov
  Given I visit "/Modules/ShowDocument.aspx?documentid=20877"
  Then the response status code should be 200
