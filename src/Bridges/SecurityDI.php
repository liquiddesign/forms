<?php

declare(strict_types=1);

namespace Forms\Bridges;

use Forms\Bridges\FormsSecurity\IChangePasswordFormFactory;
use Forms\Bridges\FormsSecurity\ILoginFormFactory;
use Forms\Bridges\FormsSecurity\ILostPasswordFormFactory;
use Forms\Bridges\FormsSecurity\IRegistrationFormFactory;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Security\DB\Account;

class SecurityDI extends \Nette\DI\CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([]);
	}
	
	public function loadConfiguration(): void
	{
		if (!class_exists(Account::class)) {
			return;
		}
		
		$builder = $this->getContainerBuilder();
		
		$builder->addFactoryDefinition($this->prefix('loginFormFactory'))->setImplement(ILoginFormFactory::class);
		$builder->addFactoryDefinition($this->prefix('changePasswordFormFactory'))->setImplement(IChangePasswordFormFactory::class);
		$builder->addFactoryDefinition($this->prefix('lostPasswordFormFactory'))->setImplement(ILostPasswordFormFactory::class);
		$builder->addFactoryDefinition($this->prefix('registrationFormFactory'))->setImplement(IRegistrationFormFactory::class);
	}
}
