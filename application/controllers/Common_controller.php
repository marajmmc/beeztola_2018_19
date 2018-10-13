<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_controller extends Root_Controller
{
    private  $message;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
    }
    public function get_dropdown_farmers_by_outlet_farmer_type_id()
    {
        $html_container_id='#farmer_id';
        if($this->input->post('html_container_id'))
        {
            $html_container_id=$this->input->post('html_container_id');
        }

        $farmer_type_id = $this->input->post('farmer_type_id');
        $outlet_id = $this->input->post('outlet_id');

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);

        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer','farmer.id = farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer.id value,farmer.name text');

        $this->db->where('farmer.farmer_type_id',$farmer_type_id);
        $this->db->where('farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer_outlet.revision',1);
        //$this->db->group_by('farmer.id');
        $this->db->order_by('farmer.ordering DESC');
        $this->db->order_by('farmer.id DESC');
        $data['items']=$this->db->get()->result_array();
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>$html_container_id,"html"=>$this->load->view("dropdown_with_select",$data,true));
        $this->json_return($ajax);
    }
    public function get_dropdown_dealers_by_outlet_id()
    {
        $html_container_id='#dealer_id';
        if($this->input->post('html_container_id'))
        {
            $html_container_id=$this->input->post('html_container_id');
        }
        $outlet_id = $this->input->post('outlet_id');

        $this->db->from($this->config->item('table_pos_setup_farmer_outlet').' farmer_outlet');
        $this->db->select('farmer_outlet.farmer_id value');
        $this->db->join($this->config->item('table_pos_setup_farmer_farmer').' farmer_farmer','farmer_farmer.id=farmer_outlet.farmer_id','INNER');
        $this->db->select('farmer_farmer.name text');

        $this->db->where('farmer_farmer.status',$this->config->item('system_status_active'));
        $this->db->where('farmer_farmer.farmer_type_id > ',1);
        $this->db->where('farmer_outlet.revision',1);
        $this->db->where('farmer_outlet.outlet_id',$outlet_id);
        $data['items']=$this->db->get()->result_array();

        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>$html_container_id,"html"=>$this->load->view("dropdown_with_select",$data,true));
        $this->json_return($ajax);
    }
    public function get_dropdown_upazillas_by_districtid()
    {
        $district_id = $this->input->post('district_id');
        $html_container_id='#upazilla_id';
        if($this->input->post('html_container_id'))
        {
            $html_container_id=$this->input->post('html_container_id');
        }
        if($this->input->post('select_label'))
        {
            $data['select_label']=$this->input->post('select_label');
        }
        $data['items']=Query_helper::get_info($this->config->item('table_login_setup_location_upazillas'),array('id value','name text'),array('district_id ='.$district_id,'status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC','id ASC'));
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>$html_container_id,"html"=>$this->load->view("dropdown_with_select",$data,true));

        $this->json_return($ajax);
    }
    public function get_dropdown_unions_by_upazillaid()
    {
        $upazilla_id = $this->input->post('upazilla_id');
        $html_container_id='#union_id';
        if($this->input->post('html_container_id'))
        {
            $html_container_id=$this->input->post('html_container_id');
        }
        if($this->input->post('select_label'))
        {
            $data['select_label']=$this->input->post('select_label');
        }
        $data['items']=Query_helper::get_info($this->config->item('table_login_setup_location_unions'),array('id value','name text'),array('upazilla_id ='.$upazilla_id,'status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC','id ASC'));
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>$html_container_id,"html"=>$this->load->view("dropdown_with_select",$data,true));

        $this->json_return($ajax);
    }
}
