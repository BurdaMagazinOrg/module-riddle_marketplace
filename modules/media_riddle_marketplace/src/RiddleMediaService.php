<?php

namespace Drupal\media_riddle_marketplace;

use Drupal\riddle_marketplace\RiddleFeedServiceInterface;

/**
 * Class RiddleFeedService.
 *
 * @package Drupal\riddle_marketplace
 */
class RiddleMediaService {


  private $feedService;

  /**
   * Riddle Media Service.
   *
   * Constructor.
   *
   * @param \Drupal\riddle_marketplace\RiddleFeedServiceInterface $feedService
   *   Riddle Feed service.
   */
  public function __construct(RiddleFeedServiceInterface $feedService) {
    $this->feedService = $feedService;
  }

  public function createMediaEntities() {

    $feed = $this->feedService->getFeed();


    $riddleIds = array_column($feed, 'uid');

    /** @var \Drupal\media_entity\MediaBundleInterface[] $riddleBundles */
    $riddleBundles = \Drupal::entityTypeManager()
      ->getStorage('media_bundle')
      ->loadByProperties([
        'type' => 'riddle_marketplace',
      ]);

    foreach ($riddleBundles as $riddleBundle) {

      $sourceField = $riddleBundle->getTypeConfiguration()['source_field'];

      $riddles = \Drupal::entityTypeManager()
        ->getStorage('media')
        ->loadByProperties([
          'bundle' => $riddleBundle->id(),
          $sourceField => $riddleIds,
        ]);

      if (count($riddles) == count($riddleIds)) {
        continue;
      }


      $existingRiddles = [];
      foreach ($riddles as $riddle) {
        $property_name = $riddle->{$sourceField}->first()->mainPropertyName();
        $existingRiddles[] = $riddle->{$sourceField}->{$property_name};
      }

      $riddlesToCreate = array_diff($riddleIds, $existingRiddles);
      foreach ($riddlesToCreate as $riddleId) {
        \Drupal\media_entity\Entity\Media::create([
          'bundle' => $riddleBundle->id(),
          $sourceField => $riddleId,
        ])->save();
      }
    }


  }

}
