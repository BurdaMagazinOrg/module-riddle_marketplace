<?php

namespace Drupal\riddle_marketplace;

use Drupal\Core\Datetime\DrupalDateTime;

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
   *
   *
   * Constructor
   */
  public function __construct() {
    $this->cacheService = \Drupal::cache('riddle_feed');
  }

  /**
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
      $feed = $this->fetchFeed($token);

      $date = new DrupalDateTime();
      $date->modify('+' . static::$cachePeriod);

      $this->cacheService->set($cacheId, $feed, $date->getTimestamp());
    }

    return $feed;
  }

  /**
   * @return mixed
   */
  private function getToken() {
    $config = \Drupal::service('config.factory')
      ->getEditable('riddle_marketplace.settings');

    return $config->get('riddle_marketplace.token');
  }

  /**
   * fetch feed from Riddle API and return only relevant data
   * - currently: uid, title
   *
   * @param $token
   * @return array
   */
  private function fetchFeed($token) {
    $url = 'https://www.riddle.com/apiv3/item/token/' . $token . "?client=d8";

    $ch = curl_init();
    $timeout = 0; // set to zero for no timeout
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $file_contents = curl_exec($ch);
    curl_close($ch);

    // process response from Riddle
    $riddleResponse = json_decode($file_contents, TRUE);

    $feed = array();
    if (!empty($riddleResponse) && is_array($riddleResponse)) {
      foreach ($riddleResponse as $riddleEntry) {
        $feed[] = array(
          'title' => $riddleEntry['data']['title'],
          'uid' => $riddleEntry['uid'],
        );
      }
    }

    return $feed;
  }

}
