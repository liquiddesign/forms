<?php

declare(strict_types=1);

namespace Forms\Controls;

use Nette\Application\Request;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\BadSignalException;
use Nette\Application\UI\ISignalReceiver;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Utils\Html;

/**
 * @method onDelete(string $directory, $filename)
 */
class UploadFile extends \Nette\Forms\Controls\UploadControl implements ISignalReceiver
{
	/**
	 * @var callable[]
	 */
	public array $onDelete = [];
	
	protected ?string $filename = null;
	
	protected ?string $infoText;
	
	protected ?string $directory;
	
	protected ?Html $deleteLink;
	
	protected ?Html $downloadLink;
	
	public function __construct($label = null, ?string $directory = null, ?string $infoText = null)
	{
		parent::__construct($label, false);
		
		$this->infoText = $infoText;
		$this->directory = $directory;
		$this->deleteLink = Html::el('a')->setAttribute("class", 'btn btn-sm btn-danger button');
		$this->downloadLink = Html::el('a')->setAttribute('class', 'download-link');
		
		$element = $this;
		
		$this->monitor(\Nette\Forms\Form::class, function ($form) use ($element): void {
			$element->onDelete[] = static function ($directory, $filename) use ($form): void {
				@\unlink($form->getUserDir() . \DIRECTORY_SEPARATOR . $directory . \DIRECTORY_SEPARATOR . $filename);
			};
			
			$element->deleteLink->setText($form->getTranslator() ? $form->getTranslator()->translate('delete') : 'delete');
			
			return;
		});
	}
	
	/**
	 * @param mixed $value
	 * @return static
	 */
	public function setValue($value)
	{
		$this->filename = $value;
		
		return $this;
	}
	
	/**
	 * Performs the server side validation.
	 */
	public function validate(): void
	{
		if ($this->getValue()) {
			return;
		}
		
		parent::validate();
	}
	
	public function upload(string $filenameFormat = '%1$s.%2$s'): ?string
	{
		$upload = $this->getValue();
		$filename = null;
		
		if ($this->getHttpData(Form::DATA_TEXT) !== null) {
			return $this->getHttpData(Form::DATA_TEXT);
		}
		
		if ($upload->isOk()) {
			/** @var \Forms\Form $form */
			$form = $this->getForm();
			
			$filename = \sprintf($filenameFormat, \pathinfo($upload->getSanitizedName(), \PATHINFO_BASENAME), \strtolower(\pathinfo($upload->getSanitizedName(), \PATHINFO_EXTENSION)));
			$filepath = $form->getUserDir() . \DIRECTORY_SEPARATOR . $this->directory . \DIRECTORY_SEPARATOR . $filename;
			$upload->move($filepath);
		}
		
		return $filename;
	}
	
	public function signalReceived(string $signal): void
	{
		if (!$this->filename) {
			return;
		}
		
		/** @var \Forms\Form $form */
		$form = $this->getForm();
		
		if ($signal === 'delete') {
			if (!$form->getPresenter()->getRequest()->hasFlag(Request::RESTORED)) {
				$this->onDelete($this->directory, $this->filename);
			}
			
			return;
		}
		
		if ($signal === 'download') {
			$filePath = $form->getUserDir() . \DIRECTORY_SEPARATOR . ($this->directory ? $this->directory . \DIRECTORY_SEPARATOR : '') . $this->filename;
			$form->getPresenter()->sendResponse(new FileResponse($filePath));
		}
		
		$class = static::class;
		
		throw new BadSignalException("Missing handler for signal '$signal' in $class.");
	}
	
	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Form::DATA_FILE);
		
		if ($this->value !== null) {
			return;
		}
		
		$this->value = new FileUpload(null);
	}
	
	public function setDeleteLink(?Html $a): void
	{
		$this->deleteLink = $a;
	}
	
	public function setDownloadLink(?Html $a): void
	{
		$this->downloadLink = $a;
	}
	
	public function getControl(): Html
	{
		/** @var \Nette\Application\UI\Form $form */
		$form = $this->getForm();
		
		$div = Html::el("div");
		
		if ($this->filename) {
			if ($this->downloadLink) {
				$downloadLink = $form->getPresenter()->link($this->lookupPath() . '-download!');
				$this->downloadLink->setAttribute('href', $downloadLink)->setText($this->filename);
				$div->addHtml($this->downloadLink);
			}
			
			$div->addHtml(parent::getControl()->setType('hidden')->setValue($this->filename));
			
			if ($this->deleteLink) {
				$deleteLink = $form->getPresenter()->link($this->lookupPath() . '-delete!');
				$this->deleteLink->setAttribute('href', $deleteLink);
				$div->addHtml($this->deleteLink);
			}
		} else {
			
			$div->addHtml(parent::getControl());
		}
		
		if ($this->infoText) {
			$div->addHtml('<div class="upload-image-infotext"><i class="fa fa-info-circle"></i> ' . $this->infoText . '</div>');
		}
		
		return $div;
	}
}
