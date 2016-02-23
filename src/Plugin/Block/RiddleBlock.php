<?php
namespace Drupal\riddle\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Riddle' Block which can show the editor
 *
 * @Block(
 *   id = "riddle_block",
 *   admin_label = @Translation("Riddle block"),
 * )
 */
class RiddleBlock extends BlockBase implements BlockPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (!empty($config['ID'])) {
      $ID = $config['ID'];
    }
    else {
      $ID = $this->t('to no one');
    }

    $response = array(
      '#type' => 'markup',
      '#markup' => '<iframe style="width:100%; height:2000px" src="https://www.riddle.com/creation/?token=' . $ID . '&client=d8" />',
      "#style_name" => 'riddle_iframe',
      '#allowed_tags' => array('iframe'),
      '#attached' => array(
        'library' => array(
          'riddle/riddle'
        )
      )
    );

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['riddle_block_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Riddle ID'),
      '#description' => $this->t(
        'Which Riddle access token do you want to use?'
      ),
      '#default_value' => isset($config['ID']) ? $config['ID'] : ''
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue(
      'ID',
      $form_state->getValue('riddle_block_name')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('riddle.settings');
    return array(
      'ID' => $default_config->get('riddle.ID')
    );
  }

  function riddle_block_view_alter(array &$build, BlockPluginInterface $block) {
    // We'll search for the string 'riddle'.
    $definition = $block->getPluginDefinition();
    if ((!empty($build['#configuration']['label']) && stristr(
          $build['#configuration']['label'],
          'riddle'
        )) || (!empty($definition['subject']) && stristr(
          $definition['subject'],
          'riddle'
        ))
    ) {
      // This will uppercase the block title.
      $build['#configuration']['label'] = Unicode::strtoupper(
        $build['#configuration']['label']
      );
    }
  }
}

?>