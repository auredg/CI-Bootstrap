<?php

class Acl{
    
    private
            $default_acl = 'default',
            $class = '',
            $method = '',
            $user_acl = '',
            $CI;
    
    
    public function __construct(){
        
        $this->CI = & get_instance();
        
        // Load the ACL config permissions
        $this->CI->load->config('acl');
        
        // Get redirect URI if unauthorized
        $this->redirect_uri = $this->CI->config->item('redirect_uri');
        
        // Get route params
        $this->class = $this->CI->router->class;
        $this->method = $this->CI->router->method;
        
        // Get user permission in session
        $this->user_acl = $this->CI->session->userdata($this->CI->config->item('session_acl_var'));
        
        // Set default permission
        if(empty($this->user_acl)) {
            $this->user_acl = $this->default_acl;
        }
    }
    
    
    /**
     * Verifie pour une URI que l'utilisateur a bien les droits necessaires
     * Les droits sont specifies dans config/acl.php
     * Cette methode est prevu pour etre appellee sur le hook "post_controller_constructor"
     * @return boolean 
     */
    public function auth(){
        // On teste dabord le couple controller/action
        if (($route_acl = $this->CI->config->item($this->class . '/' . $this->method)) != '');
        
        // On teste ensuite pour tout le controller
        else if(($route_acl = $this->CI->config->item($this->class)) != '');
        
        // Sinon, pas de permissions pour ce controller
        else {
            return TRUE;
        }
        
        if (!is_array($route_acl)) {
            $route_acl = explode('|', $route_acl);
        }
        
        if (!in_array($this->user_acl, $route_acl)) {
            $this->_authentification_failed();
            return FALSE;
        }
        
        return TRUE;
    }
    
    
    /**
     * En cas de refus de permission, cette methode sera executee
     * Redirige sur une page du site , 'redirect_uri' dans config/acl.php
     * Ou affiche une page d'erreur avec un message de type 403 
     */
    private function _authentification_failed(){
        if ($this->redirect_uri != '') {
            redirect($this->redirect_uri);
        } else {
            $err_heading = 'Error 403';
            $err_message = 'Restricted area';
            
            if(isset($this->CI->lang)){
                $err_heading = $this->CI->lang->line('error_403');
                $err_message = $this->CI->lang->line('restricted_area');
            }
            
            show_error($err_message, 403, $err_heading);
        }
    }
}
