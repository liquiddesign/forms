<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Control;
use Nette\Localization\Translator;
use Security\DB\IUser;
use StORM\DIConnection;
use StORM\Repository;

class ChangePasswordForm extends Form
{
	use SecurityFormTrait;

	protected DIConnection $connection;
	
	protected Nette\Security\User $user;
	
	protected Repository $repository;
	
	public function __construct(DIConnection $connection, Nette\Security\User $user, private Translator $translator,)
	{
		parent::__construct();
		
		$this->connection = $connection;
		
		if (!$user->getIdentity()) {
			throw new \InvalidArgumentException('Damaged user identity!');
		}
		
		$this->user = $user;
		
		$class = \get_class($user->getIdentity());
		
		if (!\is_subclass_of($class, IUser::class)) {
			throw new \InvalidArgumentException("Wrong or empty class: $class");
		}
		
		// @phpstan-ignore-next-line
		$this->repository = $this->connection->findRepository($class);
		
		$this->addPassword('oldPassword')
			->addRule([$this, 'validateOldPassword'], $this->translator->translate('changePasswordForm.oldPasswordCheck-notEqual', 'Nesprávné heslo!'))
			->setRequired();
		$this->addPassword('password')
			->setRequired();
		$this->addPassword('passwordCheck')
			->addRule($this::Equal, $this->translator->translate('changePasswordForm.passwordCheck-notEqual', 'Hesla se neshodují!'), $this['password'])
			->setRequired();
		$this->addSubmit('submit');
		
		$this->onSuccess[] = [$this, 'success'];
	}

	public function success(): void
	{
		$values = $this->getValues('array');
		
		/** @var \Security\DB\IUser $entity */
		$entity = $this->user->getIdentity();
		
		$entity->getAccount()->changePassword($values['password']);
	}
	
	public function validateOldPassword(Control $control): bool
	{
		/** @var \Security\DB\IUser $entity */
		$entity = $this->user->getIdentity();
		
		return $entity->getAccount()->checkPassword($control->getValue());
	}
}
