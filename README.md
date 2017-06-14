# MIME upload validator

[![Build Status](https://travis-ci.org/silverstripe/silverstripe-mimevalidator.svg?branch=master)](https://travis-ci.org/silverstripe/silverstripe-mimevalidator)

## Introduction

Checks uploaded file content roughly matches a known MIME type for the file extension.

It can be used with `FileField` or any subclasses like `UploadField`.

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

### Adding MIME types

By default MIME types are checked against HTTP.MimeTypes config set in framework. This can be limiting as this only
allows for one MIME type per extension. To allow for multiple MIME types per extension, you can add these in your YAML
config as below:

	MimeUploadValidator:
  	  MimeTypes:
        ics:
          - 'text/plain'
          - 'text/calendar'

