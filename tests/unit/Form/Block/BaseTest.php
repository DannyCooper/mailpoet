<?php

namespace MailPoet\Test\Form\Block;

use MailPoet\Form\Block\Base;
use MailPoet\Form\Util\FieldNameObfuscator;
use MailPoet\WP\Functions as WPFunctions;
use PHPUnit\Framework\MockObject\MockObject;

class BaseTest extends \MailPoetUnitTest {
  /** @var Base */
  private $baseRenderer;

  /** @var MockObject|WPFunctions */
  private $wpMock;

  /** @var MockObject|FieldNameObfuscator */
  private $obfuscatorMock;

  private $block = [
    'type' => 'text',
    'name' => 'Custom text',
    'id' => '1',
    'unique' => '1',
    'static' => '0',
    'params' => [
      'label' => 'Input label',
      'required' => '',
      'hide_label' => '',
    ],
    'position' => '1',
  ];
  public function _before() {
    parent::_before();
    $this->wpMock = $this->createMock(WPFunctions::class);
    $this->wpMock->method('escAttr')->will($this->returnArgument(0));
    $this->obfuscatorMock = $this->createMock(FieldNameObfuscator::class);
    $this->obfuscatorMock->method('obfuscate')->will($this->returnArgument(0));
    $this->baseRenderer = new Base($this->obfuscatorMock, $this->wpMock);
  }

  public function testItShouldRenderLabel() {
    $block = $this->block;
    $label = $this->baseRenderer->renderLabel($block);
    expect($label)->equals('<label class="mailpoet_text_label">Input label</label>');

    $block['params']['required'] = '1';
    $label = $this->baseRenderer->renderLabel($block);
    expect($label)->equals('<label class="mailpoet_text_label">Input label <span class="mailpoet_required">*</span></label>');

    $block['params']['hide_label'] = '1';
    $label = $this->baseRenderer->renderLabel($block);
    expect($label)->equals('');
  }

  public function testItShouldRenderPlaceholder() {
    $block = $this->block;
    $placeholder = $this->baseRenderer->renderInputPlaceholder($block);
    expect($placeholder)->equals('');

    $block['params']['label_within'] = '1';
    $placeholder = $this->baseRenderer->renderInputPlaceholder($block);
    expect($placeholder)->equals(' placeholder="Input label" ');

    $block['params']['required'] = '1';
    $placeholder = $this->baseRenderer->renderInputPlaceholder($block);
    expect($placeholder)->equals(' placeholder="Input label *" ');
  }

  public function testItShouldRenderInputValidations() {
    $block = $this->block;
    $validation = $this->baseRenderer->getInputValidation($block);
    expect($validation)->equals('');

    $block['params']['required'] = '1';
    $validation = $this->baseRenderer->getInputValidation($block);
    expect($validation)->equals('data-parsley-required="true" data-parsley-required-message="This field is required."');

    $block['params']['required'] = '0';
    $block['id'] = 'email';
    $validation = $this->baseRenderer->getInputValidation($block);
    expect($validation)->equals('data-parsley-required="true" data-parsley-minlength="6" data-parsley-maxlength="150" data-parsley-error-message="Please specify a valid email address."');

    $block = $this->block;
    $block['params']['validate'] = 'phone';
    $validation = $this->baseRenderer->getInputValidation($block);
    expect($validation)->equals('data-parsley-pattern="^[\d\+\-\.\(\)\/\s]*$" data-parsley-error-message="Please specify a valid phone number"');

    $block = $this->block;
    $block['type'] = 'radio';
    $validation = $this->baseRenderer->getInputValidation($block);
    expect($validation)->equals('data-parsley-group="custom_field_1" data-parsley-errors-container=".mailpoet_error_1" data-parsley-required-message="Please select at least one option"');

    $block = $this->block;
    $block['type'] = 'date';
    $validation = $this->baseRenderer->getInputValidation($block);
    expect($validation)->equals('data-parsley-group="custom_field_1" data-parsley-errors-container=".mailpoet_error_1"');

    $block = $this->block;
    $validation = $this->baseRenderer->getInputValidation($block, ['custom']);
    expect($validation)->equals('data-parsley-0="custom"');
  }

  public function testItShouldObfuscateFieldNameIfNeeded() {
    $block = $this->block;
    $fieldName = $this->baseRenderer->getFieldName($block);
    expect($fieldName)->equals('cf_1');

    $obfuscatorMock = $this->createMock(FieldNameObfuscator::class);
    $obfuscatorMock->expects($this->once())->method('obfuscate')->willReturn('xyz');
    $renderer = new Base($obfuscatorMock, $this->wpMock);

    $block['id'] = 'email';
    $fieldName = $renderer->getFieldName($block);
    expect($fieldName)->equals('xyz');
  }
}
