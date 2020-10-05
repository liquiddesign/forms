<?php

declare(strict_types=1);

namespace Forms\Controls;

use Forms\Forms;
use Forms\LocaleContainer;
use Nette\Application\UI\BadSignalException;
use Nette\Application\UI\ISignalReceiver;
use Nette\Application\UI\Presenter;
use Nette\Forms\Controls\TextArea;

/**
 * @method onSave(string $content, ?string $lang)
 */
class Wysiwyg extends TextArea implements ISignalReceiver
{
	/**
	 * @var callable[]
	 */
	public array $onSave = [];
	
	public function __construct($label = null)
	{
		parent::__construct($label);
		
		$element = $this;
		
		$this->monitor(Presenter::class, function ($presenter) use ($element): void {
			/** @var \Forms\Form $form */
			$form = $element->getForm();
			
			$link = $form->getPresenter()->link($this->getParent()->getName() . '-' . $this->getName() . '-save!');
			
			$templates = $presenter->context->getByType(Forms::class)->getWysiwygConfiguration('templates');
			$contentCss = $presenter->context->getByType(Forms::class)->getWysiwygConfiguration('contentCss');
			
			if (!\count($element->onSave)) {
				$form->modifyPolyfillConfiguration('tinymce', $element->getHtmlId(), ['save' => false]);
			}
			
			$form->modifyPolyfillConfiguration('tinymce', $element->getHtmlId(), ['saveLink' => $link]);
			$form->modifyPolyfillConfiguration('tinymce', $element->getHtmlId(), ['templates' => $templates ?? []]);
			$form->modifyPolyfillConfiguration('tinymce', $element->getHtmlId(), ['contentCss' => $contentCss ?? []]);
		});
	}
	
	public function signalReceived(string $signal): void
	{
		/** @var \Forms\Form $form */
		$form = $this->getForm();
		
		$post = $form->getPresenter()->getHttpRequest()->getPost();
		
		if (!isset($post['content']) || !isset($post['lang'])) {
			$class = static::class;
			
			throw new BadSignalException("Missing post values 'content' or 'lang' in $class.");
		}
		
		if ($signal === 'save') {
			$this->onSave($post['content'], $this->getParent() instanceof LocaleContainer && \in_array($post['lang'], $form->getMutations()) ? $post['lang'] : null);
			
			return;
		}
		
		$class = static::class;
		
		throw new BadSignalException("Missing handler for signal '$signal' in $class.");
	}
}
