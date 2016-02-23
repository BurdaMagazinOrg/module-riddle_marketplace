<?php

/**
 * @file
 * Contains \Drupal\riddle\Form\SettingsForm.
 */

namespace Drupal\riddle\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that configures riddle settings.
 */
class SettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'riddle_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->config('riddle.settings');

    $form['token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Riddle token'),
      '#description' => $this->t('Goto Riddle.com and get a token from the Account->Token page (you may need to reset to get the first token)'),
      '#default_value' => $settings->get('riddle.token'),
    );


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $values = $form_state->getValues();
    $config =$this->configFactory()->getEditable('riddle.settings');
    $config->set('riddle.token', $values['token'])->save();
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'riddle.settings',
    ];
  }
}
