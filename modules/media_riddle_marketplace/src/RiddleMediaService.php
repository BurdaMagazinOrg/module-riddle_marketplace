<?php

namespace Drupal\media_riddle_marketplace;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\riddle_marketplace\RiddleFeedServiceInterface;

/**
 * Class RiddleFeedService.
 *
 * @package Drupal\riddle_marketplace
 */
class RiddleMediaService implements RiddleMediaServiceInterface {

  /**
   * The riddle feed service.
   *
   * @var \Drupal\riddle_marketplace\RiddleFeedServiceInterface
   */
  protected $feedService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Riddle Media Service.
   *
   * Constructor.
   *
   * @param \Drupal\riddle_marketplace\RiddleFeedServiceInterface $feedService
   *   Riddle Feed service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(RiddleFeedServiceInterface $feedService, EntityTypeManagerInterface $entityTypeManager) {
    $this->feedService = $feedService;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function createMediaEntities() {

    foreach ($this->getNewRiddles() as $bundle => $riddles) {
      /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
      $bundle = $this->entityTypeManager->getStorage('media_bundle')
        ->load($bundle);
      $sourceField = $bundle->getTypeConfiguration()['source_field'];

      foreach ($riddles as $riddle) {
        $this->entityTypeManager->getStorage('media')->create([
          'bundle' => $bundle->id(),
          $sourceField => $riddle,
        ])->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getNewRiddles() {

    $feed = $this->feedService->getFeed();

    $riddleIds = array_column($feed, 'uid');

    /** @var \Drupal\media_entity\MediaBundleInterface[] $riddleBundles */
    $riddleBundles = $this->entityTypeManager->getStorage('media_bundle')
      ->loadByProperties([
        'type' => 'riddle_marketplace',
      ]);

    $newRiddles = [];
    foreach ($riddleBundles as $riddleBundle) {

      $sourceField = $riddleBundle->getTypeConfiguration()['source_field'];

      $riddles = $this->entityTypeManager->getStorage('media')
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

      $newRiddles[$riddleBundle->id()] = array_diff($riddleIds, $existingRiddles);
    }

    return $newRiddles;
  }

}
