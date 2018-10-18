Feature: Method Resolver

  Scenario: Should return the requested method from container
    Given there is a "getDummy" service for "getDummy" JSON-RPC method
    When I ask for "getDummy" JSON-RPC method
    Then I should have "getDummy" JSON-RPC method

  Scenario: Should return null if requested method does not exist
    When I ask for "not-existing-method" JSON-RPC method
    Then I should have a null JSON-RPC method
