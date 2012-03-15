<?php

function table_list($name = ''){
    $CI = & get_instance();
    
    $data = $CI->tablelist->getdata($name);
    
    $list = $CI->tablelist->getlist($name);
    
    // Premier callback (hook)
    if(!empty($data['call_predata_func']) && method_exists($CI, $data['call_predata_func'])){
        $CI->$data['call_predata_func']($list);
    }
    
    if(empty($list)){
        return lang('no_data');
    }
    
    $field_name = array_keys($list[0]);
    
    // On retire le premier element qui doit etre l'id
    $field_id = array_shift($field_name);
    
    // On recupere les champs
    $fields = $list[0];
    
    // On retire tous les champs qui n'ont pas leur place (definis dans $data['field'])
    $fields = array_intersect_key($data['field'], $fields);
    
    // Les labels seront utilise dans le table head
    foreach($fields as $name => &$field){
        $field['label'] = lang('field_'. $data['name'] .'_label_'. $name);
    }
    
    $action_list = array();
    $inside = '';
    $outside = '';    
    
    if(!empty($data['action'])){
        foreach($data['action'] as $action){
            
            if(empty($action['position'])){
                $action['position'] = 'row';
            }
            
            switch($action['position']){
                
                case 'row':
                    $action_list[$action['label']] = '';
                    break;
                
                case 'inside':
                    $inside.= format_action_html($action) . '&nbsp;';
                    break;
                
                case 'outside':
                    $outside.= format_action_html($action). '&nbsp;';
                    break;
            }
        }
    }
    
    $rows = array();
    
    foreach($list as $numrow => $row){
        
        $cells = array();
        $row_id = 0;
        
        $available_action = $action_list;
        
        // Second callback (hook)
        if(!empty($data['call_data_func']) && method_exists($CI, $data['call_data_func'])){
            $CI->$data['call_data_func']($row, $available_action);
        }
        
        foreach($row as $key => &$value){
            if(in_array($key, array_keys($data['field']))){
                $cells[] = $value;
            }else if($key === $field_id){
                $row_id = $value;
            }
        }
        
        
        $actions = array();
        
        foreach($data['action'] as $action){
            if(in_array($action['label'], array_keys($available_action))) {
                $actions[] = $action;
            }
        }
        
        // On definit les actions possibles
        if(!empty($actions)){
            $body_action = '<td class="tablelist_rowactions" align="center">' . "\r\n";
            
            foreach($actions as $key => $action){
                
                $class = NULL;

                if ($key == 0) {
                    $class = 'multiple first';
                } elseif ($key == sizeof($actions) - 1) {
                    $class = 'multiple last';
                } else {
                    $class = 'multiple';
                }

                $body_action.= format_action_html($action, $row_id, $class);
            }
            
            $body_action.= '</td>';
        }
        
        $rows[] = '<td>'. implode("</td>\r\n<td>", $cells) .'</td>'. $body_action;
    }
    
    // Construciton du tabelau html
    $head_fields = '';
    foreach($fields as &$field){
        $attributes = '';
        
        if(!empty($field['width'])){
            $attributes.= ' width="'. $field['width'] .'"';
        }
        
        $head_fields.= '<th'.$attributes.'>'. $field['label'] .'</th>' . "\r\n";
    }
    
    $head_action = '';
    if(!empty($data['action'])){
        $head_action = '<th class="tablelist_headactions" width='. $data['actionwidth'] .'>'. $inside .'</th>';
    }
    
    $html = '';
    
    if(!empty($data['total']) && $data['total'] == 'top'){
        $label = !empty($data['total_label']) ? $data['total_label'] : 'tablelist_total_result';
        $html.= '<div class="tablelist_total">'. sprintf(lang($label), $CI->tablelist->total) .'</div>';
    }
    
    if(!empty($outside)){
        $html.= '<div class="tablelist_outside">'. $outside .'</div>';
    }
    
    $html.= '
        <table class="tablelist">

        <tr>
            '. $head_fields .'
            '. $head_action .'    
        </tr>
        
        <tr>
            '. implode("</tr>\r\n<tr>", $rows) .'
        </tr>
        
    </table>';
    
    if(!empty($data['pagination']) && is_array($data['pagination']['limit'])){
        $CI->load->helper('form');
        $html.= '<div class="tablelist_pagination_limit">';
        $html.= form_open('', array('id' => 'pagination_limit'));
        $options = array_combine($data['pagination']['limit'], $data['pagination']['limit']);
        $html.= form_label(lang('tablelist_pagination_select_limit') . ' ', 'limit_select');
        $html.= form_dropdown('limit', $options, $CI->tablelist->limit, 'id="limit_select" onchange="document.getElementById(\'pagination_limit\').submit()"');
        $html.= form_close();
        $html.= '</div>';
    }else if(!empty($data['pagination'])){
        $html.= '<div class="tablelist_pagination_limit">'. sprintf(lang('tablelist_pagination_limit'), $data['pagination']['limit']) .'</div>';
    }
    
    $html.= '<div class="tablelist_pagination">'. $CI->pagination->create_links() .'</div>';
    
    return $html;
}


function format_action_html($action, $row_id = NULL, $pos = ''){
    $html = '';
    
    if(empty($action['type'])){
        $action['type'] = 'text';
    }
    
    $url_action = site_url(sprintf($action['href'], $row_id));
    $label_action = lang('form_action_'. $action['label']);
    
    switch($action['type']){
        
        case 'text':
            $html = '<a href="'. $url_action .'">'. $label_action .'</a>';
            break;
        
        case 'button':
            $html = '<button class="form_action '. $pos .'" onclick="location.href=\''. $url_action .'\';">'. $label_action .'</button>';
            break;
            
    }
    
    return $html;
}
