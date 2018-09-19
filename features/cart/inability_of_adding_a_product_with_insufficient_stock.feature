@setono_reserve_stock_cart
Feature: Inability to add a specific product to the cart when all items are reserved yet
  In order to buy only available products
  As a Visitor
  I want to be prevented from adding products which are already reserved

  Background:
    Given the store operates on a single channel in "United States"
    And the store has a product "T-Shirt banana" priced at "$12.54"

  @ui
  Scenario: Not being able to add a product to the cart when it is out of stock or reserved
    Given there are 5 units of product "T-Shirt banana" available in the inventory
    And I have added 5 products "T-Shirt banana" in the cart
    When I check this product's details
    Then I should see that it is out of stock
    And I should be unable to add it to the cart
