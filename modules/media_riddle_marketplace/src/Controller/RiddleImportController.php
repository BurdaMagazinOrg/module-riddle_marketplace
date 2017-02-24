<?php

namespace Drupal\media_riddle_marketplace\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\media_entity\Entity\Media;
use Drupal\media_riddle_marketplace\RiddleMediaServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The current request.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * RiddleImportController constructor.
   *
   * @param \Drupal\media_riddle_marketplace\RiddleMediaServiceInterface $riddleMediaService
   *   The riddle media service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(RiddleMediaServiceInterface $riddleMediaService, RequestStack $requestStack) {
    $this->riddleMediaService = $riddleMediaService;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('media_riddle_marketplace'),
      $container->get('request_stack')
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

    foreach ($this->riddleMediaService->getNewRiddles() as $bundle => $riddles) {
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
              'riddle_id' => $riddle,
            ],
          ],
        ];
      }
    }

    batch_set($batch);
    return batch_process($this->request->server->get('HTTP_REFERER'));
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
      $data['source_field'] => $data['riddle_id'],
    ])->save();
  }

}
