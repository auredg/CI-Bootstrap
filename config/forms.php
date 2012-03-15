<?php

$config['form_upload_path'] = APPPATH . '../upload/';

/*
 * Describe your forms here
 * Don't forget to prefix form name by "form_" to avoid any collision
 * 
 * HOW TO USE
 * 
 * 'field_name'=> array(
 *   'item' => '', // may be fieldset, text, password, radio, checkbox, select, file, textarea, submit, date
 *   'size' => '',
 *   'maxlength' => '',
 *   'required' => '', // true or false
 *   'rules' => '', // check the doc of the CI form validation class
 *   'placeholder' => '', // true or false or label for language key
 *   'label' => '', // label for lang translation ( if not set, it will contain : form_{formname}_label_{fieldname} )
 * FOR FIELDSET
 *   'type' => '', // on off for the fieldset, 
 * FOR SELECT
 *   'options' => '', // array of key => value
 *   'multiple' => '', // true or false
 * FOR RADIO/CHECKBOX
 *   'list' => '', // list of value (label for lang)
 * FOR FILE INPUT
 *   'ext' => array(), // list of extensions
 * FOR TEXTAREA
 *   'cols' => '',
 *   'rows' => '',
 * FOR SUBMIT
 *   'reset' => '' // true or false
 * FOR DATE
 *   'datepicker' => '' // js serialized data (format: 'd-m-Y')
 * )
 */