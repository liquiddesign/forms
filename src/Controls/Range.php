<?php

declare(strict_types=1);

namespace Forms\Controls;

use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class Range extends BaseControl
{
	protected int $min;

	protected int $max;
	
	public function __construct($caption = null, int $min = 0, int $max = 100)
	{
		parent::__construct($caption);

		$this->control = Html::el('div', []);
		$this->min = $min;
		$this->max = $max;
	}
	
	public function loadHttpData(): void
	{
		$this->setValue([
			$this->getHttpData(\Nette\Forms\Form::DATA_TEXT, '[0]'),
			$this->getHttpData(\Nette\Forms\Form::DATA_TEXT, '[1]'),
		]);
	}
	
	/**
	 * @param mixed $value
	 */
	public function setValue($value): \Forms\Controls\Range
	{
		if ($value === null) {
			$value = [];
		} elseif (!\is_array($value)) {
			throw new \InvalidArgumentException(\sprintf("Value must be array or null, %s given in field '%s'.", \gettype($value), $this->name));
		}
		
		$this->value = $value;
		
		/** @var \Forms\Form|null $form */
		$form = $this->getForm(false);
		
		if ($form) {
			$form->modifyPolyfillConfiguration('nouislider', $this->getHtmlId(), [
				'start' => $this->value,
			]);
		}
		
		return $this;
	}
	
	/**
	 * Generates control's HTML element.
	 * @return \Nette\Utils\Html|string
	 */
	public function getControl()
	{
		$this->setOption('rendered', true);
		
		$wrapper = Html::el('div', ['class' => 'nouislider-wrapper']);
		
		$el = clone $this->control;
		$el->addAttributes([
			'id' => $this->getHtmlId(),
		]);
		
		$elFrom = Html::el('input', [
			'type' => 'hidden',
			'id' => $this->getHtmlId() . '-from',
			'name' => $this->getHtmlName() . '[0]',
			'value' => $this->value[0] ?? null,
		]);
		
		$elTo = Html::el('input', [
			'type' => 'hidden',
			'id' => $this->getHtmlId() . '-to',
			'name' => $this->getHtmlName() . '[1]',
			'value' => $this->value[1] ?? null,
		]);
		
		return $wrapper->addHtml($el)->addHtml($elFrom)->addHtml($elTo);
	}
}
