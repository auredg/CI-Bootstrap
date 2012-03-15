<?php


/**
 * Retourne un formulaire affiche dans le style des formulaires Carbone
 * @global Object $CI
 * @param string $url URL d'action du formulaire (controlleur[/action])
 * @param string $form Le nom du formulaire (defini dans le controller)
 * @param array $option 
 */
function carbone_form($url, $form, $option = array()){
    $CI = &get_instance();
    
    $data = $CI->carboneform->get($form);
    
    if(empty($data)){
        return lang('no_form_added');
    }
    
    $option = $option + array(
        'method' => 'post',
        'class' => 'form'
    );
    
    $form_struct = array();
    $form_fieldset = array();
    $i = 0;
    
    $file_upload = false;
    
    $js = array();
    
    foreach($data as $name => $item){
        
        $attributes = array(
            'name' => $name,
            'id' => $name,
            'value' => $item['value'],
            'rows' => (!empty($item['rows'])) ? $item['rows'] : NULL,
            'cols' => (!empty($item['cols'])) ? $item['cols'] : NULL,
            'size' => (!empty($item['size'])) ? $item['size'] : NULL,
            'maxlength' => (!empty($item['maxlength'])) ? $item['maxlength'] : NULL,
            'placeholder' => (!empty($item['placeholder'])) ? lang($form .'_placeholder_' . ($item['placeholder'] !== TRUE ? $item['placeholder'] : $name)) : NULL,
        );    
        
        if(!empty($item['required'])){
            $item['label'].= ' '. lang('form_label_field_required');
        }
        
        switch($item['item']){

            case 'submit':
                $multiple = (!empty($item['reset'])) ? ' multiple' : '';
                
                $form_struct[$i][$name] = array('', 
                    form_submit($name, $item['label'], 'class="first'. $multiple .'"')
                );
                
                if(!empty($item['reset'])){
                    $form_struct[$i][$name][1].= form_reset('reset', lang('form_label_reset'), 'class="last'. $multiple .'"');
                }
                break;

            case 'text':
                
                $form_struct[$i][$name] = array(
                    form_label($item['label'], $name),
                    form_input($attributes)
                );
                break;
                
            case 'password':
                $form_struct[$i][$name] = array(
                    form_label($item['label'], $name),
                    form_password($attributes)
                );
                break;
            
            case 'textarea':
                
                $form_struct[$i][$name] = array(
                    form_label($item['label'], $name),
                    form_textarea($attributes)
                );
                
                break;
            
            case 'info':
                
                $form_struct[$i][$name] = array(
                    form_label($item['label'], $name),
                    '<span id="'. $name .'">'. $item['value'] .'</span>'
                );
                
            case 'date':
                
                $attributes['type'] = 'date';
                
                $form_struct[$i][$name] = array(
                    form_label($item['label'], $name),
                    form_input($attributes)
                );
                
                if(empty($item['datepicker'])){
                    $item['datepicker'] = array();
                }
                
                $params = $item['datepicker'] + array(
                    'dateFormat' => "'dd/mm/yy'",
                    'monthNames' => lang('month_names'),
                    'monthNamesShort' => lang('month_names_short'),
                    'dayNames' => lang('day_names'),
                    'dayNamesMin' => lang('day_names_min'),
                    'dayNamesShort' => lang('day_names_short'),
                );
                
                $js_params = array();
                
                foreach($params as $key => $value){
                    $js_params[] = "$key: $value";
                }
                
                $js[] = 'window.onload = function(){
                    $(\'#'. $name .'\').datepicker({'. implode(",", $js_params) .'});
                }';
                
                break;
            
            /*
             * Gestion delicate
             * Tout dabort, on definit true pour le enctype en mdoe multipart du formulaire
             * Ensuite on met un champ input type=file en opcity=0 et on met un bouton de remplacement (pour integrer le design)
             * Ce bouton a des contraintes de hauteur/largeur pour simuler le click sur l'input cache
             * Pour finir, avec du JS on peux recuperer les chemin des fichiers ajoutes afin de les afficher
             */
            case 'file':
                
                $file_upload = true;
                
                $attributes['style'] = 'position:absolute;opacity:0;-moz-opacity:0;-khtml-opacity:0;filter:alpha(opacity=0);height:24px;width:230px;';
                $attributes['onchange'] = 'document.getElementById(\'file_upload_handler_'. $name .'\').innerHTML=this.value.split(\'\\\\\').pop()';
                
                $style_btn = 'height:24px;width:230px;line-height:10px;';
                
                $multiple = $item['value'] ? ' multiple' : '';
                $view_btn = $item['value'] ? form_button(array(
                    'name' => $name, 
                    'content' => lang('form_label_view_file'), 
                    'style' => $style_btn,
                    'class' => 'last'. $multiple,
                    'onclick' => !empty($attributes['value']) ? "window.open('". base_url('upload/'. $attributes['value']) ."','file','width=600,height=480,location=no,menubar=no,status=no,titlebar=no,toolbar=no');" : ''
                )) : '';
                
                $form_struct[$i][$name] = array(
                    form_label($item['label'], $name),
                    form_hidden($name, $attributes['value']) . 
                    form_upload($attributes) . 
                    form_button(array(
                        'name' => $name, 
                        'content' => lang('form_label_upload'),
                        'style' => $style_btn,
                        'class' => 'first'. $multiple
                    )) . 
                    $view_btn . 
                    '<span id="file_upload_handler_'. $name .'" style="margin-left:10px;display:inline-block;"></span>'
                );
                break;
            
            case 'radio':
                $form_struct[$i][$name] = array(
                    form_label($item['label'])
                );
                
                $radio_list = '';
                
                foreach($item['list'] as $value){
                    $label = $name .'_'. $value;
                    
                    $checked = ($value == $item['value']);
                   
                    $radio_list.= form_radio($name, $value, $checked, 'id="'. $label .'"');
                    $radio_list.= form_label(lang($form .'_label_'. $label), $label);
                    $radio_list.= nbs(2);
                }
                
                $form_struct[$i][$name][] = $radio_list;
                
                break;
            
            case 'checkbox':
                $form_struct[$i][$name] = array(
                    form_label($item['label'])
                );
                
                $ckeckbox_list = '';
                
                foreach($item['list'] as $value){
                    $label = $name .'_'. $value;
                    
                    $checked = ($value == $item['value']);
                    
                    $ckeckbox_list.= form_checkbox($name, $value, $checked, 'id="'. $label .'"');
                    $ckeckbox_list.= form_label(lang($form .'_label_'. $label), $label);
                    $ckeckbox_list.= nbs(2);
                }
                
                $form_struct[$i][$name][] = $ckeckbox_list;
                
                break;
            
            case 'select':
                
                $id = 'id="'. $name .'"';
                
                if(!empty($attributes['placeholder'])){
                     $id .= ' placeholder="'. $attributes['placeholder'] .'" data-placeholder="'. $attributes['placeholder'] .'"';
                }

                $form_struct[$i][$name] = array(
                    form_label($item['label']),
                    !empty($item['multiple']) ? form_multiselect($name.'[]', $item['options'], $item['value'], $id) : form_dropdown($name, $item['options'], $item['value'], $id)
                );
                
                if(!empty($item['multiple'])) {
                    $js[] = 'window.onload = function(){
                        jQuery(\'select\').chosen();
                    }';
                }
                
                break;
                
           case 'fieldset':

               if($item['type'] == 'on') {
                   $i++;
                   $form_fieldset[$i] = $name;
               }elseif($item['type'] = 'off' && isset($form_fieldset[$i]) && is_array($form_fieldset[$i])) {
                   $i++;
               }
               
               break;
                
        }
        
    }
    
    /*
     * Generation du code de mise en forme du formulaire (element form et le tableau)
     */
    
        
    $form_html = '<div class="'. $form .'">';
    
    $form_attributes['method'] = $option['method'];
    
    if($file_upload) {
        $form_html.= form_open_multipart($url, $form_attributes);
    } else {
        $form_html.= form_open($url, $form_attributes);
    }
    
    foreach($form_struct as $key => $form_bloc){
        if(!empty($form_fieldset[$key])){
            $form_html.= form_fieldset(lang($form .'_fieldset_'. $form_fieldset[$key]));
        }
        
        $form_html.= '<table class="'. $option['class'] .'">';
        
        $row_count = 0;
        
        foreach($form_bloc as $name => $field){
            $row_count++;
            $class = ($row_count % 2) ? 'oddrow' : 'evenrow';
            
            $form_html.= '<tr class="'. $class .'">';
            
            foreach($field as $element){
                $form_html.= '<td>' . $element . '</td>';
            }
            
            $form_html.= '</tr>';
        }
        
        $form_html.= '</table>';
        
        if(!empty($form_fieldset[$key])){
            $form_html.= form_fieldset_close();
        }
    }
    
    $form_html.= form_close();
    
    $form_html.= '</div>';
    
    $form_html.= '<script type="text/javascript">' . implode("\r\n", $js) .'</script>';
    
    return $form_html;
    
}


function validation_errors($prefix = '', $suffix = ''){
    
    $CI = &get_instance();
    
    if (($OBJ =& _get_validation_object()) === FALSE){
        return '';
    }

    return 
        $OBJ->error_string($prefix, $suffix) .
        $CI->carboneform->showmessage($prefix, $suffix);
}


function carbone_form_error(){
    $html = '';
        
    if(validation_errors() !== ''){
        $html.= '<div class="formerror">
            <ul>
                '. validation_errors('<li class="error">', '</li>') .'
            </ul>
        </div>';
    }
    
    return $html;
}
