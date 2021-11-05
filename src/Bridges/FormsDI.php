<?php

declare(strict_types=1);

namespace Forms\Bridges;

use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class FormsDI extends \Nette\DI\CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'userDir' => Expect::string('userfiles'),
			'flagsPath' => Expect::string('public/node_modules/flag-icon-css/flags/4x3'),
			'flagsExt' => Expect::string('svg'),
			'flagsMap' => Expect::arrayOf('string'),
			'primaryMutation' => Expect::string(),
			'mutations' => Expect::arrayOf('string')->min(1),
			'wysiwyg' => Expect::structure([
				'contentCss' => Expect::arrayOf('string'),
				'templates' => Expect::mixed(),
				'tinyConfig' => Expect::string('vendor/liquiddesign/forms/src/assets/tinymce.js'),
				'filemanager' => Expect::structure([
					'directory' => Expect::string('userfiles'),
					'isAllowed' => Expect::mixed(),
					'lang' => Expect::mixed(),
				]),
				'widgets' => Expect::structure([
					'repositoryClass' => Expect::string(),
					'labels' => Expect::arrayOf('string'),
				]),
			]),
		]);
	}
	
	public function loadConfiguration(): void
	{
		/** @var \stdClass $config */
		$config = $this->getConfig();
		
		$builder = $this->getContainerBuilder();
		
		$pages = $builder->addDefinition($this->prefix('componentFactory'), new ServiceDefinition())->setType(\Forms\FormFactory::class);
		$pages->addSetup('setDefaultMutations', [$config->mutations]);
		$pages->addSetup('setDefaultUserDir', [$config->userDir]);
		$pages->addSetup('setWysiwygConfiguration', [
			(array) $config->wysiwyg->filemanager,
			(array) $config->wysiwyg->widgets,
			(array) $config->wysiwyg->contentCss,
			$config->wysiwyg->templates,
			$config->wysiwyg->tinyConfig,
			]);
		$pages->addSetup('setDefaultFlagsConfiguration', [$config->flagsPath, $config->flagsExt, $config->flagsMap]);
		$pages->addSetup('setDefaultPrimaryMutation', [$config->primaryMutation ?: \current($config->mutations)]);
	
		return;
	}
}
