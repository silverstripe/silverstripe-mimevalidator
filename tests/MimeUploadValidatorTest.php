<?php
class MimeUploadValidatorTest extends SapphireTest {

	public function testInvalidFileExtensionValidatingMimeType() {
		// setup plaintext file with invalid extension
		$tmpFileName = 'UploadTest-testUpload.jpg';
		$tmpFilePath = TEMP_FOLDER . '/' . $tmpFileName;
		$tmpFileContent = '';
		for($i=0; $i<10000; $i++) $tmpFileContent .= '0';
		file_put_contents($tmpFilePath, $tmpFileContent);

		// emulates the $_FILES array
		$tmpFile = array(
			'name' => $tmpFileName,
			'size' => filesize($tmpFilePath),
			'tmp_name' => $tmpFilePath,
			'extension' => 'jpg',
			'error' => UPLOAD_ERR_OK,
		);

		$u = new Upload();
		$u->setValidator(new MimeUploadValidator());
		$result = $u->load($tmpFile);
		$errors = $u->getErrors();
		$this->assertFalse($result, 'Load failed because file extension does not match excepted MIME type');
		$this->assertEquals('File extension does not match known MIME type', $errors[0]);

		unlink($tmpFilePath);
	}


	public function testGetExpectedMimeTypes() {
		// Setup a file with a capitalised extension and try to match it against a lowercase file.
		$tmpFileName = 'text.TXT';
		$tmpFilePath = TEMP_FOLDER . '/' . $tmpFileName;
		$tmpFileContent = '';
		for($i=0; $i<10000; $i++) $tmpFileContent .= '0';
		file_put_contents($tmpFilePath, $tmpFileContent);

		$validator = new MimeUploadValidator();
		$tmpFile = array(
			'name' => $tmpFileName,
			'tmp_name' => $tmpFilePath,
		);
		$expected = $validator->getExpectedMimeTypes($tmpFile);
		$this->assertCount(1, $expected);
		$this->assertContains('text/plain', $expected);

		unlink($tmpFilePath);

		// Test a physical ico file with capitalised extension
		$tmpFile = array(
			'name' => 'favicon.ICO',
			'tmp_name' => 'assets/favicon.ICO',
		);
		$expected = $validator->getExpectedMimeTypes($tmpFile);
		$this->assertCount(3, $expected);
	}

	public function testMimeComparison() {
		$validator = new MimeUploadValidator();

		$this->assertTrue($validator->compareMime('application/xhtml+xml', 'application/xml'));
		$this->assertTrue($validator->compareMime('application/vnd.text', 'application/text'));
		$this->assertTrue($validator->compareMime('application/vnd.vnd.text', 'application/text'));
		$this->assertTrue($validator->compareMime('application/x-text', 'application/text'));
		$this->assertTrue($validator->compareMime('application/gzip', 'application/gzip'));
		$this->assertTrue($validator->compareMime('application/x-gzip', 'application/gzip'));
		$this->assertFalse($validator->compareMime('application/png', 'application/json'));
		$this->assertFalse($validator->compareMime('text/plain', 'text/json'));
	}

}
