<?php
namespace MailPoet\Newsletter\Renderer\Blocks;

use MailPoet\Newsletter\Renderer\Columns\ColumnsHelper;
use MailPoet\Newsletter\Renderer\StylesHelper;

class Button {
  static function render($element, $column_count) {
    $element['styles']['block']['width'] = self::calculateWidth($element, $column_count);
    $element['styles']['block']['height'] = self::calculateHeight($element);
    $template = '
      <tr>
        <td class="mailpoet_padded" valign="top">
          <div>
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing:0;mso-table-lspace:0;mso-table-rspace:0;">
              <tr>
                <td class="mailpoet_button-container" style="padding:8px 20px;text-align:' . $element['styles']['block']['textAlign'] . ';"><!--[if mso]>
                  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                    href="' . $element['url'] . '"
                    style="height:' . $element['styles']['block']['height'] . ';
                           width:' . $element['styles']['block']['width'] . ';
                           v-text-anchor:middle;"
                    arcsize="' . round($element['styles']['block']['borderRadius'] / $element['styles']['block']['height'] * 100) . '%"
                    strokeweight="1px"
                    strokecolor="' . $element['styles']['block']['borderColor'] . '"
                    fillcolor="' . $element['styles']['block']['backgroundColor'] . '">
                  <w:anchorlock/>
                  <center style="color:' . $element['styles']['block']['fontColor'] . ';
                    font-family:' . $element['styles']['block']['fontFamily'] . ';
                    font-size:' . $element['styles']['block']['fontSize'] . ';
                    font-weight:bold;">' . $element['text'] . '
                  </center>
                  </v:roundrect>
                  <![endif]--><a class="mailpoet_button" href="' . $element['url'] . '" style="display:inline-block;-webkit-text-size-adjust:none;mso-hide:all;text-decoration:none!important;text-align:center;' . StylesHelper::getBlockStyles($element, $exclude = array('textAlign')) . '"> ' . $element['text'] . '
                </td>
              </tr>
            </table>
          </div>
        </td>
      </tr>';
    return $template;
  }

  static function calculateWidth($element, $column_count) {
    $column_width = ColumnsHelper::columnWidth($column_count);
    $column_width = $column_width - (StylesHelper::$padding_width * 2);
    $border_width = (int) $element['styles']['block']['borderWidth'];
    $button_width = (int) $element['styles']['block']['width'];
    $button_width = ($button_width > $column_width) ?
      $column_width :
      $button_width;
    $button_width = $button_width - (2 * $border_width) . 'px';
    return $button_width;
  }

  static function calculateHeight($element) {
    $button_height = (int) $element['styles']['block']['lineHeight'];
    $button_height = $button_height - (2 * $button_height) . 'px';
    return $button_height;
  }
}