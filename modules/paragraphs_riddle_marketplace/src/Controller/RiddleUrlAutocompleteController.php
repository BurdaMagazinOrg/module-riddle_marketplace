<?php

namespace Drupal\paragraphs_riddle_marketplace\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\Component\Utility\Html;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RiddleUrlAutocompleteController.
 *
 * @package Drupal\paragraphs_riddle_marketplace\Controller
 */
class RiddleUrlAutocompleteController extends ControllerBase {

  /**
   * Riddle URL template
   * -> it's defined in riddle_marketplace.settings->riddle_marketplace.url
   *
   * @var string
   */
  private $riddleUrlTemplate;

  /**
   * @var \Drupal\riddle_marketplace\RiddleFeedServiceInterface
   */
  private $riddleFeedService;

  /**
   * RiddleUrlAutocompleteController constructor.
   *
   * @param $riddleFeedService
   * @param $configService
   */
  public function __construct($riddleFeedService, $configService) {
    $this->riddleFeedService = $riddleFeedService;

    // fetch and store settings for this module
    $riddleSettings = $configService->get('riddle_marketplace.settings');
    $this->riddleUrlTemplate = $riddleSettings->get('riddle_marketplace.url');
  }

  /**
   * @param ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('riddle_marketplace.feed'),
      $container->get('config.factory')
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
    $riddleFeed = $this->riddleFeedService->getFeed();

    return new JsonResponse($this->getMatchList($typedRiddleTitle, $riddleFeed));
  }

  /**
   * get filtered list of Riddle Titles
   *
   * @param string $typedRiddleTitle
   *   Search text to match Riddle Title
   *
   * @param $riddleFeed
   *   Feed provided by Riddle Feed Service
   *
   * @return array List of matched Riddle Titles
   * List of matched Riddle Titles
   */
  private function getMatchList($typedRiddleTitle, $riddleFeed) {
    $matches = array();

    foreach ($riddleFeed as $feedEntry) {
      if (stripos($feedEntry['title'], $typedRiddleTitle) !== FALSE) {
        $riddleUrl = str_replace(
          array('%%RIDDLE_UID%%'),
          array($feedEntry['uid']),
          $this->riddleUrlTemplate
        );
        $riddleTitle = Html::escape($feedEntry['title']);

        $matches[] = array('value' => $riddleUrl, 'label' => $riddleTitle);
      }
    }

    return $matches;
  }

}
