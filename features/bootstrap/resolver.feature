Feature: Resolver

  Scenario: Should return the requested method
    Given there is a method named "my-method"
    When I ask for "my-method" method
    Then I should have "my-method" method

  Scenario: Should throw an exception if requested method does not exist
    When I ask for "not-existing-method" method
    Then I should have a JSON-RPC exception with code "-32601"
