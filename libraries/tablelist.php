<?php

class Tablelist{
    
    private
            $model = NULL,
            $CI;
    
    protected
            $stored = array(),
            $current = '',
            $data = array();
    
    public
            $limit = 0,
            $total = 0;

    
    public function __construct(){
        $this->CI = & get_instance();
        
        $this->model = new Tablelist_model;
        
        $this->CI->load->library('pagination');
        $this->CI->load->helper('tablelist');
        $this->CI->load->helper('url');
    }
    
    
    public function add($data){
        $this->current = $data['name'];
        
        $data = $data + array(
            'name' => '',
            'field' => array(),
            'action' => array(),
            'actionwidth' => '40%',
            'call_data_func' => NULL,
        );
        
        $this->data[$this->current] = $data;
        
        if($postdata = $this->CI->input->post()){
            $stored = array();
            
            if(!empty($postdata['limit'])){
                $stored['limit'] = $postdata['limit'];
            }
            
            $this->CI->session->set_userdata($this->current, $stored);
        }
        
        $this->stored = $this->CI->session->userdata($this->current);
    }
    
    
    public function getdata($name = ''){
        if($name != '' && isset($this->data[$name])){
            return $this->data[$name];
        } else {
            return $this->data[$this->current];
        }
    }
    
    
    public function getlist($name){
        $data = $this->getdata($name);
        
        $where = array();
        
        $page = (!empty($data['pagination'])) ? $data['pagination']['current'] : 1;
        $this->limit =(!empty($data['pagination'])) ? $data['pagination']['limit'] : NULL;
        
        if(is_array($this->limit)) {
            $this->limit = ($this->getstored('limit')) ? $this->getstored('limit') : $this->limit[0];
        }
        
        $this->total = $this->model->total($where);
        
        $config['base_url'] = site_url($this->CI->router->class .'/'. $this->CI->router->method);
        $config['total_rows'] = $this->total;
        $config['per_page'] = $this->limit;
        $config['num_links'] = 2;
        $config['use_page_numbers'] = TRUE;

        $this->CI->pagination->initialize($config);
        
        return $this->model->select($where, $page, $this->limit);
    }
    
    
    public function getstored($key){
        return (isset($this->stored[$key])) ? $this->stored[$key] : NULL;
    }
}


class Tablelist_model extends CI_Model{
    
    public function select($where = array(), $page = 1, $numrow = 20){
        $this->db->where($where);
        
        if($page > 0 && $numrow){
            $this->db->limit($numrow, ($page - 1) * $numrow);
        }
        
        return $this->db->get()->result_array();
    }
    
    public function total($where = array()){
        $_db = clone($this->db);
        $_db->where($where);
        $nb = $_db->count_all_results();
        
        return $nb;
    }
}
