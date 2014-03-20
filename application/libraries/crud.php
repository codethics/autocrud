<?
/*
┌────────────────────────────────────────────────────────────────────┐
│ CRUD Automation Library based on Codeigniter                       │
├────────────────────────────────────────────────────────────────────┤
│ Copyright © 2014 Muhitur Rahman                                    │
├────────────────────────────────────────────────────────────────────┤
│ Email: muhitbd@gmail.com                                           │
└────────────────────────────────────────────────────────────────────┘
*/
class Crud{
	var $ci;
	var $data=null;
	var $fields;
	var $field_data;
	var $relations;
	var $options;
	var $custom_list=null;
	var $custom_form=null;
	var $display_fields=null;
	
	var $child=null;
	var $change_value=null;
	var $before_save=null;
	var $after_save=null;
	var $before_update=null;
	var $after_update=null;
	
	var $change_type=null;
	
	private $merge_view=false;
	
	function __construct(){
		$this->ci =& get_instance();
		$this->ci->load->helper('form');
		$this->ci->load->helper('url');
		$this->ci->load->model('crud_model');
		$this->ci->load->helper('crud');
		$this->ci->load->library('form_validation');
		$this->ci->load->library('session');
	}
	function run($force_action=null){
		$action=$this->ci->uri->segment(3);
		
		if($action==null){
			return $this->list_data();
		}elseif($action=='insert'){
			return $this->form();
		}elseif($action=='edit'){
			return $this->form($this->ci->uri->segment(4));
		}elseif($action=='save'){
			return $this->save();
		}elseif($action=='update'){
			return $this->save($this->ci->uri->segment(4));
		}elseif($action=='delete'){
			return $this->delete($this->ci->uri->segment(4));
		}
	}
	function init($table,$fields){
		$this->ci->crud_model->construct($table);
		$this->field_data=$this->ci->crud_model->get_field_data();
		foreach($fields as $key => $value){
			$this->fields[$key] = $value;
		}
		$this->data['primary_key']=$this->ci->crud_model->getPrimaryKey();
	}
	function list_data(){
		$query = $this->ci->crud_model->get_data($this->fields,$this->relations,$this->options);
		$this->data['rows'] = $query->result_array();
		
		if($this->display_fields!=null){
			$this->data['fields'] = $this->display_fields;
		}else{
			$this->data['fields'] = $query->list_fields();
		}
		
		if($this->custom_list==null)
			return $this->ci->load->view('crud/list',$this->data,true);
		else
			return $this->ci->load->view($this->custom_list,$this->data,true);
	}
	function join($table,$foreign_key,$primary_key,$field_name,$alias){
		$this->relations[]=array(
							'table'=>$table,
							'foreign_key'=>$foreign_key,
							'primary_key'=>$primary_key,
							'field_name'=>$field_name,
							'alias'=>$alias);
	}
	function set_option($field,$alias,$options){
		$this->options[]=array('field'=>$field,'alias'=>$alias,'options'=>$options);
	}
	function set_rules($field_name,$alias,$rule){
		$this->ci->form_validation->set_rules($field_name,$alias,$rule);
	}
	function form($id=null){
		if($id!=null){
			$form_data=$this->ci->crud_model->getDataById($id);
		}else{
			foreach($this->field_data as $field_data){
				$form_data[$field_data->name]=$this->ci->input->post($field_data->name);/* loads the previous input */
			}
		}
		$i=0;
		/* generate text/textarea fields for each field data */
		foreach($this->field_data as $field_data){
			if(array_key_exists($field_data->name,$this->fields)){
				
				if($this->change_type!=null){
					foreach($this->change_type as $type)
						if($type['field']==$field_data->name){
							$field_data->type=$type['type'];
						}
				}
				
				$inputs[$i]['alias']=$this->fields[$field_data->name];
				
				if($field_data->type=='varchar'){
					$inputs[$i]['html']=get_text_field($field_data->name,$form_data[$field_data->name]);
				}elseif($field_data->type=='datetime'){
					$inputs[$i]['html']=get_date_field($field_data->name,$form_data[$field_data->name]);
				}elseif($field_data->type=='password'){
					$inputs[$i]['html']=get_password_field($field_data->name,$form_data[$field_data->name]);
				}
				$i++;
			}
		}
		/* generate dropdown fields for each relation */
		if(is_array($this->relations)){
			foreach($this->relations as $relation){
				$options=$this->ci->crud_model->get_options($relation);
				$inputs[$i]['alias']=$relation['alias'];
				$inputs[$i]['html']=get_dropdown_field($relation['foreign_key'],$options,$form_data[$field_data->name]);
				$i++;
			}
		}
		/* generate dropdown fields for each given options */
		if(is_array($this->options)){
			foreach($this->options as $option){
				$options=$option['options'];
				$inputs[$i]['alias']=$option['alias'];
				$inputs[$i]['html']=get_dropdown_field($option['field'],$options,$form_data[$field_data->name]);
				$i++;
			}
		}

		if($id==null){
			$this->data['form_open']=form_open($this->ci->uri->segment(1).'/'.$this->ci->uri->segment(2).'/save/');
		}else{
			$this->data['form_open']=form_open($this->ci->uri->segment(1).'/'.$this->ci->uri->segment(2).'/update/'.$id);
		}
		$this->data['form_close']=form_close();
		if($id==null){
			$this->data['submit']=get_submit_button('Save');
		}else{
			$this->data['submit']=get_submit_button('Update');
		}
		$this->data['inputs']=$inputs;
		
		if(!isset($this->data['error'])) $this->data['error']='';
		
		if($this->custom_form==null){
			return $this->ci->load->view('crud/form',$this->data,true);
		}else{
			return $this->ci->load->view($this->custom_form,$this->data);
		}
	}
	function save($id=null){
		if($this->ci->input->post()){
			$this->data=$this->ci->input->post();
			if($this->ci->form_validation->run()==true){
				if($id==null){//new entry
					
					if($this->before_save!=null){
						$func=$this->before_save;
						if(method_exists($this->child,$func)){
							$this->data=$this->child->$func($this->data);
						}
					}
					
					if($this->ci->crud_model->saveData($this->data)){
						if($this->after_save!=null){
							$func=$this->after_save;
							if(method_exists($this->child,$func)){
								$this->data=$this->child->$func($this->data);
							}
						}
						$this->ci->session->set_flashdata('success','Saved!');
						redirect($this->ci->uri->segment(1).'/'.$this->ci->uri->segment(2));
					}else{
						$this->data['error']=$this->ci->db->_error_message();
						return $this->form();
					}
				}else{//update a data
					if($this->before_update!=null){
						$func=$this->before_update;
						if(method_exists($this->child,$func)){
							$this->data=$this->child->$func($this->data);
						}
					}
				
					if($this->ci->crud_model->updateData($id,$this->data)){
						if($this->after_update!=null){
							$func=$this->after_update;
							if(method_exists($this->child,$func)){
								$this->data=$this->child->$func($this->data);
							}
						}
						$this->ci->session->set_flashdata('success','Updated!');
						redirect($this->ci->uri->segment(1).'/'.$this->ci->uri->segment(2));
					}else{
						$this->data['error']=$this->ci->db->_error_message();
						return $this->form($id);
					}
				}
			}else{
				$this->data['error']=validation_errors();
				return $this->form();
			}
		}
	}
	function delete($id){
		if($this->ci->crud_model->deleteData($id)){
				redirect($this->ci->uri->segment(1).'/'.$this->ci->uri->segment(2));
		}
	}
	function display_fields($fields){
		$this->display_fields=$fields;
	}
	function push_data($array){
		foreach($array as $key=>$value)
			$this->data[$key]=$value;
	}
	function before_save($object,$function){
		$this->child=$object;
		$this->before_save=$function;
	}
	function after_save($object,$function){
		$this->child=$object;
		$this->after_save=$function;
	}
	function before_update($object,$function){
		$this->child=$object;
		$this->before_update=$function;
	}
	function after_update($object,$function){
		$this->child=$object;
		$this->after_update=$function;
	}
	function change_type($field,$type){
		$this->change_type[]=array('field'=>$field,'type'=>$type);
	}
}
