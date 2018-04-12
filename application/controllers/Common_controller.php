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
}
