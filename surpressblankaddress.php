<?php

require_once 'surpressblankaddress.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function surpressblankaddress_civicrm_config(&$config) {
  _surpressblankaddress_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function surpressblankaddress_civicrm_xmlMenu(&$files) {
  _surpressblankaddress_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function surpressblankaddress_civicrm_install() {
  return _surpressblankaddress_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function surpressblankaddress_civicrm_uninstall() {
  return _surpressblankaddress_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function surpressblankaddress_civicrm_enable() {
  return _surpressblankaddress_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function surpressblankaddress_civicrm_disable() {
  return _surpressblankaddress_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function surpressblankaddress_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _surpressblankaddress_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function surpressblankaddress_civicrm_managed(&$entities) {
  return _surpressblankaddress_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function surpressblankaddress_civicrm_caseTypes(&$caseTypes) {
  _surpressblankaddress_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function surpressblankaddress_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _surpressblankaddress_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function surpressblankaddress_civicrm_tokens(&$tokens) {
  $tokens['contact']['contact.address_block'] = 'Address block';
  $tokens['contact']['contact.today_date'] = 'Today Date';
  $tokens['contact']['contact.billing_block'] = 'Billing block';
}

function surpressblankaddress_civicrm_tokenValues( &$values, $cids, $job = null, $tokens = array(), $context = null ) {
  foreach($cids as $id){
    $params   = array('contact_id' => $id, 'version' => 3,);
    $contact  = civicrm_api( 'Contact' , 'get' , $params );
    
    if(!$contact['is_error']) {
      $originalContact      = $contact['values'][$id];
      $billingAddressQuery  = "SELECT * FROM `civicrm_address` WHERE `contact_id` = %1 and `is_billing` = %2";
      $billingParams        = array(1 => array($contact['id'], 'Int'), 2 => array(1, 'Int'));
      $billingDao           = CRM_Core_DAO::executeQuery($billingAddressQuery, $billingParams);
      if($billingDao->fetch()) {
        $billingAddressFields = array(
          'street_address'         => $billingDao->street_address,
          'supplemental_address_1' => $billingDao->supplemental_address_1,
          'supplemental_address_2' => $billingDao->supplemental_address_2,
          'city'                   => $billingDao->city,
          'postal_code'            => $billingDao->postal_code,
          'state_province_id'      => $billingDao->state_province_id,
          
        );
      }
      if(!empty($contact['values'][$id]['state_province_id'])) {
        $contact['values'][$id]['state_province_name'] = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_StateProvince', $contact['values'][$id]['state_province_id'], 'name', 'id');
      }
      $values[$id]['contact.address_block'] = nl2br(CRM_Utils_Address::format($contact['values'][$id]), NULL, FALSE, TRUE);
      $values[$id]['contact.today_date'] = CRM_Utils_Date::customFormat(date('Ymd'));
      
      if($billingAddressFields) {
        $billingContact = array_merge($originalContact, $billingAddressFields);
        if(!empty($billingContact['state_province_id'])) {
          $billingContact['state_province_name']  = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_StateProvince', $billingContact['state_province_id'], 'name', 'id');
        }
        $values[$id]['contact.billing_block']     = nl2br(CRM_Utils_Address::format($billingContact, NULL, FALSE, TRUE));
      }
      
    }
  }
}