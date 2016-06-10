<?php

namespace Drupal\Tests\paragraphs_riddle_marketplace\Unit\Controller;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\Config\Config;
use Drupal\paragraphs_riddle_marketplace\Controller\RiddleUrlAutocompleteController;

/**
 * Provides automated tests for the paragraphs_riddle_marketplace module.
 *
 * And RiddleUrlAutocompleteController class.
 *
 * @group riddle_marketplace
 */
class RiddleUrlAutocompleteControllerTest extends UnitTestCase {

  /**
   * riddle marketplace Feed Service Mock
   */
  protected $riddleFeedServiceMock;

  /**
   * Config Factory Mock -> provides base configuration required for Testing
   */
  protected $configFactoryMock;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "RiddleUrlAutocompleteController's controller functionality",
      'description' => 'Test Unit for module paragraphs_riddle_marketplace and controller RiddleUrlAutocompleteController.',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->riddleFeedServiceMock = $this->getMock('Drupal\riddle_marketplace\RiddleFeedServiceInterface');
    $this->setUpConfigFactoryMock();
  }

  /**
   * setup Config relevant for proper functioning of tests
   */
  protected function setUpConfigFactoryMock() {
    $this->configFactoryMock = $this->getMock('\Drupal\Core\Config\ConfigFactoryInterface');

    $storage = $this->getMock('Drupal\Core\Config\StorageInterface');
    $event_dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    $typed_config = $this->getMock('Drupal\Core\Config\TypedConfigManagerInterface');
    $config = new Config('riddle_marketplace', $storage, $event_dispatcher, $typed_config);
    $config->set('riddle_marketplace.url', 'https://www.riddle.com/a/%%RIDDLE_UID%%');

    $this->configFactoryMock->expects($this->once())
      ->method('get')
      ->willReturn($config);
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
    $controller = new RiddleUrlAutocompleteController($this->riddleFeedServiceMock, $this->configFactoryMock);

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
    $controller = new RiddleUrlAutocompleteController($this->riddleFeedServiceMock, $this->configFactoryMock);

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
