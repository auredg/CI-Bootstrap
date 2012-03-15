<?php

/**
 * Classe qui reprend le fonctionnement de la lib_form de Carbone
 * 
 * @author Aurelien <aurelien.doignon@globalis-ms.com>
 * @company GLOBALIS media systems
 */
class Carboneform{
    
    protected 
            $formdata = array(),
            $current = '',
            $config = array(),
            $message_array = array(),
            $values = array(),
            $datafield = array('text', 'textarea', 'password', 'checkbox', 'radio', 'file', 'select', 'date');
    
    
    public function __construct(){
        $this->CI = &get_instance();
        
        $this->CI->config->load('forms');

        $this->CI->load->helper('carboneform');
        
        $this->CI->load->library('form_validation');
    }
    
    
    /**
     * Ajoute un formulaire (validation & affichage)
     * @param string $form Nom du formulaire
     * @param type $data Donnees du formulaire
     * @return \Carboneform 
     */
    public function add($form){
        
        $this->current = $form;
        $data = $this->getconfig($form);
        
        if(!is_array($data)){
            return $this;
        }
        
        $postdata = $this->CI->input->post();
        
        // On traite les donnes pour rajouter des informations
        foreach($data as $name => &$item){
            
            // Libelle des champs (traduits)
            if(!empty($item['label'])) {
                $item['label'] = lang($item['label']);
            } else {
                $item['label'] = lang($form .'_label_'. $name);
            }
            
            // Options des champs select
            if($item['item'] === 'select' && !is_array($item['options'])) {

                if(is_string($item['options']) && method_exists($this->CI, $item['options'])){
                    // Si c'est une chaine, c'est probablement un nom de methode (situee dans le controller)
                    $item['options'] = $this->CI->$item['options']();
                }else{
                    // Sinon liste vide (pour eviter les erreurs)
                    $item['options'] = array();
                }
            }
            
            // Utilisation des donnees post pour les value
            if(empty($item['value']) && isset($postdata[$name])){
                $item['value'] = $postdata[$name];
            }else{
                $item['value'] = '';
            }
        }
        
        $this->formdata[$this->current] = $data;
        
        return $this;
    }
    
    
    /**
     * Retourne un formulaire
     * @param string $form
     * @return data 
     */
    public function get($form){
        return (isset($this->formdata[$form])) ? $this->formdata[$form] : NULL;
    }
    
    
    /**
     * Retourne un formulaire
     * @param string $form
     * @return data 
     */
    public function getconfig($form){
        return $this->CI->config->item($form);
    }
    
    
    /**
     * Lancer la validation du dernier formulaire ajoute
     * @return bool|NULL Le resultat peut etre un boolean, ou NULL si la validation du formulaire a echouee 
     */
    public function validate(){
        $validation = TRUE;
        
        if($this->CI->input->post()){
            
            // Pour les fichiers, on effectue la validation  a l'aide de la classe file_upload
            
            foreach($this->getcurrent() as $key => $data){
                if($data['item'] === 'file' && (!empty($data['required']) || !empty($_FILES[$key]['name']))){
                    $validation = $this->_validate_upload($key, $data) && $validation;
                }
            }
            
            // On utilise la classe form_validation de CI pour valider les champs classiques
            
            $this->setconfig();
            
            $this->CI->form_validation->set_rules($this->config);
            
            $validation = $this->CI->form_validation->run() && $validation;
        } else {
            $validation = FALSE;
        }

        return $validation;
    }
    
    
    /**
     * Valide un upload et effectue la copie du fichie vers le dossier upload
     * @param string $name Le nom du champ
     * @param array $data Donnees a tester (ext et max)
     * @return bool|array False ou les donnes du fichier uploade
     */
    private function _validate_upload($name, $data){
        $config['upload_path'] = realpath($this->CI->config->item('form_upload_path')) .'/';
        $config['allowed_types'] = implode('|', $data['ext']);
        $config['max_size'] = (isset($data['max'])) ? $data['max'] : 2 * 1024;
        $config['encrypt_name'] = TRUE;
        
        $this->CI->load->library('upload');
        $this->CI->upload->initialize($config);
        
        if (!$this->CI->upload->do_upload($name)){
            // Erreur lors de l'upload
            $this->setmessage($this->CI->upload->display_errors('', ''));
            return false;
        }else{
            // L'upload s'est bien passe
            $filedata = $this->CI->upload->data();
            
            // On tente d'effacer l'ancien fichier
            $oldfile = $this->CI->input->post($name);
            if($oldfile){
                $oldfile = $config['upload_path'] . $oldfile;
                if(file_exists($oldfile)){
                    unlink($oldfile);
                }
            }
            
            // On indique le nouveau fichier comme si c'etait un champ (traitement des donnees)
            $_POST[$name] = $filedata['file_name'];
            // On met a jour la valeur du formulaire (affichage), sans quoi l'ancienne resterait toujours affichee
            $this->setvalue($name, $filedata['file_name']);
            
            return true;
        }
    }
    
    
    /**
     * Met a jout les valeurs par defaut d'un formulaire ou pre-remplir les champs
     */
    public function setvalues($data = array()){
        
        foreach($data as $key => $value){
            if(isset($this->formdata[$this->current][$key]) && empty($this->formdata[$this->current][$key]['value'])){
                $this->setvalue($key, $value);
            }
        }
    }
    
    
    /**
     * Met a jour la valeur d'un seul champ 
     */
    public function setvalue($field, $value){
        $this->formdata[$this->current][$field]['value'] = $value;
    }
    
    
    /**
     * Retourne les donnees du formulaire
     * @return null 
     */
    public function getcurrent(){
        if(isset($this->formdata[$this->current])){
            return $this->formdata[$this->current];
        } else {
            return NULL;
        }
    }
    
    
    /**
     * Recupere les donnees soumise en post
     * Supprime toute donnees qui ne serait pas presente dans le formulaire pour eviter toute injection via les donnees $_POST
     * Et eviter toute donnees inatendue pour les controllers
     * @return array Donnees post filtrees (pour le dernier formulaire) 
     */
    public function getdata(){
        // On recupere les donnees du formulaire
        $postdata = $this->CI->input->post();
        
        $fieldlist = array();
        
        foreach($this->getcurrent() as $name => $item){
            if(in_array($item['item'], $this->datafield)){
                $fieldlist[$name] = '';
            }
        }
        
        $postdata = array_intersect_key($postdata, $fieldlist);
        
        return $postdata;
    }
    
    
    /**
     * 
     * @return boolean 
     */
    private function setconfig(){
        if($formdata = $this->getcurrent()) {
            
            /*
            * On va adapter le format de regles de carboneform au format 
            * de la classe form_validation de CI
            */
            foreach($formdata as $field => $data){
                if( in_array($data['item'], $this->datafield)){

                    // Ajout des regles par defaut 
                    $rules = array();

                    // Ajout de la regle rapide "champ requis"
                    if(!empty($data['required'])){
                        $rules[] = 'required';
                    }

                    // Ajout de la regle rapide "maxlength"
                    if(!empty($data['maxlength'])){
                        $rules[] = 'max_length['. (int)$data['maxlength'] .']';
                    }

                    // Ajout des autres regles definies
                    if(!empty($data['rules'])){
                        $rules[] = $data['rules'];
                    }

                    if(!empty($rules)){
                        $this->config[] = array(
                            'field' => $field,
                            'label' => $data['label'],
                            'rules' => implode('|', $rules)
                        );
                    }else{
                        $this->config[] = array(
                            'field' => $field,
                            'label' => $data['label'],
                            'rules' => ''
                        );
                    }
                }

            }
            
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     * Definit un message a afficher
     * @param string $msg
     * @param string $type 
     */
    public function setmessage($msg){
        $this->message_array[] = $msg;
    }
    
    
    /**
     * Recupere les message d'erreur
     * @return string 
     */
    public function showmessage($prefix = '', $suffix = ''){
        // Generate the error string
        $str = '';

        foreach ($this->message_array as $val) {
            if ($val != '') {
                $str .= $prefix . $val . $suffix . "\n";
            }
        }

        return $str;
    }
    
    
}