Feature: Method Resolver

  Scenario: Should return the requested method from container
    Given there is a "getDummy" service for "getDummy" JSON-RPC method
    When I ask for "getDummy" JSON-RPC method
    Then I should have "getDummy" JSON-RPC method

  Scenario: Should return null if requested method does not exist
    When I ask for "not-existing-method" JSON-RPC method
    Then I should have a null JSON-RPC method

  Scenario: Should return the requested method  from container when service name resolver is used
    Given there is a "a.dummy.method.service" service for "getDummy" JSON-RPC method
    And ServiceNameResolver will resolve "getDummy" JSON-RPC method to "a.dummy.method.service" service
    When I ask for "getDummy" JSON-RPC method
    Then I should have "getDummy" JSON-RPC method
