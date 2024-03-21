<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

use Nette;
use Security\DB\IUser;
use StORM\DIConnection;
use StORM\Repository;

class ChangePasswordForm extends \Nette\Application\UI\Form
{
	use SecurityFormTrait;

	
	protected DIConnection $connection;
	
	protected Nette\Security\User $user;
	
	protected Repository $repository;
	
	public function __construct(DIConnection $connection, Nette\Security\User $user)
	{
		parent::__construct();
		
		$this->connection = $connection;
		
		if (!$user->getIdentity()) {
			throw new \InvalidArgumentException('Damaged user identity!');
		}
		
		$this->user = $user;
		
		$class = \get_class($user->getIdentity());
		
		if (!\is_subclass_of($class, IUser::class) || !\is_subclass_of($class, Nette\Security\IIdentity::class)) {
			throw new \InvalidArgumentException("Wrong or empty class: $class");
		}
		
		// @phpstan-ignore-next-line
		$this->repository = $this->connection->findRepository($class);
		
		$this->addPassword('oldPassword')
			->addRule([$this, 'validateOldPassword'], 'changePasswordForm.oldPasswordCheck.notEqual', $user)
			->setRequired();
		$this->addPassword('password')
			->setRequired();
		$this->addPassword('passwordCheck')
			->addRule($this::EQUAL, 'changePasswordForm.passwordCheck.notEqual', $this['password'])
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
	
	public static function validateOldPassword(\Nette\Forms\Control $control, Nette\Security\User $user): bool
	{
		/** @var \Security\DB\IUser $entity */
		$entity = $user->getIdentity();
		
		return $entity->getAccount()->checkPassword($control->getValue());
	}
}
