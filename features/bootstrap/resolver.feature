Feature: Resolver

  Scenario: Should return the requested method
    Given there is a service method named "my-method"
    When I ask for "my-method" method
    Then I should have "my-method" method

  Scenario: Should throw an exception if requested method does not exist
    When I ask for "not-existing-method" method
    Then I should have a JSON-RPC exception with code "-32601"

  Scenario: Should return the requested method when service name prefixer is used
    Given there is a service name resolver with prefix "my-prefix."
    And there is a service method named "my-method" with prefix "my-prefix."
    When I ask for "my-method" method
    Then I should have "my-method" method
