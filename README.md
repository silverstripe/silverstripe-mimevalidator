# MIME upload validator

## Introduction

Checks uploaded file content roughly matches a known MIME type for the file extension.

It can be used with either `FileField` or `UploadField`.

For example, it will fail validation if someone renames a `.exe` file to `.jpg`
and attempts to upload the file.

## Requirements

 * SilverStripe 3.1+
 * fileinfo PHP extension

## Installation via Composer

	cd path/to/my/silverstripe/site
	composer require "silverstripe/mimevalidator:*"

## Configuration

The validator is not used by default. It can be enabled in a couple of ways:

### Enable globally

In your `mysite/_config/config.yml` file:

	Injector:
	  Upload_Validator:
	    class: MimeUploadValidator

NOTE: This will *not* work in SilverStripe 3.1.5, as there is no injector
support for `Upload_Validator`. You will need to wait until 3.1.6 or greater
is released, or use the 3.1 branch of framework in your project.

### Enable on an individual upload field

	$field = UploadField::create();
	$field->setValidator(new MimeUploadValidator());

