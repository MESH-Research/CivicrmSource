<?php
/**
 * COmanage Registry CiviCRM Source Plugin Language File
 *
 * Copyright (C) 2020 Modern Language Association
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright     Copyright (C) 2020 Modern Language Association
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.2.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @version       $Id$
 */

global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_civicrm_source_texts['en_US'] = array(
  // Titles, per-controller
  'ct.civicrm_sources.1'  => 'CiviCRM Organizational Identity Source',
  'ct.civicrm_sources.pl' => 'CiviCRM Organizational Identity Sources',

  // Error messages
  'er.civicrmsource.connect'        => 'Failed to connect to CiviCRM API',

  // Plugin texts
  'pl.civicrmsource.info'           => 'The CiviCRM API server must be available and the specified credentials must be valid before this configuration can be saved.',
  'pl.civicrmsource.apiroot'        => 'API Root',
  'pl.civicrmsource.apiroot.desc'   => 'URL prefix for the API, including schema and host (eg: https://api.xxx.org/path)',
  'pl.civicrmsource.eppnsuffix'     => 'EPPN Suffix',
  'pl.civicrmsource.eppnsuffix.desc' => 'Suffix to append to WordPress username to generate eppn (include @, eg: @idp.xxx.org)',
  'pl.civicrmsource.sitekey'        => 'Site Key',
  'pl.civicrmsource.sitekey.desc'   => 'CiviCRM Site Key',
  'pl.civicrmsource.userkey'        => 'API Key',
  'pl.civicrmsource.userkey.desc'   => 'CiviCRM API Key'
);
