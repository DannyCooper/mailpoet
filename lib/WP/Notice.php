<?php

namespace MailPoet\WP;

use MailPoet\WP\Functions as WPFunctions;

class Notice {

  const TYPE_ERROR = 'error';
  const TYPE_WARNING = 'warning';
  const TYPE_SUCCESS = 'success';
  const TYPE_INFO = 'info';

  private $type;
  private $message;
  private $classes;
  private $data_notice_name;
  private $render_in_paragraph;

  public function __construct($type, $message, $classes = '', $dataNoticeName = '', $renderInParagraph = true) {
    $this->type = $type;
    $this->message = $message;
    $this->classes = $classes;
    $this->dataNoticeName = $dataNoticeName;
    $this->renderInParagraph = $renderInParagraph;
  }

  public function getMessage() {
    return $this->message;
  }

  public static function displayError($message, $classes = '', $dataNoticeName = '', $renderInParagraph = true, $showErrorTitle = true) {
    if ($showErrorTitle) {
      $message = sprintf(
        "<b>%s </b> %s",
        WPFunctions::get()->__('MailPoet Error:', 'mailpoet'),
        $message
      );
    }
    self::createNotice(self::TYPE_ERROR, $message, $classes, $dataNoticeName, $renderInParagraph);
  }

  public static function displayWarning($message, $classes = '', $dataNoticeName = '', $renderInParagraph = true) {
    return self::createNotice(self::TYPE_WARNING, $message, $classes, $dataNoticeName, $renderInParagraph);
  }

  public static function displaySuccess($message, $classes = '', $dataNoticeName = '', $renderInParagraph = true) {
    self::createNotice(self::TYPE_SUCCESS, $message, $classes, $dataNoticeName, $renderInParagraph);
  }

  public static function displayInfo($message, $classes = '', $dataNoticeName = '', $renderInParagraph = true) {
    self::createNotice(self::TYPE_INFO, $message, $classes, $dataNoticeName, $renderInParagraph);
  }

  protected static function createNotice($type, $message, $classes, $dataNoticeName, $renderInParagraph) {
    $notice = new Notice($type, $message, $classes, $dataNoticeName, $renderInParagraph);
    WPFunctions::get()->addAction('admin_notices', [$notice, 'displayWPNotice']);
    return $notice;
  }

  public function displayWPNotice() {
    $class = sprintf('notice notice-%s mailpoet_notice_server %s', $this->type, $this->classes);
    $message = nl2br($this->message);
    $dataNoticeName = !empty($this->dataNoticeName) ? sprintf('data-notice="%s"', $this->dataNoticeName) : '';

    if ($this->renderInParagraph) {
      printf('<div class="%1$s" %3$s><p>%2$s</p></div>', $class, $message, $dataNoticeName);
    } else {
      printf('<div class="%1$s" %3$s>%2$s</div>', $class, $message, $dataNoticeName);
    }
  }
}
