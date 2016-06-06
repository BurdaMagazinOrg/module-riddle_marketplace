<?php

namespace Drupal\Tests\paragraphs_riddle_marketplace\Unit\Controller;

use Drupal\Tests\UnitTestCase;
use Drupal\paragraphs_riddle_marketplace\Controller\RiddleUrlAutocompleteController;

/**
 * Provides automated tests for the paragraphs_riddle_marketplace module.
 */
class RiddleUrlAutocompleteControllerTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "RiddleUrlAutocompleteController's controller functionality",
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
   * execute private/protected method
   *
   * @param $object
   * @param $methodName
   * @param array $parameters
   *
   * @return mixed
   */
  public function executeMethod(&$object, $methodName, array $parameters = array()) {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(TRUE);

    return $method->invokeArgs($object, $parameters);
  }

  /**
   * set value for private/protected parameter
   *
   * @param $object
   * @param $name
   * @param $value
   */
  public function setProperty(&$object, $name, $value) {
    $reflection = new \ReflectionClass(get_class($object));
    $property = $reflection->getProperty($name);
    $property->setAccessible(TRUE);

    $property->setValue($object, $value);
  }

  /**
   * Tests basic getMatchList method functionality.
   *
   * @dataProvider getMatchListDataProvider
   *
   * @param string $query
   * @param array $feed
   * @param array $expected
   */
  public function testGetMatchList($query, $feed, $expected) {
    $riddleFeedServiceMock = $this->getMock('Drupal\riddle_marketplace\RiddleFeedServiceInterface');
    $controller = new RiddleUrlAutocompleteController($riddleFeedServiceMock);

    $matchedList = $this->executeMethod($controller, 'getMatchList', array(
      $query,
      $feed
    ));
    $this->assertEquals($expected, $matchedList);
  }

  /**
   * Tests getMatchList method functionality with changed URL Template
   */
  public function testGetMatchListChangedRiddleUrlTemplate() {
    $riddleFeedServiceMock = $this->getMock('Drupal\riddle_marketplace\RiddleFeedServiceInterface');
    $controller = new RiddleUrlAutocompleteController($riddleFeedServiceMock);

    $this->setProperty($controller, 'riddleUrlTemplate', 'https://www.test.com/a/%%RIDDLE_UID%%');
    $matchedList = $this->executeMethod($controller, 'getMatchList', array(
      'title',
      array(
        array(
          'title' => 'test title',
          'uid' => '1',
        ),
      ),
    ));

    $this->assertEquals(
      array(
        array(
          'value' => 'https://www.test.com/a/1',
          'label' => 'test title',
        ),
      ),
      $matchedList
    );
  }

  /**
   * Data provider for getMatchList method related tests
   *
   * @return array
   */
  public function getMatchListDataProvider() {
    $feed = array(
      array(
        'title' => 'test title',
        'uid' => '1',
      ),
      array(
        'title' => 'TEST TITLE',
        'uid' => '2',
      ),
      array(
        'title' => 'TiTlE',
        'uid' => '3',
      ),
    );

    $matchResult = array(
      array(
        'value' => 'https://www.riddle.com/a/1',
        'label' => 'test title',
      ),
      array(
        'value' => 'https://www.riddle.com/a/2',
        'label' => 'TEST TITLE',
      ),
      array(
        'value' => 'https://www.riddle.com/a/3',
        'label' => 'TiTlE',
      ),
    );

    return array(
      array('', array(), array()),
      array('not_found', $feed, array()),
      array('title', $feed, $matchResult),
      array('test', $feed, array($matchResult[0], $matchResult[1])),
      array('test1', $feed, array()),
    );
  }

}
