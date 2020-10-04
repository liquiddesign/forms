<?php

declare(strict_types=1);

namespace Forms;

use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Localization\ITranslator;

class Forms
{
	/**
	 * @var string[]
	 */
	private array $defaultMutations = [];
	
	private string $primaryMutation;
	
	private string $userDir;
	
	/**
	 * @var string[]
	 */
	private array $flagsMap = [];
	
	private ?string $flagsPath = null;
	
	private ?string $flagsExt = null;
	
	private ?ITranslator $translator;
	
	private Container $context;
	
	private Request $request;
	
	/**
	 * @var mixed[]
	 */
	private array $wysiwygConfiguration;
	
	public function __construct(?ITranslator $translator, Request $request, Container $context)
	{
		$this->translator = $translator;
		$this->context = $context;
		$this->request = $request;
	}
	
	public function setDefaultUserDir(string $userDir): void
	{
		$this->userDir = $userDir;
	}
	
	/**
	 * @param mixed[] $filemanager
	 * @param mixed[] $widgets
	 * @param string[] $contentCss
	 * @param string[] $templates
	 */
	public function setWysiwygConfiguration(array $filemanager, array $widgets, array $contentCss, array $templates): void
	{
		$this->wysiwygConfiguration = ['filemanager' => $filemanager, 'widgets' => $widgets, 'contentCss' => $contentCss, 'templates' => $templates];
	}
	
	/**
	 * @param string $index
	 * @return mixed[]|null
	 */
	public function getWysiwygConfiguration(string $index): ?array
	{
		return $this->wysiwygConfiguration[$index] ?? null;
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
	 * @return array|string[]
	 */
	public function getDefaultMutations(): array
	{
		return $this->defaultMutations;
	}
	
	public function getDefaultPrimaryMutation(): string
	{
		return $this->primaryMutation;
	}
	
	public function setDefaultFlagsConfiguration(?string $path, ?string $ext, array $flagsMap): void
	{
		$this->flagsPath = $path;
		$this->flagsExt = $ext;
		$this->flagsMap = $flagsMap;
	}
	
	public function createForm(): Form
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setMutations($this->getDefaultMutations());
		$form->setPrimaryMutation($this->getDefaultPrimaryMutation());
		$form->setUserPaths(($this->context->getParameters()['wwwDir'] ?? '') . \DIRECTORY_SEPARATOR . $this->getDefaultUserDir(), $this->request->getUrl()->getBaseUrl() . $this->getDefaultUserDir());
		$form->setFlagsConfiguration($this->request->getUrl()->getBaseUrl() . $this->flagsPath, $this->flagsExt, $this->flagsMap);
		
		return $form;
	}
}
