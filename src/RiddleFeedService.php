<?php

namespace Drupal\riddle_marketplace;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Class RiddleFeedService.
 *
 * @package Drupal\riddle_marketplace
 */
class RiddleFeedService implements RiddleFeedServiceInterface {

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheService;

  /**
   * cache validity period, mainly used to reduce number of requests to Riddle
   * and to keep fast response for user
   *
   * - period has to be valid for DrupalDateTime::modify method
   * - period should be less then time required to add new Riddle entry, so that client after adding entry in Riddle can find it in search here
   *
   * @var string
   */
  private static $cachePeriod = '30 seconds';

  /**
   * Generic name used for Riddles without defined title
   *
   * @var string
   */
  private static $genericNamePrefix = 'Riddle ';

  /**
   * Riddle Feed Service
   *
   * Constructor
   */
  public function __construct() {
    $this->cacheService = \Drupal::cache('riddle_feed');
  }

  /**
   * {@inheritdoc}
   *
   * @return array|null
   */
  public function getFeed() {
    $feed = NULL;

    $token = $this->getToken();
    $cacheId = 'riddle_marketplace.feed:' . $token;

    if ($cache = $this->cacheService->get($cacheId)) {
      $feed = $cache->data;
    }
    else {
      $riddleResponse = $this->fetchRiddleResponse($token);
      $feed = $this->processRiddleResponse($riddleResponse);
      $cacheExpire = $this->getCacheExpireTimestamp();

      $this->cacheService->set($cacheId, $feed, $cacheExpire);
    }

    return $feed;
  }

  /**
   * get Riddle Token from riddle_marketplace settings
   *
   * @return mixed
   */
  private function getToken() {
    $config = \Drupal::service('config.factory')
      ->getEditable('riddle_marketplace.settings');

    return $config->get('riddle_marketplace.token');
  }

  /**
   * fetch feed from Riddle API and return in JSON format (array)
   *
   * @param $token
   *
   * @return array
   */
  private function fetchRiddleResponse($token) {
    $url = 'https://www.riddle.com/apiv3/item/token/' . $token . "?client=d8";

    $ch = curl_init();
    $timeout = 0; // set to zero for no timeout
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $result = curl_exec($ch);
    curl_close($ch);

    // return response from Riddle
    return json_decode($result, TRUE);
  }

  /**
   * process response from Riddle API (JSON format)
   * and return only relevant data for internal feed cached storage
   *
   * - currently: uid, title
   *
   * @param array|NULL $riddleResponse
   *
   * @return array
   */
  private function processRiddleResponse($riddleResponse) {
    $feed = array();

    if (!empty($riddleResponse) && is_array($riddleResponse)) {
      foreach ($riddleResponse as $riddleEntry) {

        // check is entry valid - TODO: improve validation
        if (
          empty($riddleEntry) || !is_array($riddleEntry)
          || empty($riddleEntry['data']) || !is_array($riddleEntry['data'])
          || empty($riddleEntry['uid'])
        ) {
          continue;
        }

        // get title if it's defined - otherwise use Generic names
        if (!empty($riddleEntry['data']['title'])) {
          $title = $riddleEntry['data']['title'];
        }
        else {
          $title = static::$genericNamePrefix . $riddleEntry['uid'];
        }

        $feed[] = array(
          'title' => $title,
          'uid' => $riddleEntry['uid'],
        );
      }
    }

    return $feed;
  }

  /**
   * get cache validity end timestamp
   *
   * @return mixed
   */
  private function getCacheExpireTimestamp() {
    $date = new DrupalDateTime();
    $date->modify('+' . static::$cachePeriod);

    return $date->getTimestamp();
  }
}
