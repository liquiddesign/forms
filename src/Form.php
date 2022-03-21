<?php

declare(strict_types=1);

namespace Forms;

use Forms\Controls\Antispam;
use Forms\Controls\DoubleClickProtection;
use Nette\Application\ApplicationException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Localization\Translator;
use Nette\Utils\Html;

/**
 * Class Form
 * @property array<callable(static, array|object): void|callable(array|object): void> $onSuccess
 * @property array<callable(static): void> $onAnchor
 * @property array<callable(static): void> $onError
 * @property array<callable(static): void> $onRender
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
	 * @var array<string>
	 */
	protected array $flagsMap = [];
	
	/**
	 * @var array<int>|array<string>|array<array<string>> ,null[]
	 */
	protected array $wysiwygConfiguration = [
		'contentCss' => [],
		'templates' => [],
	];
	
	protected string $userUrl;
	
	protected string $userDir;
	
	protected ?string $primaryMutation = null;
	
	/**
	 * @var array<string>
	 */
	protected array $mutations;
	
	/**
	 * @var array<array<mixed>>
	 */
	protected array $polyfills = [];

	private Translator $translator;
	
	private ?string $adminLang = null;
	
	public function __construct(?\Nette\ComponentModel\IContainer $parent = null, ?string $name = null)
	{
		parent::__construct($parent, $name);
		
		$this->setRenderer(new DefaultRenderer());
		$this->onAnchor[] = function (Form $form): void {
			if ($presenter = $form->getPresenterIfExists()) {
				$presenter->template->tinyConfig = $form->getWysiwygConfiguration()['tinyConfig'] ?? [];
			}
		};
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

	public function setFormTranslator(Translator $translator): void
	{
		$this->translator = $translator;
	}
	
	public function setAdminLang(string $lang): void
	{
		$this->adminLang = $lang;
	}
	
	public function getAdminLang(): ?string
	{
		if ($this->adminLang) {
			return $this->adminLang;
		}
		
		return $this->getPrimaryMutation();
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
	 * @return array<array<mixed>>
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
	 * @param array<string> $mutations
	 */
	public function setMutations(array $mutations): void
	{
		if (isset($this[self::MUTATION_SELECTOR_NAME])) {
			throw new ApplicationException('Mutation selector already exists, please call ->removeMutationSelector() first.');
		}
		
		$this->mutations = $mutations;
	}
	
	/**
	 * @return array<string>
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
	
	public function setWysiwygConfiguration(array $contentCss, array $templates, ?string $tinyConfig): void
	{
		$this->wysiwygConfiguration['contentCss'] = $contentCss;
		$this->wysiwygConfiguration['templates'] = $templates;
		$this->wysiwygConfiguration['tinyConfig'] = $tinyConfig;
	}
	
	/**
	 * @return array<int>|array<string>|array<array<string>> ,null[]
	 */
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
				$label = $this->translator->translate('admin.defaultActiveMutation', 'Výchozí mutace je aktivní');
				$checkbox->setCaption($label);
				$checkbox->setHtmlAttribute('style', 'display:none;');
				$checkbox->getLabelPrototype()->setAttribute('style', 'position: relative; left: -1.25rem');
			} else {
				$checkbox->setHtmlAttribute('onclick', 'formDisableMutation(this.form,"' . $mutation . '","' . self::MUTATION_TRANSLATOR_NAME . '")');
			}
		});
		
		$localeContainer->forAll(function (Checkbox $checkbox, $mutation) use ($omitted): void {
			$checkbox->setHtmlAttribute('data-flag', '0');
			$checkbox->setOmitted($omitted);
		});
		
		$localeContainer->forSecondary(function (Checkbox $checkbox, $mutation): void {
			$checkbox->setHtmlAttribute('onclick', 'formDisableMutation(this.form,"' . $mutation . '","' . self::MUTATION_TRANSLATOR_NAME . '")');
		});
		
		$this->getForm()->onRender[] = function (Form $form) use ($name): void {
			foreach ($form->getMutations() as $mutation) {
				if (!$form[$name][$mutation]->getValue()) {
					foreach ($form->getComponents(true) as $component) {
						if ($component instanceof LocaleContainer && $component->getName() !== $name) {
							/** @var \Nette\Forms\Controls\BaseControl $control */
							$control = $component[$mutation];
							$control->setDisabled();
						}
					}
				} elseif ($form[$form::MUTATION_SELECTOR_NAME] instanceof BaseControl && !$form[$name][$form[$form::MUTATION_SELECTOR_NAME]->getValue()]->getValue()) {
					$form[$form::MUTATION_SELECTOR_NAME]->setDefaultValue($mutation);
				}
			}
		};
		
		$this->getForm()->onValidate[] = function ($form) use ($name): void {
			$empty = (bool) $form->getMutations();
			
			foreach ($form->getMutations() as $mutation) {
				if ($form[$name][$mutation]->getValue()) {
					$empty = false;
				}
			}
			
			if (!$empty) {
				return;
			}

			$form[$name][$this->getPrimaryMutation()]->setValue(true);
			$form[$form::MUTATION_SELECTOR_NAME]->setValue($this->getPrimaryMutation());
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
	 * @param array<string>|null $mutations
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
