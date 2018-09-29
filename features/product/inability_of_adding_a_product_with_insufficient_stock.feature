@setono_reserve_stock_cart
Feature: Inability to add a specific product to the cart when all items are reserved yet
  In order to buy only available products
  As a Visitor
  I want to be prevented from adding products which are already reserved

  Background:
    Given the store operates on a single channel in "United States"
    And the store has a product "T-shirt banana" priced at "$12.54"
    And this product is tracked by the inventory

  @ui
  Scenario: Not being able to add a product to the cart when it is out of stock or reserved
    Given there are 5 units of product "T-shirt banana" available in the inventory
    And I have added 5 products "T-shirt banana" in the cart
    Then I should be notified that the product has been successfully added
    When I check this product's details
    Then I should see that it is out of stock
    And I should be unable to add it to the cart
