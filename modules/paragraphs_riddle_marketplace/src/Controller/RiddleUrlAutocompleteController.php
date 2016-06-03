<?php

namespace Drupal\paragraphs_riddle_marketplace\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\Component\Utility\Html;
use Drupal\hal\Encoder\JsonEncoder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonDecode;

/**
 * Class RiddleUrlAutocompleteController.
 *
 * @package Drupal\paragraphs_riddle_marketplace\Controller
 */
class RiddleUrlAutocompleteController extends ControllerBase {

  /**
   * Riddle URL template
   *
   * @var string
   */
  private static $riddleUrlTemplate = 'https://www.riddle.com/a/%%RIDDLE_UID%%';

  /**
   * @var \Drupal\riddle_marketplace\RiddleFeedServiceInterface
   */
  private $riddleFeedService;

  /**
   * RiddleUrlAutocompleteController constructor.
   *
   * @param $riddleFeedService
   */
  public function __construct($riddleFeedService) {
    $this->riddleFeedService = $riddleFeedService;
  }

  /**
   * @param ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('riddle_marketplace.feed')
    );
  }

  /**
   * Retrieves suggestions for Riddle URL autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing autocomplete suggestions.
   */
  public function autocomplete(Request $request) {
    $typedRiddleTitle = $request->query->get('q');

    $matches = array();
    $riddleFeed = $this->riddleFeedService->getFeed();
    foreach ($riddleFeed as $feedEntry) {
      if (stripos($feedEntry['title'], $typedRiddleTitle) !== FALSE) {
        $riddleUrl = str_replace(
          array('%%RIDDLE_UID%%'),
          array($feedEntry['uid']),
          static::$riddleUrlTemplate
        );
        $riddleTitle = Html::escape($feedEntry['title']);

        $matches[] = array('value' => $riddleUrl, 'label' => $riddleTitle);
      }
    }

    return new JsonResponse($matches);
  }

}
