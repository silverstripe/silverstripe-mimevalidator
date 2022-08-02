# MIME upload validator

[![CI](https://github.com/silverstripe/silverstripe-mimevalidator/actions/workflows/ci.yml/badge.svg)](https://github.com/silverstripe/silverstripe-mimevalidator/actions/workflows/ci.yml)
[![Silverstripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

## Introduction

Checks uploaded file content roughly matches a known MIME type for the file extension.

It can be used with `FileField` or any subclasses like `UploadField`.

For example, it will fail validation if someone renames a `.exe` file to `.jpg`
and attempts to upload the file.

## Requirements

 * Silverstripe 4.0+
 * fileinfo [PHP extension](http://php.net/manual/en/intro.fileinfo.php)

**Note:** For a Silverstripe 3.x compatible version, please use [the 1.x release line](https://github.com/silverstripe/silverstripe-mimevalidator/tree/1.0).

## Installation via Composer

Install with composer by running `composer require silverstripe/mimevalidator` in the root of your Silverstripe project.

Silverstripe CMS Recipe 4.6 and above include this module via `silverstripe/recipe-core`.
Therefore, it is unnecessary to directly install this module if your project has been upgraded to,
or was created with CMS Recipe 4.6.0 or later.

## Configuration

Read [Allowed file types on the Silverstripe CMS documentation](https://docs.silverstripe.org/en/4/developer_guides/files/allowed_file_types/) for details on configuring MIME type validation.
