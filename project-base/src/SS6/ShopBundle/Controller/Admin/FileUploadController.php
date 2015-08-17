<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Component\Controller\AdminBaseController;
use SS6\ShopBundle\Model\FileUpload\FileUpload;
use SS6\ShopBundle\Twig\FileThumbnail\FileThumbnailExtension;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FileUploadController extends AdminBaseController {

	/**
	 * @var \SS6\ShopBundle\Model\FileUpload\FileUpload
	 */
	private $fileUpload;

	/**
	 * @var \SS6\ShopBundle\Twig\FileThumbnail\FileThumbnailExtension
	 */
	private $fileThumbnailExtension;

	public function __construct(
		FileUpload $fileUpload,
		FileThumbnailExtension $fileThumbnailExtension
	) {
		$this->fileUpload = $fileUpload;
		$this->fileThumbnailExtension = $fileThumbnailExtension;
	}

	/**
	 * @Route("/file_upload/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function uploadAction(Request $request) {
		$actionResult = [
			'status' => 'error',
			'code' => 0,
			'filename' => '',
			'message' => 'Došlo k neočekávané chybě, soubor nebyl nahrán.',
		];
		$file = $request->files->get('file');

		if ($file instanceof UploadedFile) {
			try {
				$temporaryFilename = $this->fileUpload->upload($file);
				$fileThumbnailInfo = $this->fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

				$actionResult = [
					'status' => 'success',
					'filename' => $temporaryFilename,
					'iconType' => $fileThumbnailInfo->getIconType(),
					'imageThumbnailUri' => $fileThumbnailInfo->getImageUri(),
				];
				$actionResult['status'] = 'success';
				$actionResult['filename'] = $temporaryFilename;
			} catch (\SS6\ShopBundle\Model\FileUpload\Exception\FileUploadException $ex) {
				$actionResult['status'] = 'error';
				$actionResult['code'] = $ex->getCode();
				$actionResult['message'] = $ex->getMessage();
			}
		}

		return new JsonResponse($actionResult);
	}

	/**
	 * @Route("/file_upload/delete_temporary_file/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function deleteTemporaryFileAction(Request $request) {
		$filename = $request->get('filename');
		$actionResult = $this->fileUpload->tryDeleteTemporaryFile($filename);

		return new JsonResponse($actionResult);
	}

}
