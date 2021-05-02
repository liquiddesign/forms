<?php

declare(strict_types=1);

namespace Forms;

use Nette\Forms\Controls\TextBase;

/**
 * Trait LocaleComponentsTrait
 * @method \Forms\LocaleContainer addLocaleText(string $name, $label = null, int $cols = null, int $maxLength = null)
 * @method \Forms\LocaleContainer addLocalePassword(string $name, $label = null, int $cols = null, int $maxLength = null)
 * @method \Forms\LocaleContainer addLocaleTextArea(string $name, $label = null, int $cols = null, int $maxLength = null)
 * @method \Forms\LocaleContainer addLocaleEmail(string $name, $label = null)
 * @method \Forms\LocaleContainer addLocaleInteger(string $name, $label = null)
 * @method \Forms\LocaleContainer addLocaleUpload(string $name, $label = null)
 * @method \Forms\LocaleContainer addLocaleMultiUpload(string $name, $label = null)
 * @method \Forms\LocaleContainer addLocaleCheckbox(string $name, $caption = null)
 * @method \Forms\LocaleContainer addLocaleRadioList(string $name, $label = null, array $items = null)
 * @method \Forms\LocaleContainer addLocaleCheckboxList(string $name, $label = null, array $items = null)
 * @method \Forms\LocaleContainer addLocaleSelect(string $name, $label = null, array $items = null, int $size = null)
 * @method \Forms\LocaleContainer addLocaleMultiSelect(string $name, $label = null, array $items = null, int $size = null)
 * @method \Forms\LocaleContainer addLocaleImage(string $name, string $src = null, string $alt = null)
 * @method \Forms\LocaleContainer addLocalePerexEdit(string $name, ?string $label = null, ?array $configuration = [])
 * @method \Forms\LocaleContainer addLocaleRichEdit(string $name, ?string $label = null, ?array $configuration = [])
 * @mixin \Nette\Forms\Container
 */
trait LocaleComponentsTrait
{
	public function getForm(bool $throw = true): Form
	{
		// @phpstan-ignore-next-line
		return $this instanceof Form ? $this : $this->lookup(Form::class, $throw);
	}
	
	public function setRequired(bool $required = true)
	{
		foreach ($this->getForm()->getMutations() as $mutation) {
			if (isset($this->getParent()[Form::MUTATION_TRANSLATOR_NAME][$mutation])) {
				$this[$mutation]->addConditionOn($this->getParent()[Form::MUTATION_TRANSLATOR_NAME][$mutation], Form::EQUAL, true)->setRequired($required);
			} else {
				$this[$mutation]->setRequired($required);
			}
		}
	}
	
	protected function addLocaleContainer(string $name): LocaleContainer
	{
		$control = new LocaleContainer();
		$control->currentGroup = $this->currentGroup;
		
		if ($this->currentGroup !== null) {
			$this->currentGroup->add($control);
		}
		
		return $this[$name] = $control;
	}
	
	protected function addLocaleControls(string $primaryMutation, array $mutations, string $controlName, array $args): LocaleContainer
	{
		$prefix = 'add';
		$name = \array_shift($args);
		
		$container = $this->addLocaleContainer($name);
		$mutations = \array_unique([$primaryMutation] + $mutations);
		
		foreach ($mutations as $mutation) {
			$argMutation = \array_merge([$mutation], $args);
			
			$method = $prefix . \ucfirst($controlName);
			
			/** @var \Nette\Forms\Controls\BaseControl $control */
			$control = $container->$method(...$argMutation);
			
			$control->getLabelPrototype()->setAttribute('data-mutation', $mutation);
			$control->getControlPrototype()->setAttribute('data-mutation', $mutation);
			
			if ($control instanceof TextBase) {
				$control->setNullable();
			}
		}
		
		return $container;
	}
	
	/**
	 * @param mixed $name
	 * @param mixed $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		$prefix = 'addLocale';
		$controlName = (string) \substr($name, \strlen($prefix));
		
		if ($prefix === \substr($name, 0, \strlen($prefix)) && \method_exists($this, 'add' . $controlName)) {
			return $this->addLocaleControls($this->getForm()->getPrimaryMutation(), $this->getForm()->getMutations(), \strtolower($controlName), $arguments);
		}
		
		/** @noinspection PhpUndefinedClassInspection */
		return parent::__call($name, $arguments);
	}
}
