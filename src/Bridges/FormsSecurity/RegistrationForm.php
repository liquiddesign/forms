<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

use Base\ShopsConfig;
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
	/**
	 * @var array<callable(static, \Security\DB\Account): void> Called when account is created
	 */
	public array $onAccountCreated = [];
	
	/**
	 * @var array<callable(static, ?string, ?string, bool, bool, ?string): void> Called when registration is composer
	 */
	public array $onComplete = [];

	/**
	 * Occurs when the form was validated
	 * @var array<callable(self, array|object): void|callable(array|object): void>
	 */
	public array $onValidate = [];
	
	public function __construct(
		protected readonly bool $confirmation,
		protected readonly bool $emailAuthorization,
		/**
		 * @var \Security\DB\AccountRepository<\Security\DB\Account>
		 */
		protected readonly AccountRepository $accountRepository,
		protected readonly Nette\Localization\Translator $translator,
		protected readonly Nette\Security\Passwords $passwords,
		protected readonly Nette\Mail\Mailer $mailSender,
		protected readonly ShopsConfig $shopsConfig
	) {
		parent::__construct();

		$loginInput = $this->addText('login', 'registerForm.login')
			->setRequired();
		
		$this->addPassword('password', 'registerForm.password');
		
		$this->addPassword('passwordCheck', 'registerForm.passwordCheck')
			->addRule($this::EQUAL, 'registerForm.passwordCheck.notEqual', $this['password']);

		$this->addAntispam('registerForm.badAntispam');
		
		$this->addSubmit('submit', 'registerForm.submit');

		$this->onValidate[] = function (RegistrationForm $form) use ($loginInput): void {
			if (!$form->isValid()) {
				return;
			}

			/** @var array<mixed> $values */
			$values = $form->getValues('array');

			$query = $this->accountRepository->many()->where('this.login', $values['login']);

			$this->shopsConfig->filterShopsInShopEntityCollection($query);

			/** @var \Security\DB\Account|null $account */
			$account = $query->first();

			if (!$account) {
				return;
			}

			$loginInput->addError($this->translator->translate('registerForm.accountAlreadyExists', 'Účet s tímto loginem již existuje.'));
			$form->addError($this->translator->translate('registerForm.accountAlreadyExists', 'Účet s tímto loginem již existuje.'));
		};
		
		$this->onSuccess[] = [$this, 'success'];
	}

	public function success(Nette\Forms\Form $form): void
	{
		/** @var array<mixed> $values */
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
			'shop' => $this->shopsConfig->getSelectedShop()?->getPK(),
		]);
		
		$this->onAccountCreated($this, $account);
		
		$this->onComplete($this, $email, $password, $this->confirmation, $this->emailAuthorization, $token);
	}
}
