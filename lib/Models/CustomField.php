<?php
namespace MailPoet\Models;

if(!defined('ABSPATH')) exit;

class CustomField extends Model {
  public static $_table = MP_CUSTOM_FIELDS_TABLE;

  function __construct() {
    parent::__construct();

    $this->addValidations('name', array(
      'required' => __('You need to specify a name.')
    ));
  }

  function subscribers() {
    return $this->has_many_through(
      __NAMESPACE__ . '\Subscriber',
      __NAMESPACE__ . '\SubscriberCustomField',
      'custom_field_id',
      'subscriber_id'
    );
  }
}