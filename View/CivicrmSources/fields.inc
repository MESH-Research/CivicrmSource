<!--
/**
 * COmanage Registry CiviCRM Source Plugin Fields
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
-->
<?php
  // Determine if fields are editable
  $e = false;
  
  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  
  if(!$e && !$permissions['view'])
    return false;
  
  print $this->Form->hidden('org_identity_source_id', array('default' => $vv_oisid)) . "\n";

?>
<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> 
  <p>
    <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
    <strong><?php print _txt('pl.civicrmsource.info'); ?></strong>
  </p>
</div>
<br />
<table id="<?php print $this->action; ?>_civicrm_source" class="ui-widget">
  <tbody>
    <tr class="line1">
      <td style="width:50%">
        <strong class="fieldTitle"><?php print _txt('pl.civicrmsource.apiroot'); ?></strong><font class="required">*</font><br />
        <font class="desc"><?php print _txt('pl.civicrmsource.apiroot.desc'); ?></font>
      </td>
      <td>
        <?php print ($e ? $this->Form->input('apiroot', array('size' => 50)) : Sanitize::html($civicrm_sources[0]['CivicrmSource']['apiroot'])); ?>
      </td>
    </tr>
    <tr class="line2">
      <td>
        <strong class="fieldTitle"><?php print _txt('pl.civicrmsource.sitekey'); ?></strong><br />
        <font class="desc"><?php print _txt('pl.civicrmsource.sitekey.desc'); ?></font>
      </td>
      <td>
        <?php print ($e ? $this->Form->input('sitekey', array('size' => 50)) : Sanitize::html($civicrm_sources[0]['CivicrmSource']['sitekey'])); ?>
      </td>
    </tr>
    <tr class="line1">
      <td>
        <strong class="fieldTitle"><?php print _txt('pl.civicrmsource.userkey'); ?></strong><br />
        <font class="desc"><?php print _txt('pl.civicrmsource.userkey.desc'); ?></font>
      </td>
      <td>
        <?php print ($e ? $this->Form->input('userkey') : Sanitize::html($civicrm_sources[0]['CivicrmSource']['userkey'])); ?>
      </td>
    </tr>
    <tr class="line2">
      <td>
        <strong class="fieldTitle"><?php print _txt('pl.civicrmsource.eppnsuffix'); ?></strong><br />
        <font class="desc"><?php print _txt('pl.civicrmsource.eppnsuffix.desc'); ?></font>
      </td>
      <td>
        <?php print ($e ? $this->Form->input('eppnsuffix', array('size' => 50)) : Sanitize::html($civicrm_sources[0]['CivicrmSource']['eppnsuffix'])); ?>
      </td>
    </tr>
    <tr>
      <td>
        <i><font class="required"><?php print _txt('fd.req'); ?></font></i><br />
      </td>
      <td>
        <?php
          if($e) {
            print $this->Form->submit($submit_label);
            print $this->Form->button(_txt('op.reset'), 
                                      array('type'=>'reset'));
          }
        ?>
      </td>
    </tr>
  </tbody>
</table>
