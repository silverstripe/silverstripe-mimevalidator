# MIME upload validator

[![Build Status](http://img.shields.io/travis/silverstripe/silverstripe-mimevalidator.svg?style=flat)](https://travis-ci.org/silverstripe/silverstripe-mimevalidator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mimevalidator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mimevalidator/?branch=master)
[![codecov](https://codecov.io/gh/silverstripe/silverstripe-mimevalidator/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe/silverstripe-mimevalidator)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

## Introduction

Checks uploaded file content roughly matches a known MIME type for the file extension.

It can be used with `FileField` or any subclasses like `UploadField`.

For example, it will fail validation if someone renames a `.exe` file to `.jpg`
and attempts to upload the file.

## Requirements

 * SilverStripe 4.0+
 * fileinfo [PHP extension](http://php.net/manual/en/intro.fileinfo.php)

**Note:** For a SilverStripe 3.x compatible version, please use [the 1.x release line](https://github.com/silverstripe/silverstripe-mimevalidator/tree/1.0).

## Installation via Composer

Install with composer by running `composer require silverstripe/mimevalidator` in the root of your SilverStripe project.

## Configuration

The validator is not used by default. It can be enabled in a couple of ways:

### Enable globally

In your `mysite/_config/config.yml` file:

```yml
SilverStripe\Core\Injector\Injector:
  SilverStripe\Assets\Upload_Validator:
    class: SilverStripe\MimeValidator\MimeUploadValidator
```

### Enable on an individual upload field

```php
$field = UploadField::create();
$field->setValidator(MimeUploadValidator::create());
```

### Adding MIME types

By default MIME types are checked against HTTP.MimeTypes config set in framework. This can be limiting as this only
allows for one MIME type per extension. To allow for multiple MIME types per extension, you can add these in your YAML
config as below:

```yml
SilverStripe\MimeValidator\MimeUploadValidator:
  MimeTypes:
    ics:
      - 'text/plain'
      - 'text/calendar'
```
