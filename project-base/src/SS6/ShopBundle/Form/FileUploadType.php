<?php

namespace SS6\ShopBundle\Form;

use SS6\ShopBundle\Form\FormType;
use SS6\ShopBundle\Model\FileUpload\FileUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ExecutionContextInterface;

class FileUploadType extends AbstractType implements DataTransformerInterface {

	/**
	 * @var \SS6\ShopBundle\Model\FileUpload\FileUpload
	 */
	private $fileUpload;

	/**
	 * @var \Symfony\Component\Validator\Constraint[]
	 */
	private $constraints;

	/**
	 * @var bool
	 */
	private $required;

	/**
	 * @param \SS6\ShopBundle\Model\FileUpload\FileUpload $fileUpload
	 */
	public function __construct(FileUpload $fileUpload) {
		$this->fileUpload = $fileUpload;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'file_upload';
	}

	/**
	 * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'error_bubbling' => false,
			'compound' => true,
			'file_constraints' => [],
			'multiple' => false,
		]);
	}

	/**
	 * @param array $value
	 * @return string
	 */
	public function reverseTransform($value) {
		return $value['uploadedFiles'];
	}

	/**
	 * @param string $value
	 * @return array
	 */
	public function transform($value) {
		return [
			'uploadedFiles' => (array)$value,
			'file' => null,
		];
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$this->required = $options['required'];
		$this->constraints = $options['file_constraints'];

		$builder->addModelTransformer($this);
		$builder
			->add('uploadedFiles', FormType::COLLECTION, [
				'type' => 'hidden',
				'allow_add' => true,
				'constraints' => [
					new Constraints\Callback([$this, 'validateUploadedFiles']),
				],
			])
			->add('file', FormType::FILE, [
				'multiple' => $options['multiple'],
			]);

		// fallback for IE9 and older
		$builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
	}

	/**
	 * @param string|null $uploadedFiles
	 * @param \Symfony\Component\Validator\ExecutionContextInterface $context
	 */
	public function validateUploadedFiles($uploadedFiles, ExecutionContextInterface $context) {
		if ($this->required || count($uploadedFiles) > 0) {
			foreach ($uploadedFiles as $uploadedFile) {
				$filepath = $this->fileUpload->getTemporaryFilepath($uploadedFile);
				$file = new File($filepath, false);
				$context->validateValue($file, $this->constraints);
			}
		}
	}

	/**
	 * @param \Symfony\Component\Form\FormEvent $event
	 */
	public function onPreSubmit(FormEvent $event) {
		$data = $event->getData();
		if (is_array($data) && array_key_exists('file', $data) && is_array($data['file'])) {
			$fallbackFiles = $data['file'];
			foreach ($fallbackFiles as $file) {
				if ($file instanceof UploadedFile) {
					try {
						$data['uploadedFiles'][] = $this->fileUpload->upload($file);
					} catch (\SS6\ShopBundle\Model\FileUpload\Exception\FileUploadException $ex) {
						$event->getForm()->addError('Nahrání souboru se nezdařilo.');
					}
				}
			}

			$event->setData($data);
		}
	}

}
