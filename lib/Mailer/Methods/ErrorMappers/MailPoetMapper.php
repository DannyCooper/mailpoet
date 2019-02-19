<?php
namespace MailPoet\Mailer\Methods\ErrorMappers;

use MailPoet\Mailer\MailerError;
use MailPoet\Mailer\SubscriberError;
use MailPoet\Services\Bridge\API;
use InvalidArgumentException;
use MailPoet\Util\Helpers;

if (!defined('ABSPATH')) exit;

class MailPoetMapper {
  use ConnectionErrorMapperTrait;

  const TEMPORARY_UNAVAILABLE_RETRY_INTERVAL = 300; // seconds

  function getInvalidApiKeyError() {
    return new MailerError(
      MailerError::OPERATION_SEND,
      MailerError::LEVEL_HARD,
      __('MailPoet API key is invalid!', 'mailpoet')
    );
  }

  function getErrorForResult(array $result, $subscribers, $sender = null) {
    $level = MailerError::LEVEL_HARD;
    $operation = MailerError::OPERATION_SEND;
    $retry_interval = null;
    $subscribers_errors = [];
    $result_code = !empty($result['code']) ? $result['code'] : null;

    switch ($result_code) {
      case API::RESPONSE_CODE_NOT_ARRAY:
        $message = __('JSON input is not an array', 'mailpoet');
        break;
      case API::RESPONSE_CODE_PAYLOAD_ERROR:
        $result_parsed = json_decode($result['message'], true);
        $message = __('Error while sending.', 'mailpoet');
        if (!is_array($result_parsed)) {
          $message .= ' ' . $result['message'];
          break;
        }
        try {
          $subscribers_errors = $this->getSubscribersErrors($result_parsed, $subscribers);
          $level = MailerError::LEVEL_SOFT;
        } catch (InvalidArgumentException $e) {
          $message .= ' ' . $e->getMessage();
        }
        break;
      case API::RESPONSE_CODE_TEMPORARY_UNAVAILABLE:
        $message = __('Email service is temporarily not available, please try again in a few minutes.', 'mailpoet');
        $retry_interval = self::TEMPORARY_UNAVAILABLE_RETRY_INTERVAL;
        break;
      case API::RESPONSE_CODE_CAN_NOT_SEND:
        if ($result['message'] === MailerError::MESSAGE_EMAIL_NOT_AUTHORIZED) {
          $operation = MailerError::OPERATION_AUTHORIZATION;
          $message = sprintf(__('<p>The MailPoet Sending Service did not send your latest email because the address <i>%s</i> is not yet authorized.</p>', 'mailpoet'), $sender ? $sender['from_email'] : __('Unknown address'));
          $message .= '<p>';
          $message .= Helpers::replaceLinkTags(
            __('[link]Authorize your email in your account now.[/link]', 'mailpoet'),
            'https://account.mailpoet.com/account/authorization',
            array(
              'class' => 'button button-primary',
              'target' => '_blank',
            )
          );
          $message .= ' &nbsp; <button class="button js-button-resume-sending">' . __('Resume sending', 'mailpoet') . '</button>';
          $message .= '</p>';
          $message .= "<script>jQuery('.js-button-resume-sending').on('click', function() { MailPoet.Ajax.post({ api_version: window.mailpoet_api_version, endpoint: 'mailer', action: 'resumeSending' }).done(function() { jQuery('.js-error-unauthorized-email').slideUp(); MailPoet.Notice.success(MailPoet.I18n.t('mailerSendingResumedNotice')); if (window.mailpoet_listing) { window.mailpoet_listing.forceUpdate(); }}).fail(function(response) { if (response.errors.length > 0) { MailPoet.Notice.error(response.errors.map(function(error) { return error.message }), { scroll: true }); }}); })</script>";
        } else {
          $message = Helpers::replaceLinkTags(
            __('You currently are not permitted to send any emails with MailPoet Sending Service, which may have happened due to poor deliverability. Please [link]contact our support team[/link] to resolve the issue.', 'mailpoet'),
            'https://www.mailpoet.com/support/',
            array('target' => '_blank')
          );
        }
        break;
      case API::RESPONSE_CODE_KEY_INVALID:
      case API::RESPONSE_CODE_PAYLOAD_TOO_BIG:
      default:
        $message = $result['message'];
    }
    return new MailerError($operation, $level, $message, $retry_interval, $subscribers_errors);
  }

  private function getSubscribersErrors($result_parsed, $subscribers) {
    $errors = [];
    foreach ($result_parsed as $result_error) {
      if (!is_array($result_error) || !isset($result_error['index']) || !isset($subscribers[$result_error['index']])) {
        throw new InvalidArgumentException( __('Invalid MSS response format.', 'mailpoet'));
      }
      $subscriber_errors = [];
      if (isset($result_error['errors']) && is_array($result_error['errors'])) {
        array_walk_recursive($result_error['errors'], function($item) use (&$subscriber_errors) {
          $subscriber_errors[] = $item;
        });
      }
      $message = join(', ', $subscriber_errors);
      $errors[] = new SubscriberError($subscribers[$result_error['index']], $message);
    }
    return $errors;
  }
}
