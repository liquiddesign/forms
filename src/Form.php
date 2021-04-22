<?php

declare(strict_types=1);

namespace Forms;

use Forms\Controls\Antispam;
use Forms\Controls\DoubleClickProtection;
use Nette\Application\ApplicationException;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

/**
 * Class Form
 */
class Form extends \Nette\Application\UI\Form
{
	use LocaleComponentsTrait;
	use ComponentsTrait;
	
	public const MUTATION_SELECTOR_NAME = '__MUTATION_SELECTOR';
	public const MUTATION_TRANSLATOR_NAME = 'active';
	public const ANTISPAM_ID = '_antispam_';
	
	protected ?string $flagsPath = null;
	
	protected ?string $flagsExt = null;
	
	/**
	 * @var string[]
	 */
	protected array $flagsMap = [];
	
	protected array $wysiwygConfiguration = [
		'contentCss' => [],
		'templates' => [],
	];
	
	protected string $userUrl;
	
	protected string $userDir;
	
	protected ?string $primaryMutation = null;
	
	/**
	 * @var string[]
	 */
	protected array $mutations;
	
	/**
	 * @var mixed[][]
	 */
	protected array $polyfills = [];
	
	public function __construct(?\Nette\ComponentModel\IContainer $parent = null, ?string $name = null)
	{
		parent::__construct($parent, $name);
		
		$this->setRenderer(new DefaultRenderer());
	}
	
	/**
	 * Doubleclick + Cross-Site Request Forgery (CSRF) form protection.
	 */
	public function addDoubleClickProtection(?string $errorMessage = null): DoubleClickProtection
	{
		$control = new DoubleClickProtection($errorMessage);
		$this->addComponent($control, self::PROTECTOR_ID, \key((array) $this->getComponents()));
		
		return $control;
	}
	
	public function addAntispam(string $errorMessage, float $timeThreshold = 1.0): Antispam
	{
		$control = new Antispam($errorMessage, $timeThreshold);
		$this->addComponent($control, self::ANTISPAM_ID, \key((array) $this->getComponents()));
		
		return $control;
	}
	
	public function addPolyfill(string $name, string $id, ?array $configuration): void
	{
		if (!isset($this->polyfills[$name])) {
			$this->polyfills[$name] = [];
		}
		
		$this->polyfills[$name][$id] = $configuration;
	}
	
	public function modifyPolyfillConfiguration(string $name, string $id, ?array $configuration): bool
	{
		if (!isset($this->polyfills[$name][$id]) && !\is_array($this->polyfills[$name][$id])) {
			return false;
		}
		
		$this->polyfills[$name][$id] = \array_merge($this->polyfills[$name][$id], $configuration);
		
		return true;
	}
	
	/**
	 * @return mixed[][]
	 */
	public function getPolyfills(): array
	{
		return $this->polyfills;
	}
	
	public function getPrimaryMutation(): ?string
	{
		return $this->primaryMutation;
	}
	
	public function setPrimaryMutation(string $mutation): void
	{
		$this->primaryMutation = $mutation;
	}
	
	/**
	 * @param string[] $mutations
	 */
	public function setMutations(array $mutations): void
	{
		if (isset($this[self::MUTATION_SELECTOR_NAME])) {
			throw new ApplicationException('Mutation selector already exists, please call ->removeMutationSelector() first.');
		}
		
		$this->mutations = $mutations;
	}
	
	/**
	 * @return string[]
	 */
	public function getMutations(): array
	{
		return $this->mutations;
	}
	
	public function setActiveMutation(string $mutation): void
	{
		if (isset($this[self::MUTATION_SELECTOR_NAME])) {
			/** @var \Nette\Forms\Controls\Checkbox $selector */
			$selector = $this[self::MUTATION_SELECTOR_NAME];
			$selector->setDefaultValue($mutation);
		}
	}
	
	public function getActiveMutation(): ?string
	{
		if (!isset($this[self::MUTATION_SELECTOR_NAME])) {
			return null;
		}
		
		/** @var \Nette\Forms\Controls\Checkbox $selector */
		$selector = $this[self::MUTATION_SELECTOR_NAME];
		
		return $selector->getValue();
	}
	
	public function setUserPaths(string $userDir, string $userUrl): void
	{
		$this->userDir = $userDir;
		$this->userUrl = $userUrl;
	}
	
	public function setWysiwygConfiguration(array $contentCss, array $templates): void
	{
		$this->wysiwygConfiguration['contentCss'] = $contentCss;
		$this->wysiwygConfiguration['templates'] = $templates;
	}
	
	public function getWysiwygConfiguration(): array
	{
		return $this->wysiwygConfiguration;
	}
	
	
	public function getUserDir(): string
	{
		return $this->userDir;
	}
	
	public function getUserUrl(): string
	{
		return $this->userUrl;
	}
	
	public function setFlagsConfiguration(?string $flagsPath, ?string $flagsExt, array $flagsMap): void
	{
		if ($flagsPath) {
			$this->flagsPath = $flagsPath;
		}
		
		if ($flagsExt) {
			$this->flagsExt = $flagsExt;
		}
		
		if (!$flagsMap) {
			return;
		}
		
		$this->flagsMap = $flagsMap;
	}
	
	public function getFlagsPath(): string
	{
		return $this->flagsPath;
	}
	
	public function getFlagsExt(): string
	{
		return $this->flagsExt;
	}
	
	public function getFlagSrc(string $mutation): string
	{
		return $this->flagsPath . '/' . ($this->flagsMap[$mutation] ?? $mutation) . '.' . $this->flagsExt;
	}
	
	public function addMutationSelector(string $label): void
	{
		$items = [];
		
		foreach ($this->getMutations() as $mutation) {
			$items[$mutation] = Html::el("img class=mutation-flag alt=$mutation title=$mutation src=" . $this->getFlagSrc($mutation));
		}
		
		$this->addRadioList(self::MUTATION_SELECTOR_NAME, $label, $items)->setDefaultValue($this->getPrimaryMutation())->setOmitted()
			->setHtmlAttribute('onclick', 'formChangeMutation(this.form, this.value)');
	}
	
	public function addTranslatedCheckbox(string $label, string $name = self::MUTATION_TRANSLATOR_NAME, bool $omitted = false, bool $forcePrimary = true): void
	{
		$localeContainer = $this->addLocaleCheckbox($name, $label)->forPrimary(function (Checkbox $checkbox, $mutation) use ($forcePrimary): void {
			$checkbox->setDefaultValue(true);
			
			if ($forcePrimary) {
				$checkbox->setHtmlAttribute('style', 'display:none;');
				$checkbox->getLabelPrototype()->setAttribute('style', 'position: relative; left: -1.25rem');
			} else {
				$checkbox->setHtmlAttribute('onclick', 'formDisableMutation(this.form,"'.$mutation.'","'.self::MUTATION_TRANSLATOR_NAME.'")');
			}
		});
		
		$localeContainer->forAll(function (Checkbox $checkbox, $mutation) use ($omitted): void {
			$checkbox->setHtmlAttribute('data-flag', '0');
			$checkbox->setOmitted($omitted);
		});
		
		$localeContainer->forSecondary(function (Checkbox $checkbox, $mutation) use ($omitted): void {
			$checkbox->setHtmlAttribute('onclick', 'formDisableMutation(this.form,"'.$mutation.'","'.self::MUTATION_TRANSLATOR_NAME.'")');
		});
		
		/* @phpstan-ignore-next-line */
		$this->getForm()->onAnchor[] = function (Form $form) use ($name): void {
			foreach ($form->getMutations() as $mutation) {
				if (!$form[$name][$mutation]->getValue()) {
					foreach ($form->getComponents(true) as $component) {
						if ($component instanceof LocaleContainer && $component->getName() !== $name) {
							/** @var \Nette\Forms\Controls\BaseControl $control */
							$control = $component[$mutation];
							$control->setDisabled();
						}
					}
				}
			}
		};
	}
	
	public function removeMutationSelector(): void
	{
		unset($this[self::MUTATION_SELECTOR_NAME]);
	}
	
	/**
	 * Adds naming container to the form.
	 * @param string|int $name
	 */
	public function addContainer($name): Container
	{
		$control = new Container();
		$control->currentGroup = $this->currentGroup;
		
		if ($this->currentGroup !== null) {
			$this->currentGroup->add($control);
		}
		
		return $this[$name] = $control;
	}
	
	/**
	 * @param string[]|null $mutations
	 */
	public function setReadonly(?array $mutations = null): void
	{
		unset($mutations);
		
		foreach ($this->getComponents() as $component) {
			// TODO implement
			unset($component);
		}
	}
	
	protected function setReadonlyForDescendant(\Nette\Forms\Container $container, string $mutation): void
	{
		// TODO implement
		unset($container, $mutation);
	}
}
