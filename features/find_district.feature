# @api
# Feature: Anyone can enter an address to look up its council district
#
# @javascript @in-progress
# Scenario: Enter an address to find its district
#     Given I am on "/council-district-5"
#     When I fill in "Start typing an address to find its council district" with "200 E Main st"
#     And I press the "enter" key in the "Start typing an address to find its council district" field
#     And I wait for AJAX to finish
#     # And I press the "i" key in the "Start typing an address to find its council district" field
#     Then I should see "200 E MAIN ST"
