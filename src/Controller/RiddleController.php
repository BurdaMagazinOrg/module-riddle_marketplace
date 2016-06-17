<?php

namespace Drupal\riddle_marketplace\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class RiddleController.
 *
 * @package Drupal\riddle_marketplace\Controller
 */
class RiddleController extends ControllerBase {

  /**
   * Create Render array for riddle iframe.
   *
   * @return array
   *   Render array for Riddle Iframe.
   */
  public function riddleIframe() {

    $config = \Drupal::service('config.factory')->getEditable(
      'riddle_marketplace.settings'
    );
    $token = $config->get('riddle_marketplace.token');

    return [
      '#theme' => 'riddle_backend',
      '#token' => $token,
    ];
  }

}
