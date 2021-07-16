<?php

declare(strict_types=1);

namespace Forms;

use Forms\Controls\Range;
use Forms\Controls\UploadFile;
use Forms\Controls\UploadImage;
use Forms\Controls\Wysiwyg;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextInput;

/**
 * Trait ComponentsTrait
 * @mixin \Nette\Forms\Container
 */
trait ComponentsTrait
{
	public function addRichEdit(string $name, ?string $label = null, ?array $configuration = []): Wysiwyg
	{
		$textarea = $this[$name] = (new Wysiwyg($label));
		
		$init = [
				'height' => 400,
				'width' => 1024,
				'insertcontent' => $textarea->getParent() instanceof LocaleContainer,
				'save' => true,
			] + $this->getForm()->getWysiwygConfiguration();
		
		$this->getForm()->addPolyfill('tinymce', $textarea->getHtmlId(), $configuration + $init);
		$textarea->setHtmlAttribute('class', 'tinymce');
		
		return $textarea;
	}
	
	public function addPerexEdit(string $name, ?string $label = null, ?array $configuration = []): Wysiwyg
	{
		$init = [
			'height' => 150,
			'width' => 1024,
			'plugins' => ["autolink link", "code"],
			'toolbar1' => "undo redo | styleselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | link unlink anchor | copy cut paste pastetext insertcontent code",
			'toolbar2' => "",
			'setup' => false,
		];
		
		return $this->addRichEdit($name, $label, $configuration + $init);
	}
	
	public function addDate(string $name, ?string $label = null, ?array $configuration = []): TextInput
	{
		$textbox = $this->addText($name, $label);
		
		$this->getForm()->addPolyfill('flatpickr', $textbox->getHtmlId(), $configuration);
		$textbox->setHtmlAttribute('class', 'flatpicker');
		
		return $textbox;
	}
	
	public function addDatetime(string $name, ?string $label = null, ?array $configuration = []): TextInput
	{
		return $this->addDate($name, $label, $configuration + ['enableTime' => true]);
	}
	
	public function addTime(string $name, ?string $label = null, ?array $configuration = []): TextInput
	{
		return $this->addDate($name, $label, $configuration + ['enableTime' => true, 'noCalendar' => true, 'dateFormat' => 'H:i', 'time_24hr' => true]);
	}
	
	public function addDateRange(string $name, ?string $label = null, ?array $configuration = []): TextInput
	{
		return $this->addDate($name, $label, $configuration + ['mode' => 'range']);
	}
	
	public function addColor($name, ?string $label = null): TextInput
	{
		return $this->addText($name, $label)->setHtmlType('color');
	}
	
	public function addRange($name, ?string $label = null, int $min = 0, int $max = 100, ?array $configuration = []): Range
	{
		$rangebox = $this[$name] = (new Range($label, $min, $max));
		
		$init = [
			'start' => [$min, $max],
			'connect' => true,
			'range' => ['min' => $min, 'max' => $max],
		];
		
		$this->getForm()->addPolyfill('nouislider', $rangebox->getHtmlId(), $configuration + $init);
		$rangebox->setHtmlAttribute('class', 'nouislider');
		
		return $rangebox;
	}
	
	public function addDataSelect($name, ?string $label = null, ?array $items = null, ?array $configuration = []): SelectBox
	{
		$default = [
			'search' => true,
		];
		
		$selectbox = $this->addSelect($name, $label, $items);
		
		$this->getForm()->addPolyfill('tail.select', $selectbox->getHtmlId(), $configuration + $default);
		$selectbox->setHtmlAttribute('class', 'tail.select');
		
		return $selectbox;
	}
	
	public function addDataMultiSelect($name, ?string $label = null, ?array $items = null, ?array $configuration = []): MultiSelectBox
	{
		$default = [
			'search' => true,
			'descriptions' => true,
			'hideSelected' => true,
			'hideDisabled' => true,
			'multiLimit' => 15,
			'multiShowCount' => false,
			'multiContainer' => true,
		];
		
		$selectbox = $this->addMultiSelect($name, $label, $items);
		
		$this->getForm()->addPolyfill('tail.select', $selectbox->getHtmlId(), $configuration + $default);
		$selectbox->setHtmlAttribute('class', 'tail.select');
		
		return $selectbox;
	}
	
	public function addSelect2($name, ?string $label = null, ?array $items = null, ?array $configuration = []): SelectBox
	{
		$default = [
			'maximumSelectionLength' => 15,
		];
		
		$selectbox = $this->addSelect($name, $label, $items);
		$this->getForm()->addPolyfill('multiselect2', $selectbox->getHtmlId(), $configuration + $default);
		$this->getForm()->addPolyfill('multiselect2' . $this->getAdminLang(), $selectbox->getHtmlId(), []);
		$selectbox->setHtmlAttribute('class', 'multiselect2');
		
		return $selectbox;
	}

	public function addSelect2Ajax(
		$name,
		string $url,
		?string $label = null,
		?array $configuration = [],
		?string $placeholder = null
	): SelectBox {
		$configuration += [
			'ajax' => [
				'url' => $url,
				'delay' => 250
			],
			'minimumInputLength' => 2,
		];

		if ($placeholder) {
			$configuration += [
				'placeholder' => $placeholder
			];
		}

		return $this->addSelect2($name, $label, null, $configuration)->checkDefaultValue(false);
	}
	
	public function addMultiSelect2($name, ?string $label = null, ?array $items = null, ?array $configuration = []): MultiSelectBox
	{
		$default = [
			'theme' => 'classic',
			'maximumSelectionLength' => 15,
		];
		
		$selectbox = $this->addMultiSelect($name, $label, $items);
		$this->getForm()->addPolyfill('multiselect2', $selectbox->getHtmlId(), $configuration + $default);
		$this->getForm()->addPolyfill('multiselect2'. $this->getAdminLang(), $selectbox->getHtmlId(), []);
		$selectbox->setHtmlAttribute('class', 'multiselect2');
		
		return $selectbox;
	}
	
	public function addImagePicker($name, ?string $label = null, array $directories = [], ?string $infoText = null): UploadImage
	{
		return $this[$name] = (new UploadImage($label, $directories, $infoText));
	}
	
	public function addFilePicker($name, ?string $label = null, ?string $directory = null, ?string $infoText = null): UploadFile
	{
		return $this[$name] = (new UploadFile($label, $directory, $infoText));
	}
}
