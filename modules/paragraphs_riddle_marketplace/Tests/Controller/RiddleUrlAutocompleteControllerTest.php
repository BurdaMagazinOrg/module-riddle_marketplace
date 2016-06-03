<?php

namespace Drupal\paragraphs_riddle_marketplace\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the paragraphs_riddle_marketplace module.
 */
class RiddleUrlAutocompleteControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "paragraphs_riddle_marketplace RiddleUrlAutocompleteController's controller functionality",
      'description' => 'Test Unit for module paragraphs_riddle_marketplace and controller RiddleUrlAutocompleteController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests paragraphs_riddle_marketplace functionality.
   */
  public function testRiddleUrlAutocompleteController() {
    // Check that the basic functions of module paragraphs_riddle_marketplace.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
