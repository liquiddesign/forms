<?php

declare(strict_types=1);

namespace Forms;

use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Localization\Translator;

class FormFactory
{
	protected Container $context;
	
	protected Request $request;

	/**
	 * @var array<string>
	 */
	private array $defaultMutations = [];
	
	private string $primaryMutation;
	
	private string $userDir;
	
	/**
	 * @var array<string>
	 */
	private array $flagsMap = [];
	
	private ?string $flagsPath = null;
	
	private ?string $flagsExt = null;

	private Translator $translator;
	
	/**
	 * @var array<mixed>
	 */
	private array $wysiwygConfiguration;
	
	public function __construct(Container $context, Request $request, Translator $translator)
	{
		$this->context = $context;
		$this->request = $request;
		$this->translator = $translator;
	}
	
	public function setDefaultUserDir(string $userDir): void
	{
		$this->userDir = $userDir;
	}
	
	/**
	 * @param array<mixed> $filemanager
	 * @param array<mixed> $widgets
	 * @param array<string> $contentCss
	 * @param array<string> $templates
	 */
	public function setWysiwygConfiguration(array $filemanager, array $widgets, array $contentCss, array $templates, ?string $tinyConfig): void
	{
		$this->wysiwygConfiguration = ['filemanager' => $filemanager, 'widgets' => $widgets, 'contentCss' => $contentCss, 'templates' => $templates, 'tinyConfig' => $tinyConfig];
	}
	
	/**
	 * @param string|null $index
	 * @return array<mixed>|null
	 */
	public function getWysiwygConfiguration(?string $index = null): ?array
	{
		return $index ? ($this->wysiwygConfiguration[$index] ?? null) : $this->wysiwygConfiguration;
	}
	
	public function getDefaultUserDir(): string
	{
		return $this->userDir;
	}
	
	public function setDefaultMutations(array $mutations): void
	{
		$this->defaultMutations = $mutations;
	}
	
	public function setDefaultPrimaryMutation(string $mutation): void
	{
		$this->primaryMutation = $mutation;
	}
	
	/**
	 * @return array<string>
	 */
	public function getDefaultMutations(): array
	{
		return $this->defaultMutations;
	}
	
	public function getDefaultPrimaryMutation(): string
	{
		return $this->primaryMutation;
	}
	
	/**
	 * @return array<int>|array<string>|array<array<string>> ,null[]
	 */
	public function getDefaultFlagsConfiguration(): array
	{
		return [$this->flagsPath, $this->flagsExt, $this->flagsMap];
	}
	
	public function setDefaultFlagsConfiguration(?string $path, ?string $ext, array $flagsMap): void
	{
		$this->flagsPath = $path;
		$this->flagsExt = $ext;
		$this->flagsMap = $flagsMap;
	}
	
	public function create(string $formClass = Form::class): Form
	{
		if ($formClass !== Form::class && !\is_subclass_of($formClass, Form::class)) {
			throw new \InvalidArgumentException("$formClass is not child of Forms\\Form");
		}
		
		$form = new $formClass();
		$form->setMutations($this->getDefaultMutations());
		$form->setPrimaryMutation($this->getDefaultPrimaryMutation());
		$form->setUserPaths(($this->context->getParameters()['wwwDir'] ?? '') . \DIRECTORY_SEPARATOR . $this->getDefaultUserDir(), $this->request->getUrl()->getBaseUrl() . $this->getDefaultUserDir());
		$form->setFlagsConfiguration($this->request->getUrl()->getBaseUrl() . $this->flagsPath, $this->flagsExt, $this->flagsMap);
		$form->setWysiwygConfiguration($this->wysiwygConfiguration['contentCss'] ?? [], $this->wysiwygConfiguration['templates'] ?? [], $this->wysiwygConfiguration['tinyConfig'] ?? null);
		$form->setFormTranslator($this->translator);
		
		return $form;
	}
	
	/**
	 * @deprecated use create() instead
	 */
	public function createForm(): Form
	{
		return $this->create();
	}
}
