<?php

declare(strict_types=1);

namespace Forms\Controls;

use Nette\Application\Request;
use Nette\Application\UI\BadSignalException;
use Nette\Application\UI\ISignalReceiver;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Utils\Html;
use Nette\Utils\Image;
use Nette\Utils\Random;

/**
 * @method onDelete(array $directories, $filename)
 */
class UploadImage extends \Nette\Forms\Controls\UploadControl implements ISignalReceiver
{
	/**
	 * @var callable[]
	 */
	public array $onDelete = [];
	
	protected ?string $thumbBaseUrl = null;
	
	protected ?string $filename = null;
	
	protected ?string $infoText = null;
	
	protected ?int $thumbSize = 50;
	
	/**
	 * @var string[]
	 */
	protected array $directories;
	
	protected Html $deleteLink;
	
	public function __construct($label = null, array $directories = [], ?string $infoText = null)
	{
		parent::__construct($label, false);
		
		$this->addRule(\Nette\Forms\Form::IMAGE);
		$this->directories = $directories;
		$this->infoText = $infoText;
		$this->deleteLink = Html::el('a')->setAttribute("class", "btn btn-sm btn-danger button");
		
		$element = $this;
		
		$this->monitor(\Nette\Forms\Form::class, function ($form) use ($element): void {
			$element->onDelete[] = static function ($directories, $filename) use ($form): void {
				foreach (\array_keys($directories) as $directory) {
					@\unlink($form->getUserDir() . \DIRECTORY_SEPARATOR . $directory . \DIRECTORY_SEPARATOR . $filename);
				}
			};
			
			if ($element->thumbBaseUrl === null) {
				$element->thumbBaseUrl = $form->getUserUrl() . ($this->directories ? '/' . \key($this->directories) : '');
			}
			
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
	
	public function signalReceived(string $signal): void
	{
		if (!$this->filename) {
			return;
		}
		
		if ($signal !== 'delete') {
			$class = static::class;
			
			throw new BadSignalException("Missing handler for signal '$signal' in $class.");
		}
		
		/** @var \Nette\Application\UI\Form $form */
		$form = $this->getForm();
		
		if (!$form->getPresenter()->getRequest()->hasFlag(Request::RESTORED)) {
			$this->onDelete($this->directories, $this->filename);
		}
		
		return;
	}
	
	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Form::DATA_FILE);
		
		if ($this->value !== null) {
			return;
		}
		
		$this->value = new FileUpload(null);
	}
	
	public function upload(string $filenameFormat = '%1$s.%2$s'): ?string
	{
		/** @var \Forms\Form $form */
		$form = $this->getForm();
		
		if ($this->getHttpData(Form::DATA_TEXT) !== null) {
			return $this->getHttpData(Form::DATA_TEXT);
		}
		
		return self::uploadImage($this->getValue(), $form->getUserDir(), $this->directories, $filenameFormat);
	}
	
	public function setDeleteLink(Html $a): void
	{
		$this->deleteLink = $a;
	}
	
	public function getControl(): Html
	{
		/** @var \Forms\Form $form */
		$form = $this->getForm();
		
		$div = Html::el("div")->setAttribute('id', $this->getHtmlId() . '-container')->setAttribute('class', 'upload-image-container');
		
		if ($this->filename) {
			
			$link = $form->getPresenter()->link($this->lookupPath() . '-delete!');
			$this->deleteLink->setAttribute('href', $link);
			
			$src = $this->thumbBaseUrl . '/' . $this->filename . '?' . Random::generate();
			$div->addHtml(Html::el('img')
				->setAttribute('src', $src)
				->setAttribute('style', $this->thumbSize !== null ? 'max-height:'.$this->thumbSize.'px; max-width:'.$this->thumbSize.'px;' : 0));
			$div->addHtml(parent::getControl()->setType('hidden')->setValue($this->filename));
			
			$div->addHtml($this->deleteLink);
			
		} else {
			$div->addHtml(parent::getControl());
		}
		
		if ($this->infoText) {
			$div->addHtml('<div class="upload-image-infotext"><i class="fa fa-info-circle"></i> ' . $this->infoText . '</div>');
		}
		
		return $div;
	}
	
	protected static function uploadImage(FileUpload $upload, string $userDir, array $directories, string $filenameFormat = '%1$s.%2$s'): ?string
	{
		if ($upload->isOk() && $upload->isImage()) {
			$filename = null;
			
			foreach ($directories as $directory => $resizeClosure) {
				$filename = \sprintf($filenameFormat, \pathinfo($upload->getSanitizedName(), \PATHINFO_BASENAME), \strtolower(\pathinfo($upload->getSanitizedName(), \PATHINFO_EXTENSION)));
				$filepath = $userDir . \DIRECTORY_SEPARATOR . $directory . \DIRECTORY_SEPARATOR . $filename;
				
				$image = Image::fromFile($upload->getTemporaryFile());
				
				if ($resizeClosure instanceof \Closure) {
					\call_user_func_array($resizeClosure, [$image]);
				}
				
				$image->save($filepath);
			}
			
			return $filename;
		}
		
		return null;
	}
}
