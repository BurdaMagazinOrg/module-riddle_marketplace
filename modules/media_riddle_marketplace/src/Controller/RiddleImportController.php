<?php

namespace Drupal\media_riddle_marketplace\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\media_entity\Entity\Media;
use Drupal\media_riddle_marketplace\RiddleMediaServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RiddleImportController.
 *
 * @package Drupal\media_riddle_marketplace\Controller
 */
class RiddleImportController extends ControllerBase {

  /**
   * The riddle media service.
   *
   * @var \Drupal\media_riddle_marketplace\RiddleMediaServiceInterface
   */
  protected $riddleMediaService;

  /**
   * RiddleImportController constructor.
   *
   * @param \Drupal\media_riddle_marketplace\RiddleMediaServiceInterface $riddleMediaService
   *   The riddle media service.
   */
  public function __construct(RiddleMediaServiceInterface $riddleMediaService) {
    $this->riddleMediaService = $riddleMediaService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('media_riddle_marketplace')
    );
  }

  /**
   * The controller route.
   *
   * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Return a batch.
   */
  public function content() {

    $batch = [
      'title' => 'Exporting',
      'operations' => [],
    ];

    $mediaService = \Drupal::service('media_riddle_marketplace');

    foreach ($mediaService->getNewRiddles() as $bundle => $riddles) {
      /** @var \Drupal\media_entity\Entity\MediaBundle $bundle */
      $bundle = $this->entityTypeManager()->getStorage('media_bundle')
        ->load($bundle);
      $sourceField = $bundle->getTypeConfiguration()['source_field'];

      foreach ($riddles as $riddle) {

        $batch['operations'][] = [
          '\Drupal\media_riddle_marketplace\Controller\RiddleImportController::import',
          [
            [
              'bundle' => $bundle->id(),
              'source_field' => $sourceField,
              'riddleId' => $riddle,
            ],
          ],
        ];
      }
    }

    batch_set($batch);
    return batch_process('admin/content/media');
  }

  /**
   * The import function, used by batch.
   *
   * @param array $data
   *   Containing keys bundle, source_field and riddleId.
   */
  public static function import(array $data) {
    Media::create([
      'bundle' => $data['bundle'],
      $data['source_field'] => $data['riddleId'],
    ])->save();
  }

}
