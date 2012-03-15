<?php

    if (!defined('BASEPATH')) exit('No direct script access allowed');
    
    /**
     * Librairie qui permet d'avoir un ou plusieurs layout pour les pages CI<br />
     * Pour charger sa vue : $this->layout->add('VUE')->load();<br />
     * Pour charger plusieurs vues a la suite : $this->layout->add('VUE_1')->add('VUE_2')->add()...<br />
     * L'appel a $this->layout->load() appellera le layout<br />
     * Pour obtenir le contenu d'une vue : $this->layout->add('VUE', array(), TRUE) OU $this->layout->get('VUE')<br />
     */
    class Layout{
        
        private $CI;
	private $var = array();
        private $theme = '';
        
        
        /**
         * @constructor 
         */
        public function __construct(){
            $this->CI = &get_instance();
            
            $this->CI->config->load('layout');
            
            $this->theme = $this->CI->config->item('theme');
            $this->var['output'] = '';
	
            // Meta donnees
            $this->set_titre($this->CI->config->item('title'));
            $this->set_soustitre($this->CI->router->fetch_class());
            $this->set_description($this->CI->config->item('description'));
            $this->var['sitetitle'] = $this->CI->config->item('sitetitle');
            $this->var['keywords'] = $this->CI->config->item('keywords');
            $this->var['charset'] = $this->CI->config->item('charset');
            
            // Scripts
            $this->var['css'] = array();
            $this->var['js'] = array();
            
            foreach($this->CI->config->item('css') as $css){
                $this->add_css($css);
            }
            
            foreach($this->CI->config->item('js') as $js){
                $this->add_js($js);
            }
	}
        
        
        /**
         * Appel au chargement de la page finale avec le layout
         * Derniere fonction appelee dans les controlleurs, le contenu sera envoye au client ensuite 
         */
        public function load(){
            
            // Parser classique
            $this->CI->load->view('../layout/'. $this->theme, $this->var);
            
            $this->CI->output->enable_profiler(true);
	}
        
        
        /**
         * Ajouter une vue au flux de sortie
         * @param string $name Nom de la vue
         * @param array $data Variables pour la vue
         * @param boolean $ob Renvoie les donnees au lieu de les ajouter dans le flux
         * @return \Layout|string
         */
        public function add($name, $data = array(), $ob = false){
            
            $view = $this->CI->load->view($name, $data, true);
            
            if($ob === false){
                $this->var['output'] .= $view;
                return $this;
            }else{
                return $view;
            }
        }
        
        
         /**
         * Recupere le contenu d'une vue dans une variable
         * et l'envoie au layout principal
         * @param string $name Nom de la vue
         * @param array $data Variables pour la vue
         * @return \Layout 
         */
        public function get($name, $data = array()){
            $this->var[$name] = $this->add($name, $data, true);
            return $this;
        }
        
        
        /**
         * Change le theme actif pour la page
         * @param string $theme Nom du theme (et nom de fichier dans application/layout/)
         * @return boolean 
         */
        public function set_theme($theme){
            if(is_string($theme) && !empty($theme) && file_exists('./application/layout/' . $theme . '.php')){
                $this->theme = $theme;
                return true;
            }
            
            return false;
        }
        
        
        /**
         * Definir le titre de la page
         * @param string $titre Titre de la page
         * @return boolean 
         */
        public function set_titre($titre){
            if(is_string($titre) && !empty($titre)){
                $this->var['titre'] = ucfirst($titre);
                return true;
            }
            return false;
        }
        
        
        /**
         * Definir le sous-titre de la page
         * @param string $soustitre Sous-titre de la page
         * @return boolean 
         */
        public function set_soustitre($soustitre){
            if(is_string($soustitre) && !empty($soustitre)){
                $this->var['soustitre'] = ucfirst($soustitre);
                return true;
            }
            return false;
        }
        
        
        /**
         * Definir la description
         * @param string $description Description de la page
         * @return boolean 
         */
        public function set_description($description){
            if(is_string($description) && !empty($description)){
                $this->var['description'] = strip_tags($description);
                return true;
            }
            return false;
        }

        
        /**
         * Definir le charset de la page
         * @param string $charset Charset (utf-8, iso-8859-1, ...)
         * @return boolean 
         */
        public function set_charset($charset){
            if(is_string($charset) && !empty($charset)){
                $this->var['charset'] = $charset;
                return true;
            }
            return false;
        }
        
        
        /**
         * Ajoute un ou plusieurs mots cles pour la page
         * @param string|array $keyword Mot cle ou tableau de liste de mots cles
         * @param boolean $remove Indique s'il faut remplacer les mots cles existant ou juste en ajouter
         * @return boolean
         */
        public function set_keyword($keyword, $remove = false){            
            if($remove === true){
                $this->var['keywords'] = '';
            }

            if(is_string($keyword) && !empty($keyword)){
                $this->var['keywords'].= $keyword;
                return true;
            }
            
            return false;
        }
        
        
        /**
         * Ajoute un fichier css a charger
         * @param string $css Nom de la feuille de style CSS (dans votre dossier css)
         * @return boolean 
         */
        public function add_css($css, $media = 'all')
        {
            if(is_string($css) && !empty($css)){
                $this->var['css'][] = array(
                    'href' => $this->CI->config->item('base_url') .'css/'. $css, 
                    'media' => $media
                );
                return true;
            }
            
            return false;
        }

        
        /**
         * Ajoute un fichier javascript a charger
         * @param string $js Nom du javascript (dans votre dossier js)
         * @return boolean 
         */
        public function add_js($js, $type = 'text/javascript')
        {
            if(is_string($js) && !empty($js)){
                $this->var['js'][] = array(
                    'src' => $this->CI->config->item('base_url') .'js/'. $js,
                    'type' => $type
                );
                return true;
            }
            
            return false;
        }
    }
