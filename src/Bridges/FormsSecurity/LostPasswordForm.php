<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

use Base\ShopsConfig;
use Nette;
use Security\DB\AccountRepository;
use Security\DB\IUser;
use StORM\DIConnection;
use StORM\Repository;

/**
 * @method onRecover(\Forms\Bridges\FormsSecurity\LostPasswordForm $form)
 */
class LostPasswordForm extends \Nette\Application\UI\Form
{
	/**
	 * @var array<callable(static): void> Called when recover success
	 */
	public array $onRecover = [];
	
	public ?string $token;
	
	protected Nette\Mail\Mailer $mailer;
	
	protected Repository $repository;
	
	private AccountRepository $accountRepository;
	
	public function __construct(
		DIConnection $connection,
		Nette\Localization\Translator $translator,
		Nette\Mail\Mailer $mailer,
		AccountRepository $accountRepository,
		ShopsConfig $shopsConfig,
		?string $class = null
	) {
		parent::__construct();
		
		$this->accountRepository = $accountRepository;
		$this->mailer = $mailer;
		
		if ($class) {
			if (!(new \ReflectionClass($class))->implementsInterface(IUser::class)) {
				throw new \InvalidArgumentException("Wrong or empty class: $class");
			}
			
			$this->repository = $connection->findRepository($class);
		} else {
			$this->repository = $accountRepository;
		}
		
		$emailInput = $this->addText('email', $translator->translate('lostPasswordForm.email', 'Email'))
			->addRule($this::EMAIL)
			->setRequired();

		$this->onValidate[] = function (LostPasswordForm $form) use ($shopsConfig, $emailInput, $translator): void {
			if (!$form->isValid()) {
				return;
			}

			$values = $form->getValues('array');

			$query = $this->accountRepository->many()->where('this.login', $values['email']);
			
			$shopsConfig->filterShopsInShopEntityCollection($query);

			/** @var \Security\DB\Account|null $account */
			$account = $query->first();
			
			if ($account && $account->isActive()) {
				return;
			}

			$emailInput->addError($translator->translate('lostPasswordForm.emailNotFound', 'Email nenalezen!'));
		};
		
		$this->addSubmit('submit');
		
		$this->onSuccess[] = [$this, 'success'];
	}
	
	public function success(Nette\Forms\Form $form): void
	{
		$values = $form->getValues('array');
		
		$this->token = Nette\Utils\Random::generate(128);
		
		$this->accountRepository->one(['login' => $values['email']])->update(['confirmationToken' => $this->token]);
		
		$this->onRecover($this);
	}
}
