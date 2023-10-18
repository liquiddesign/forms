<?php

declare(strict_types=1);

namespace Forms\Controls;

use Carbon\Carbon;
use Nette\Forms\Controls\HiddenField;
use Nette\Utils\Html;

class Antispam extends HiddenField
{
	public const PROTECTION = 'Forms\Controls\Antispam::validateAntispam';
	
	protected float $timeThreshold;
	
	/**
	 * @param string|object $errorMessage
	 * @param float $timeThreshold
	 */
	public function __construct($errorMessage, float $timeThreshold = 1.0)
	{
		parent::__construct();
		
		$this->setOmitted()
			->setRequired()
			->addRule(self::PROTECTION, $errorMessage);
			
		$this->timeThreshold = $timeThreshold;
	}
	
	/**
	 * @param mixed $value
	 * @return static
	 * @internal
	 */
	public function setValue($value): static
	{
		unset($value);
		
		return $this;
	}

	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(\Nette\Forms\Form::DATA_TEXT);
	}

	public function getControl(): Html
	{
		$this->setOption('rendered', true);
		
		$wrapper = Html::el('div', ['class' => 'form-antispam', 'style' => 'display:none']);
		
		$el = parent::getControl()->value((string) \microtime(true));
		
		$elFrom = Html::el('input', [
			'type' => 'text',
			'id' => $this->getHtmlId() . 'email_as',
			'name' => $this->getHtmlName() . 'email_as',
			'value' => '',
		]);
		
		$elTo = Html::el('input', [
			'type' => 'text',
			'id' => $this->getHtmlId() . 'year_as',
			'name' => $this->getHtmlName() . 'year_as',
			'value' => '',
		]);
		
		$wrapper->addHtml($el);
		$wrapper->addHtml($elFrom);
		$wrapper->addHtml($elTo);
		
		$wrapper->addHtml('<script>document.getElementById("' . $this->getHtmlId() . 'year_as").value = "' . Carbon::now()->format('Y') . '";</script>');
		
		return $wrapper;
	}
	
	/** @param \Forms\Controls\Antispam $control
	 * @internal
	 */
	public static function validateAntispam(self $control): bool
	{
		$form = $control->getForm();
		
		if (\microtime(true) - (float) $control->value < $control->timeThreshold) {
			return false;
		}
		
		if ($form->getHttpData(\Nette\Forms\Form::DATA_TEXT, $control->getHtmlName() . 'email_as') !== '') {
			return false;
		}
		
		return $form->getHttpData(\Nette\Forms\Form::DATA_TEXT, $control->getHtmlName() . 'year_as') === Carbon::now()->format('Y');
	}
}
