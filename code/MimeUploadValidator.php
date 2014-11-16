<?php
/**
 * Adds an additional validation rule to Upload_Validator that attempts to detect
 * the file extension of an uploaded file matches it's contents, which is done
 * by detecting the MIME type and doing a fuzzy match.
 */
class MimeUploadValidator extends Upload_Validator {

	/**
	 * The preg_replace() pattern to use against MIME types. Used to strip out
	 * useless characters so matching of MIME types can be fuzzy.
	 *
	 * @var string Regexp pattern
	 */
	protected $filterPattern = '/.*[\/\.\-\+]/i';

	public function setFilterPattern($pattern) {
		$this->filterPattern = $pattern;
	}

	public function getFilterPattern() {
		return $this->filterPattern;
	}

	/**
	 * Check if the temporary file has a valid MIME type for it's extension.
	 *
	 * @uses finfo php extension
	 * @return boolean|null
	 */
	public function isValidMime() {
		$expectedMimes = $this->getExpectedMimeTypes($this->tmpFile);
		if(empty($expectedMimes)) {
			throw new MimeUploadValidator_Exception(
				sprintf('Could not find a MIME type for extension %s', $extension)
			);
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$foundMime = $finfo->file($this->tmpFile['tmp_name']);
		if(!$foundMime) {
			throw new MimeUploadValidator_Exception(
				sprintf('Could not find a MIME type for file %s', $this->tmpFile['tmp_name'])
			);
		}

		foreach($expectedMimes as $expected) {
			if($this->compareMime($foundMime, $expected)) return true;
		}
		return false;
	}

	/**
	 * Fetches an array of valid mimetypes.
	 *
	 * @return array
	 */
	public function getExpectedMimeTypes($tmpFile) {
		$extension = strtolower(pathinfo($tmpFile['name'], PATHINFO_EXTENSION));

		// we can't check filenames without an extension or no temp file path, let them pass validation.
		if(!$extension || !$tmpFile['tmp_name']) return true;

		// if the finfo php extension isn't loaded, we can't complete this check.
		if(!class_exists('finfo')) {
			throw new MimeUploadValidator_Exception('PHP extension finfo is not loaded');
		}

		// Attempt to figure out which mime types are expected/acceptable here.
		$expectedMimes = array();

		// Get the mime types set in framework core
		$knownMimes = Config::inst()->get('HTTP', 'MimeTypes');
		if(isset($knownMimes[$extension])) {
			$expectedMimes[] = $knownMimes[$extension];
		}

		// Get the mime types and their variations from mimevalidator
		$knownMimes = Config::inst()->get(get_class($this), 'MimeTypes');
		if(isset($knownMimes[$extension])) {
			if(is_array($knownMimes[$extension])) {
				$expectedMimes += $knownMimes[$extension];
			} else {
				$expectedMimes[] = $knownMimes[$extension];
			}
		}
		return $expectedMimes;
	}

	/**
	 * Check two MIME types roughly match eachother.
	 *
	 * Before we check MIME types, remove known prefixes "vnd.", "x-" etc.
	 * If there is a suffix, we'll use that to compare. Examples:
	 *
	 * application/x-json = json
	 * application/json = json
	 * application/xhtml+xml = xml
	 * application/xml = xml
	 *
	 * @param string $first The first MIME type to compare to the second
	 * @param string $second The second MIME type to compare to the first
	 * @return boolean
	 */
	public function compareMime($first, $second) {
		return preg_replace($this->filterPattern, '', $first) === preg_replace($this->filterPattern, '', $second);
	}

	public function validate() {
		if(parent::validate() === false) return false;

		try {
			$result = $this->isValidMime();
			if($result === false) {
				$this->errors[] = _t(
					'File.INVALIDMIME',
					'File extension does not match known MIME type'
				);
				return false;
			}
		} catch(MimeUploadValidator_Exception $e) {
			$this->errors[] = _t(
				'File.FAILEDMIMECHECK',
				'MIME validation failed: {message}',
				'Argument 1: Message about why MIME type detection failed',
				array('message' => $e->getMessage())
			);
			return false;
		}

		return true;
	}

}

class MimeUploadValidator_Exception extends Exception {

}

