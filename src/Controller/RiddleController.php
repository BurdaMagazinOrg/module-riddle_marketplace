<?php
/**
 * @file
 * Contains \Drupal\riddle\Controller\RiddleController.
 */

namespace Drupal\riddle\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class RiddleController extends ControllerBase {


  public function CreateRiddle() {
    return $this->getRiddleLink('creation');
  }

  public function GetRiddleLink($page) {
    $token = $this->GetToken();
    $domain = "https://www.riddle.com";
    $url = $domain . '/LoginByToken?token=' . $token . '&redirect=' . $page. "&client=d8";
    return array(
      '#type' => 'markup',
      '#markup' => '<iframe src="' . $url . '">',
      "#style_name" => 'riddle_iframe',
      '#allowed_tags' => array('iframe'),
      '#attached' => array(
        'library' => array(
          'riddle/riddle'
        )
      )
    );
  }

  public function GetToken() {
    $config = \Drupal::service('config.factory')->getEditable(
      'riddle.settings'
    );
    $token = $config->get('riddle.token');
    return $token;
  }

  public function ListRiddles() {
    return $this->GetRiddleLink('riddles');
  }

  // public link to save token
  // TODO: hook this up to the admin config screen
  public function SetToken($token) {
    $this->PersistToken($token);
    $response = "Riddle access token set to " . $this->GetToken();
    return new JsonResponse($response);

  }

  public function PersistToken($token) {
    $config = \Drupal::service('config.factory')->getEditable(
      'riddle.settings'
    );
    $config->set('riddle.token', $token)->save();
  }
}

?>
