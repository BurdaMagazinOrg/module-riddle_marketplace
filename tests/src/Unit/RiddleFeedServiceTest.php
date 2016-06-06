<?php

namespace Drupal\Tests\riddle_marketplace\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\riddle_marketplace\RiddleFeedService;

/**
 * Provides automated tests for the riddle_marketplace module.
 * 
 * And RiddleFeedService class.
 */
class RiddleFeedServiceTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "RiddleFeedService's controller functionality",
      'description' => 'Test Unit for module riddle_marketplace and service RiddleFeedService.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();

    $container->set(
      'cache.riddle_feed',
      $this->getMock('\Drupal\Core\Cache\CacheBackendInterface')
    );

    \Drupal::setContainer($container);
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
   * Tests basic processRiddleResponse method functionality.
   *
   * @dataProvider processRiddleResponseDataProvider
   *
   * @param array $riddleResponse
   * @param array $expected
   */
  public function testProcessRiddleResponse($riddleResponse, $expected) {
    $feedService = new RiddleFeedService();

    $feed = $this->executeMethod(
      $feedService,
      'processRiddleResponse',
      array($riddleResponse)
    );

    $this->assertEquals($expected, $feed);
  }

  /**
   * Data provider for processRiddleResponse method related tests
   *
   * @return array
   */
  public function processRiddleResponseDataProvider() {
    $riddleFeed = array(
      array(
        'data' => array(
          'title' => ''
        ),
        'uid' => '1',
      ),
      array(
        'data' => array(
          'title' => 'Defined Title'
        ),
        'uid' => '2',
      ),
      array(
        'data' => array(
          'title' => 'No UID Title'
        ),
        'uid' => '',
      ),
    );

    $expectedResult = array(
      array(
        'title' => 'Riddle 1',
        'uid' => '1',
      ),
      array(
        'title' => 'Defined Title',
        'uid' => '2',
      ),
    );

    return array(
      array(array(), array()),
      array($riddleFeed, $expectedResult),
    );
  }

}
