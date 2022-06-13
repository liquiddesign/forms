<?php

declare(strict_types=1);

namespace Forms;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Utils\Html;

class DefaultRenderer extends DefaultFormRenderer
{
	public function renderPair(\Nette\Forms\Control $control): string
	{
		
		$pair = $this->getWrapper('pair container');
		
		if ($control instanceof BaseControl) {
			$pair->addHtml($this->renderLabel($control));
			$pair->addHtml($this->renderControl($control));
			$pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), true);
			$pair->class($control->hasErrors() ? $this->getValue('pair .error') : null, true);
			$pair->class($control->getOption('class'), true);
			
			if (++$this->counter % 2) {
				$pair->class($this->getValue('pair .odd'), true);
			}
			
			$pair->id = $control->getOption('id');
		}
		
		if ($control instanceof BaseControl && $form = $control->getForm()) {
			if ($form instanceof Form) {
				$controlMutation = $control->getControlPrototype()->getAttribute('data-mutation');
				
				if ($controlMutation) {
					$activeMutation = $form->getActiveMutation();
					
					$pair->setAttribute('data-mutation', $controlMutation);
					
					if ($controlMutation !== $activeMutation && $activeMutation !== null) {
						$pair->hidden(true);
					}
				}
			}
		}
		
		return $pair->render(0);
	}
	
	public function renderLabel(\Nette\Forms\Control $control): Html
	{
		$html = parent::renderLabel($control);
		
		if ($control instanceof BaseControl && $form = $control->getForm()) {
			if ($form instanceof Form) {
				$controlMutation = $control->getControlPrototype()->getAttribute('data-mutation');
				$showFlag = $control->getControlPrototype()->getAttribute('data-flag') ?? true;
				
				if ($controlMutation && $showFlag) {
					$src = $form->getFlagSrc($controlMutation);
					$html->addHtml("&nbsp; <img class=mutation-flag src=$src alt=$controlMutation title=$controlMutation>");
				}
			}
		}
		
		return $html;
	}
}
