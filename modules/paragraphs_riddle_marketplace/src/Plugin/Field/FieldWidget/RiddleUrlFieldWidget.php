<?php

namespace Drupal\paragraphs_riddle_marketplace\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'riddle_url_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "riddle_url_field_widget",
 *   label = @Translation("Riddle url field widget"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class RiddleUrlFieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'size' => 60,
      'placeholder' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: !size', array('!size' => $this->getSetting('size')));
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', array('@placeholder' => $this->getSetting('placeholder')));
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['uri'] = $element + array(
        '#type' => 'textfield',
        '#default_value' => isset($items[$delta]->uri) ? $items[$delta]->uri : NULL,
        '#size' => $this->getSetting('size'),
        '#placeholder' => $this->getSetting('placeholder'),
        '#maxlength' => $this->getFieldSetting('max_length'),
        '#autocomplete_route_name' => 'paragraphs_riddle_marketplace.riddle_url_autocomplete_controller_autocomplete',
      );

    return $element;
  }

}
