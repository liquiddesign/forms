<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

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
	public const EMAIL_EXISTS = '\Forms\Bridges\FormsSecurity\LostPasswordForm::validateEmail';
	
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
		
		$this->addText('email', $translator->translate('lostPasswordForm.email', 'Email'))
			->addRule($this::EMAIL)
			->addRule(
				$this::EMAIL_EXISTS,
				$translator->translate('lostPasswordForm.emailNotFound', 'Email nenalezen!'),
				$this->repository,
			)
			->setRequired();
		
		$this->addSubmit('submit');
		
		$this->onSuccess[] = [$this, 'success'];
	}
	
	public function success(Nette\Forms\Form $form): void
	{
		$values = $form->getValues('array');
		
		$this->token = Nette\Utils\Random::generate(128);
		
		$this->accountRepository->many()->match(['login', $values['email']])->update(['confirmationToken' => $this->token]);
		
		$this->onRecover($this);
	}
	
	public static function validateEmail(\Nette\Forms\Control $control, Repository $repository): bool
	{
		return !$repository->many()->match(['login' => $control->getValue()])->isEmpty();
	}
}
