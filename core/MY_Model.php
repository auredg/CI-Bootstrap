<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class MY_Model extends CI_Model{

    
    /**
     * Read a table and returns a collections of rows
     * @param string $select Field to select (default *)
     * @param array $where Conditions to select fields
     * @param int $nb The number of row returned
     * @param type $start Start at the row $start
     * @return array(array) A collections of rows
     */
    public function read($select = '*', $where = array(), $nb = NULL, $start = NULL){
        return $this->_read($select, $where, $nb, $start)->result_array();
    }
    
    
    /**
     * Read a table and return only a single row (even in there is many results)
     * @param string $select Field to select (default *)
     * @param array $where Conditions to select fields
     * @param int $nb The number of row returned
     * @param type $start Start at the row $start
     * @return array A single row
     */
    public function readone($select = '*', $where = array(), $nb = NULL, $start = NULL){
        return $this->_read($select, $where, $nb, $start)->row_array();
    }
    
    
    /**
     * Return a single row with id
     * @param int $id The id of the row
     * @param string $select If you want to select few fields
     * @return array A single row
     */
    public function get($id, $select = '*'){
        return $this->readone($select, array($this->table .'_id' => $id));
    }
    
    
    /**
     * Private method to read , just returns the DB resource, no data
     */
    private function _read($select, $where, $nb, $start){
        $this->db->select($select)
                ->from($this->table)
                ->where($where);
        
        if($nb){
            $this->db->limit($nb, $start);
        }
        
        return $this->db->get();
    }
    
    
    /**
     * Save the data
     * @param array $data Donnees
     * @param int $id Identifiant du contact ou NULL (auquel cas on enregistre un nouveau contact)
     */
    public function save($data, $id = NULL){
        if($id){
            $this->db->where(array($this->table . '_id' => $id))
                    ->update($this->table, $data);
            return $id;
        } else {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id($this->table, $this->table . '_id');
        }
    }

    
    /**
     * Delete record(s) from the table
     * @param array $where Conditions to delete rows
     * @return type 
     */
    public function delete($id){
	
	return (bool) $this->db->where(array($this->table . '_id' => $id))
                ->delete($this->table);
    }
    
    
    /**
     * Active un enregistrement
     * @param int $id
     * @return bool 
     */
    public function active($id){
        return (bool) $this->db->where(array($this->table . '_id' => $id))
                ->update($this->table, array('actif' => 1));
    }
    
    
    /**
     * Desctive un enregistrement
     * @param int $id
     * @return bool 
     */
    public function desactive($id){
        return (bool) $this->db->where(array($this->table . '_id' => $id))
                ->update($this->table, array('actif' => 0));
    }
    
    
    /**
     * Renvoie si l'enregistrement est actif ou non
     * @param int $id
     * @return bool 
     */
    public function isactive($id){
        $row = $this->db->select('actif')
                ->from($this->table)
                ->where(array($this->table . '_id' => $id))
                ->get()->row();
        
        return (bool) ($row->actif === '1');
    }
    
    
    /**
     * Count the number of rows
     * If specified, you can add a WHERE condition
     * @param string|array $field The field name or array of conditions
     * @param mixed|array $value NULL or the value to search
     * @return int 
     */
    public function count($field = array(), $value = NULL){
        return (int) $this->db->where($field, $value)
                ->from($this->table)
                ->count_all_results();
    }
    
    
    /**
     * Fill table with fake data
     * @param int $nb Num rows to insert
     */
    public function filltest($nb = 50){
        
        $fieldlist = $this->db->field_data($this->table);
        
        for($i = 0; $i < $nb; $i++){
            
            foreach($fieldlist as $field){

                if($field->primary_key == 0){
                    
                    $field->value = '';
                    
                    if(strpos($field->type, 'int') !== FALSE){
                        $field->value = rand(0, $field->max_length);
                    }
                    
                    if(strpos($field->type, 'char') !== FALSE){
                        $value = '';
                        
                        while(strlen($value) < $field->max_length && strlen($value) < 8){
                            $value.= base64_encode(rand());
                        }
                        
                        $field->value = substr($value, 0, $field->max_length);
                    }
                    
                    if(strpos($field->type, 'text') !== FALSE){
                        $value = '';
                        $max = 64 * 1024; // 64 ko
                        
                        while(strlen($value) < $max){ 
                            $value.= base64_encode(rand());
                        }
                        
                        $field->value = substr($value, 0, $max);
                    }
                }
                
                if(!empty($field->value)){
                    $data[$field->name] = $field->value;
                }
            }
            
            $this->db->insert($this->table, $data);
        }
    }
}
