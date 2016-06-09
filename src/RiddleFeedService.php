<?php

namespace Drupal\riddle_marketplace;

use Drupal\Core\Datetime\DrupalDateTime;
use GuzzleHttp\Client;

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
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $moduleSettings;

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
   * Generic title used for Riddles without defined title
   * -> Riddle UID will be appended at end of it
   *
   * @var string
   */
  private $emptyTitlePrefix;

  /**
   * Riddle Feed Service
   *
   * Constructor
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheService
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configService
   */
  public function __construct($cacheService, $configService) {
    $this->cacheService = $cacheService;

    // fetch and store settings for this module
    $this->moduleSettings = $configService->get('riddle_marketplace.settings');

    // set Empty Title Prefix
    $this->emptyTitlePrefix = $this->moduleSettings->get('riddle_marketplace.empty_title_prefix');
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
      $riddleResponse = $this->fetchRiddleResponse();
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
    return $this->moduleSettings->get('riddle_marketplace.token');
  }

  /**
   * get Riddle API url from riddle_marketplace settings
   * and apply defined token
   *
   * @return mixed
   */
  private function getApiUrl() {
    return str_replace(
      array("%%TOKEN%%"),
      array($this->getToken()),
      $this->moduleSettings->get('riddle_marketplace.api_url')
    );
  }

  /**
   * fetch feed from Riddle API and return in JSON format (array)
   *
   * @return array
   */
  private function fetchRiddleResponse() {
    $url = $this->getApiUrl();

    $client = new Client();
    $result = $client->request('GET', $url);

    // return response from Riddle
    return json_decode($result->getBody(), TRUE);
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
        // skip invalid riddle feed entries
        if (!$this->isValidRiddleFeedEntry($riddleEntry)) {
          continue;
        }

        $feed[] = array(
          'title' => $this->getRiddleTitle($riddleEntry),
          'uid' => $riddleEntry['uid'],
        );
      }
    }

    return $feed;
  }

  /**
   * validation Riddle Feed Entry
   *
   * @param $riddleEntry
   * @return bool
   */
  private function isValidRiddleFeedEntry($riddleEntry) {
    if (
      empty($riddleEntry) || !is_array($riddleEntry)
      || empty($riddleEntry['data']) || !is_array($riddleEntry['data'])
      || empty($riddleEntry['uid'])
    ) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * returns Riddle Title from feed entry
   * - in case title is not defined use generic name
   *
   * @param $riddleEntry
   *
   * @return string
   */
  private function getRiddleTitle($riddleEntry) {
    if (!empty($riddleEntry['data']['title'])) {
      return $riddleEntry['data']['title'];
    }

    return $this->emptyTitlePrefix . $riddleEntry['uid'];
  }

  /**
   * get cache validity end timestamp
   *
   * @return mixed
   */
  private function getCacheExpireTimestamp() {
    /**
     * @var \DateTime $date
     */
    $date = new DrupalDateTime();
    $date->modify('+' . static::$cachePeriod);

    return $date->getTimestamp();
  }
}
