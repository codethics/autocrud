<?
class Crud_Model extends CI_Model{
	var $table;
	function construct($table){
		$this->table=$table;
	}
	function get_data($fields,$relations,$options){
		$this->db->select($this->table.'.'.$this->getPrimaryKey().' as \''.$this->getPrimaryKey().'\'');
		
		foreach($fields as $key=>$value){
			$this->db->select($this->table.'.'.$key.' as \''.$value.'\'');
		}
		if(is_array($relations)){
			$i=0;
			foreach($relations as $relation){
				$table='table'.$i;
				$this->db->select($table.'.'.$relation['field_name'].' as \''.$relation['alias'].'\'');
				$this->db->join($relation['table'].' as '.$table,$table.'.'.$relation['primary_key'].'='.$this->table.'.'.$relation['foreign_key'],'left');
				$i++;
			}
		}
		
		if(is_array($options)){
			foreach($options as $option){
				//generates a CASE sql statement
				$statement='(CASE '.$this->db->dbprefix($this->table).'.'.$option['field'];
				foreach($option['options'] as $key=>$value)
					$statement.=' WHEN \''.$key.'\' THEN \''.$value.'\'';
				$statement.=' END) as \''.$option['alias'].'\'';
				$this->db->select($statement);
			}
		}
		
		return $this->db->get($this->table);
	}
	function get_options($relation){
		$rows=$this->db->get($relation['table'])->result_array();
		$options[0]='Select '.$relation['alias'];
		foreach($rows as $row){
			$options[$row[$relation['primary_key']]]=$row[$relation['field_name']];
		}
		return $options;
	}
	function getDataById($id){
		$this->db->where($this->getPrimaryKey(),$id);
		return $this->db->get($this->table)->row_array();
	}
	function get_field_data(){
		return $this->db->field_data($this->table);
	}
	function saveData($data){
		if($this->db->insert($this->table,$data)) return true;
	}
	function updateData($id,$data){
		$this->db->where($this->getPrimaryKey(),$id);
		if($this->db->update($this->table,$data)) return true;
	}
	function getPrimaryKey(){
		$fields = $this->db->field_data($this->table);
		foreach($fields as $field){
			if($field->primary_key==1)
				return $field->name;
		}
	}
	function deleteData($id){
		$this->db->where($this->getPrimaryKey(),$id);
		if($this->db->delete($this->table)) return true;
	}
}
