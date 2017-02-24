<?php

namespace Drupal\media_riddle_marketplace\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'twitter_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "riddle_embed",
 *   label = @Translation("Riddle embed"),
 *   field_types = {
 *     "string", "string_long"
 *   }
 * )
 */
class RiddleEmbedFormatter extends FormatterBase {

  /**
   * Extracts the embed code from a field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   *
   * @return string|null
   *   The embed code, or NULL if the field type is not supported.
   */
  protected function getEmbedCode(FieldItemInterface $item) {
    switch ($item->getFieldDefinition()->getType()) {

      case 'string':
      case 'string_long':
        return $item->value;

      default:
        break;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {

      if ($code = $this->getEmbedCode($item)) {
        $markup = '<div class="riddle_target" data-rid-id="' . $code . '" data-fg="#1486cd" data-bg="#FFFFFF" style="margin:0 auto;max-width:100%;width:640px;"><iframe style="width:100%;height:300px;border:1px solid #cfcfcf;" src="https://www.riddle.com/a/' . $code . '"></iframe></div>';

        $element[$delta] = [
          '#type' => 'inline_template',
          '#template' => $markup,
          '#attached' => [
            'library' => [
              'riddle_marketplace/riddle.embed',
            ],
          ],
        ];
      }

    }

    return $element;
  }

}
