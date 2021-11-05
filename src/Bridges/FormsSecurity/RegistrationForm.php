<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

use Forms\Form;
use Nette;
use Security\DB\AccountRepository;
use StORM\Connection;

/**
 * @method onComplete(\Forms\Bridges\FormsSecurity\RegistrationForm $form, ?string $email, ?string $password, bool $confirmation, bool $emailAuthorization, ?string $token)
 * @method onAccountCreated(\Forms\Bridges\FormsSecurity\RegistrationForm $form, \Security\DB\Account $account)
 */
class RegistrationForm extends Form
{
	public const UNIQUE_LOGIN = '\Forms\Bridges\FormsSecurity\RegistrationForm::validateLogin';
	
	/**
	 * @var array<callable(static, \Security\DB\Account): void> Called when account is created
	 */
	public array $onAccountCreated = [];
	
	/**
	 * @var array<callable(static, ?string, ?string, bool, bool, ?string): void> Called when registration is composer
	 */
	public array $onComplete = [];
	
	protected Nette\Localization\Translator $translator;
	
	protected bool $confirmation;
	
	protected bool $emailAuthorization;

	protected AccountRepository $accountRepository;
	
	protected Nette\Mail\Mailer $mailer;
	
	protected Nette\Security\Passwords $passwords;
	
	public function __construct(
		bool $confirmation,
		bool $emailAuthorization,
		AccountRepository $accountRepository,
		Nette\Localization\Translator $translator,
		Nette\Security\Passwords $passwords,
		Nette\Mail\Mailer $mailSender
	) {
		parent::__construct();
		
		$this->translator = $translator;
		$this->mailer = $mailSender;
		$this->passwords = $passwords;
		$this->accountRepository = $accountRepository;
		$this->confirmation = $confirmation;
		$this->emailAuthorization = $emailAuthorization;
		
		$this->addText('login', 'registerForm.login')
			->addRule($this::UNIQUE_LOGIN, 'registerForm.account.alreadyExists', $accountRepository)
			->setRequired();
		
		$this->addPassword('password', 'registerForm.password');
		
		$this->addPassword('passwordCheck', 'registerForm.passwordCheck')
			->addRule($this::EQUAL, 'registerForm.passwordCheck.notEqual', $this['password']);

		$this->addAntispam('registerForm.badAntispam');
		
		$this->addSubmit('submit', 'registerForm.submit');
		
		$this->onSuccess[] = [$this, 'success'];
	}

	public function success(Nette\Forms\Form $form): void
	{
		$values = $form->getValues('array');
		$email = $values['email'] ?? $values['login'];
		$password = $values['password'] ?? Nette\Utils\Random::generate(8);
		
		$token = $this->emailAuthorization ? Nette\Utils\Random::generate(128) : null;
		
		/** @var \Security\DB\Account $account */
		$account = $this->accountRepository->createOne([
			'uuid' => Connection::generateUuid(),
			'login' => $values['login'],
			'password' => $this->passwords->hash($password),
			'active' => !$this->confirmation,
			'authorized' => !$this->emailAuthorization,
			'confirmationToken' => $token,
		]);
		
		$this->onAccountCreated($this, $account);
		
		$this->onComplete($this, $email, $password, $this->confirmation, $this->emailAuthorization, $token);
	}
	
	public static function validateLogin(\Nette\Forms\Control $control, AccountRepository $accountRepository): bool
	{
		return $accountRepository->many()->match(['login' => $control->getValue()])->isEmpty();
	}
}
