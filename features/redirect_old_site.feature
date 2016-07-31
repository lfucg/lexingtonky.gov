@api
Feature: Redirect paths from old site

Scenario: Traffic ticker
  Given I visit "/index.aspx?page=1918"
  Then I should see "Real-time traffic ticker"
  And the url should match "traffic-ticker"
