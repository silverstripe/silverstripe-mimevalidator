<?php
/**
 * A basic controller that can be used for admin users/developers to upload a file to test it's MIME type
 *
 * This is the best way to identify a file's MIME type as it will check against the server's definition of MIME types
 * which may vary across different operating systems and versions of php.
 *
 * This also emulates how MimeUploadValidator validates file MIME types, by checking against a file's tmp_name from
 * the temporary file created rather than the file directly.
 *
 * @see MimeUploadValidator.php
 */
class FileMIMETester extends Controller {

	public static $allowed_actions = array(
		'Form',
		'doUpload',
		'complete'
	);

	public function init() {
		parent::init();

		// Only allow admin users to access this page
		if(!Permission::check('ADMIN')) Security::permissionFailure();
	}

	/**
	 * Generate the title for the page
	 * @return string
	 */
	public function Title() {
		return "File MIME Tester";
	}

	/**
	 * Generate the content for the page as there is no actual database data
	 * @return HTML
	 */
	public function Content() {
		return <<<HTML
<p>This form will let you upload a file to test the MIME type determined by the server.</p>
<p>This can be used to check MIME types of files on a server by server basis as they may differ between operating 
systems.</p>
<p>Note: The file will be deleted after upload.</p>
HTML;
	}

	/**
	 * Basic form to upload a file
	 * @return Form $form
	 */
	public function Form() {
		$form = new Form(
			$this,
			"Form",
			new FieldList(
				$file = new FileField("File", "File")
			),
			new FieldList(
				new FormAction("doUpload", "Upload")
			)
		);

		// use our test validator to bypass upload validation
		$file->setValidator(new MIMETest_Validator());

		return $form;
	}

	/**
	 * Method which checks the uploaded file MIME type
	 * 
	 * @param array $data
	 * @param Form $form
	 * @return void
	 */
	public function doUpload($data, $form) {
		// get upload object to get file info from
		$upload = $form->Fields()->dataFieldByName('File')->getUpload();

		// get the tmp file info
		$tmpFile = $upload->getValidator()->getTmpFile();
		
		$finfo = new finfo(FILEINFO_MIME_TYPE);

		// get the mime type from the tmp file path as this is how the MimeUploadValidator behaves
		$mimeType = $finfo->file($tmpFile['tmp_name']);

		$this->redirect($this->Link() . 'complete?type=' . $mimeType);
	}

	/**
	 * The form success method that displays the MIME type found from the uploaded file
	 * @return array
	 */
	public function complete() {
		$getVars = $this->request->getVars();

		// get the MIME type from the URL
		$type = isset($getVars['type']) ? $getVars['type'] : '';

		return array(
			'Content' => sprintf(
				'<p>File MIME type: %s</p><p><a href="%s">Try another file</a></p>',
				$type,
				$this->Link()
			),
			'Form' => ''
		);
	}

}

/**
 * A test validator that always returns true when validating
 *
 * This can be used to bypass upload form field validators when needed, to run tests without having to ensure
 * the custom validation is met
 *
 * Note: The upload size limit will still be enforced
 */
class MIMETest_Validator extends Upload_Validator {

	/**
	 * Custom getter to access the protected $tmpFile data
	 * @return array|null
	 */
	public function getTmpFile() {
		return $this->tmpFile;
	}

	/**
	 * Overriding validate method to bypass any validation as we are just testing for a MIME type
	 * @return bool
	 */
	public function validate() {
		return true;
	}

}