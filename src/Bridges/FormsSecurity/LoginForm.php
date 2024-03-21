<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

use Nette;
use Security\DB\IUser;

/**
 * @method onLogin(\Forms\Bridges\FormsSecurity\LoginForm $form, \Nette\Security\IIdentity $identity)
 * @method onLoginFail(\Forms\Bridges\FormsSecurity\LoginForm $form, int $errorCode)
 */
class LoginForm extends \Nette\Application\UI\Form
{
	use SecurityFormTrait;

	
	/**
	 * @var array<callable(static, \Nette\Security\IIdentity): void> Called when login success
	 */
	public array $onLogin = [];
	
	/**
	 * @var array<callable(static, int): void> Called when login fails
	 */
	public array $onLoginFail = [];
	
	protected Nette\Security\User $user;
	
	/**
	 * @var array<string>
	 */
	protected array $classes;
	
	/**
	 * LoginForm constructor.
	 * @param array<string> $classes
	 * @param \Nette\Security\User $user
	 */
	public function __construct(array $classes, Nette\Security\User $user)
	{
		parent::__construct();
		
		$this->user = $user;
		
		foreach ($classes as $class) {
			if (!\is_subclass_of($class, IUser::class) || !\is_subclass_of($class, Nette\Security\IIdentity::class)) {
				throw new \InvalidArgumentException("Wrong or empty class: $class");
			}
		}
		
		$this->classes = $classes;

		$this->addText('login', 'loginForm.login')->setRequired(true);
		$this->addPassword('password', 'loginForm.password')->setRequired(true);
		
		$this->addSubmit('submit', 'loginForm.submit');
		
		$this->onSuccess[] = [$this, 'submit'];
	}
	
	protected function submit(): void
	{
		try {
			$values = $this->getValues('array');
			$this->user->login($values['login'], $values['password'], $this->classes);
			$this->onLogin($this, $this->user->getIdentity());
		} catch (Nette\Security\AuthenticationException $exception) {
			$this->onLoginFail($this, $exception->getCode());
		}
	}
}
