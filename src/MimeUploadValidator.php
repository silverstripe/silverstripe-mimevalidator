<?php

namespace SilverStripe\MimeValidator;

use finfo;
use Exception;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTP;
use SilverStripe\Assets\Upload_Validator;


/**
 * Adds an additional validation rule to Upload_Validator that attempts to detect
 * the file extension of an uploaded file matches it's contents, which is done
 * by detecting the MIME type and doing a fuzzy match.
 *
 * Class MimeUploadValidator
 * @package SilverStripe\MimeValidator
 */
class MimeUploadValidator extends Upload_Validator
{
    /**
     * The preg_replace() pattern to use against MIME types. Used to strip out
     * useless characters so matching of MIME types can be fuzzy.
     *
     * @var string Regexp pattern
     */
    protected $filterPattern = '/.*[\/\.\-\+]/i';

    /**
     * @param string $pattern
     */
    public function setFilterPattern($pattern)
    {
        $this->filterPattern = $pattern;
    }

    /**
     * @return string
     */
    public function getFilterPattern()
    {
        return $this->filterPattern;
    }

    /**
     * Check if the temporary file has a valid MIME type for it's extension.
     *
     * @uses finfo php extension
     * @return bool|null
     * @throws MimeUploadValidator_Exception
     */
    public function isValidMime()
    {
        $extension = strtolower(pathinfo($this->tmpFile['name'], PATHINFO_EXTENSION));

        // we can't check filenames without an extension or no temp file path, let them pass validation.
        if (!$extension || !$this->tmpFile['tmp_name']) {
            return true;
        }

        $expectedMimes = $this->getExpectedMimeTypes($this->tmpFile);
        if (empty($expectedMimes)) {
            throw new MimeUploadValidator_Exception(
                sprintf('Could not find a MIME type for extension %s', $extension)
            );
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $foundMime = $fileInfo->file($this->tmpFile['tmp_name']);
        if (!$foundMime) {
            throw new MimeUploadValidator_Exception(
                sprintf('Could not find a MIME type for file %s', $this->tmpFile['tmp_name'])
            );
        }

        foreach ($expectedMimes as $expected) {
            if ($this->compareMime($foundMime, $expected)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetches an array of valid mimetypes.
     *
     * @param $file
     * @return array
     * @throws MimeUploadValidator_Exception
     */
    public function getExpectedMimeTypes($file)
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // if the finfo php extension isn't loaded, we can't complete this check.
        if (!class_exists('finfo')) {
            throw new MimeUploadValidator_Exception('PHP extension finfo is not loaded');
        }

        // Attempt to figure out which mime types are expected/acceptable here.
        $expectedMimes = array();

        // Get the mime types set in framework core
        $knownMimes = Config::inst()->get(HTTP::class, 'MimeTypes');
        if (isset($knownMimes[$extension])) {
            $expectedMimes[] = $knownMimes[$extension];
        }

        // Get the mime types and their variations from mime validator
        $knownMimes = Config::inst()->get(get_class($this), 'MimeTypes');
        if (isset($knownMimes[$extension])) {
          $mimes = (array) $knownMimes[$extension];

          foreach($mimes as $mime){
              if (!in_array($mime,$expectedMimes)) {
                  $expectedMimes[] = $mime;
              }
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
    public function compareMime($first, $second)
    {
        return preg_replace($this->filterPattern, '', $first) === preg_replace($this->filterPattern, '', $second);
    }

    public function validate()
    {
        if (parent::validate() === false) {
            return false;
        }

        try {
            $result = $this->isValidMime();

            if ($result === false) {
                $this->errors[] = _t(
                    'File.INVALIDMIME',
                    'File extension does not match known MIME type'
                );

                return false;
            }
        } catch (MimeUploadValidator_Exception $e) {
            $this->errors[] = _t(
                'File.FAILEDMIMECHECK',
                'MIME validation failed: {message}',
                'Argument 1: Message about why MIME type detection failed',
                ['message' => $e->getMessage()]
            );

            return false;
        }

        return true;
    }
}

class MimeUploadValidator_Exception extends Exception
{
}
