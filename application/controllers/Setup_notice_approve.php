<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_notice_approve extends Root_Controller
{
    public $message;
    public $permissions;
    public $controller_url;
    public $common_view_location;
    public $file_type;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission(get_class());
        $this->controller_url=strtolower(get_class());
        $this->common_view_location='setup_notice_request';
        $this->file_type='';
        $this->language_labels();
        $this->load->helper('notice');
    }
    private function language_labels()
    {
        $this->lang->language['LABEL_REVISION_COUNT']='Number of edit';
        $this->lang->language['LABEL_NOTICE_ID']='Notice ID';
        $this->lang->language['LABEL_TITLE']='Notice Title';
        $this->lang->language['LABEL_NOTICE_TYPE']='Notice Type';
        $this->lang->language['LABEL_DATE_PUBLISH']='Notice Publish Date';
        $this->lang->language['LABEL_EXPIRE_DAY']='Number Of Day As New';
        $this->lang->language['LABEL_REMAINING_DAY']='Number Of Remaining Day';
        $this->lang->language['LABEL_FILE_IMAGE']='File';
        $this->lang->language['LABEL_FILE_VIDEO']='Video';
        $this->lang->language['LABEL_LINK_URL']='External Link (Url)';
    }
    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
        }
        elseif($action=="list_all")
        {
            $this->system_list_all();
        }
        elseif($action=="get_items_all")
        {
            $this->system_get_items_all();
        }
        elseif($action=="approve")
        {
            $this->system_approve($id);
        }
        elseif ($action == "save_approve")
        {
            $this->system_save_approve();
        }
        elseif($action=="delete")
        {
            $this->system_delete($id);
        }
        elseif ($action == "save_delete")
        {
            $this->system_save_delete();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="set_preference")
        {
            $this->system_set_preference('list');
        }
        elseif($action=="set_preference_all")
        {
            $this->system_set_preference('list_all');
        }
        elseif($action=="save_preference")
        {
            System_helper::save_preference();
        }
        else
        {
            $this->system_list();
        }
    }
    private function get_preference_headers($method)
    {
        $data=array();
        if($method=='list')
        {
            $data['id']= 1;
            $data['notice_type']= 1;
            $data['date_publish']= 1;
            $data['expire_day']= 1;
            $data['remaining_day']= 1;
            $data['title']= 1;
            $data['description']= 1;
            $data['revision_count']= 1;
            $data['ordering']= 1;
        }
        elseif($method=='list_all')
        {
            $data['id']= 1;
            $data['notice_type']= 1;
            $data['date_publish']= 1;
            $data['expire_day']= 1;
            $data['remaining_day']= 1;
            $data['title']= 1;
            $data['description']= 1;
            $data['revision_count']= 1;
            $data['ordering']= 1;
            $data['status']= 1;
            $data['status_forward']= 1;
            $data['status_approve']= 1;
        }
        else
        {

        }

        return $data;
    }
    private function system_set_preference($method)
    {
        $user = User_helper::get_user();
        if(isset($this->permissions['action6']) && ($this->permissions['action6']==1))
        {
            $data['system_preference_items']=System_helper::get_preference($user->user_id,$this->controller_url,$method,$this->get_preference_headers($method));
            $data['preference_method_name']=$method;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("preference_add_edit",$data,true));
            $ajax['system_page_url']=site_url($this->controller_url.'/index/set_preference_'.$method);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_list()
    {
        $user = User_helper::get_user();
        $method='list';
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['title']="Notice Publish Approve Pending List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items()
    {
        $this->db->from($this->config->item('table_pos_setup_notice_request').' item');
        $this->db->select('item.*');
        $this->db->join($this->config->item('table_pos_setup_notice_types').' type','type.id=item.type_id','INNER');
        $this->db->select('type.name notice_type');
        $this->db->where('item.status !=',$this->config->item('system_status_delete'));
        $this->db->where('item.status_forward',$this->config->item('system_status_forwarded'));
        $this->db->where('item.status_approve',$this->config->item('system_status_pending'));
        $this->db->order_by('item.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['expire_day']=Notice_helper::get_expire_day($item['date_publish'],$item['expire_time']);
            $item['remaining_day']=Notice_helper::get_expire_day_by_current_time($item['expire_time']);
            $item['date_publish']=$item['date_publish']?System_helper::display_date($item['date_publish']):'';
        }
        $this->json_return($items);
    }
    private function system_list_all()
    {
        $user = User_helper::get_user();
        $method='list_all';
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['system_preference_items']= System_helper::get_preference($user->user_id, $this->controller_url, $method, $this->get_preference_headers($method));
            $data['title']="Notice Publish Approve All List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list_all",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/list_all/');
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items_all()
    {
        $current_records = $this->input->post('total_records');
        if(!$current_records)
        {
            $current_records=0;
        }
        $pagesize = $this->input->post('pagesize');
        if(!$pagesize)
        {
            $pagesize=100;
        }
        else
        {
            $pagesize=$pagesize*2;
        }
        $time=time();
        $this->db->from($this->config->item('table_pos_setup_notice_request').' item');
        $this->db->select('item.*');
        $this->db->join($this->config->item('table_pos_setup_notice_types').' type','type.id=item.type_id','INNER');
        $this->db->select('type.name notice_type');
        $this->db->where('item.status !=',$this->config->item('system_status_delete'));
        $this->db->where('item.status_forward',$this->config->item('system_status_forwarded'));
        //$this->db->order_by('item.ordering','ASC');
        $this->db->order_by('item.id','DESC');
        $this->db->limit($pagesize,$current_records);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['expire_day']=Notice_helper::get_expire_day($item['date_publish'],$item['expire_time']);
            $item['remaining_day']=Notice_helper::get_expire_day_by_current_time($item['expire_time']);
            $item['date_publish']=$item['date_publish']?System_helper::display_date($item['date_publish']):'';
        }
        $this->json_return($items);
    }
    private function system_approve($id)
    {
        if(isset($this->permissions['action7'])&&($this->permissions['action7']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            $this->db->from($this->config->item('table_pos_setup_notice_request').' item');
            $this->db->select('item.*');
            $this->db->join($this->config->item('table_pos_setup_notice_types').' type','type.id=item.type_id','INNER');
            $this->db->select('type.name notice_type');
            $this->db->where('item.id',$item_id);
            $this->db->where('item.status !=',$this->config->item('system_status_delete'));
            $this->db->order_by('item.id','DESC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Approve Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Notice.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_inactive'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Notice In-Active.';
                $this->json_return($ajax);
            }
            if($data['item']['status_forward']!=$this->config->item('system_status_forwarded'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Notice Not Forwarded.';
                $this->json_return($ajax);
            }
            if($data['item']['status_approve']==$this->config->item('system_status_approved'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Notice Already Approved.';
                $this->json_return($ajax);
            }
            $data['info_basic']=Notice_helper::get_basic_info($data['item']);
            $data['files']=Query_helper::get_info($this->config->item('table_pos_setup_notice_file_videos'),'*',array('notice_id='.$item_id,'status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));

            $data['user_group_ids']=explode(',',trim($data['item']['user_group_ids'],','));
            $data['urls']=json_decode($data['item']['url_links'],true);

            $data['notice_types']=Query_helper::get_info($this->config->item('table_pos_setup_notice_types'),'*',array('status ="'.$this->config->item('system_status_active').'"'));
            $user=User_helper::get_user();
            if($user->user_group==1)
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),'*',array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            }
            else
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),'*',array('id !=1','status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            }

            $data['title']="Notice Approve :: ". $data['item']['id'];
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/approve",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/approve/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_approve()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        if($id>0)
        {
            if(!((isset($this->permissions['action7']) && ($this->permissions['action7']==1))))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            if(!$item_head['status_approve'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Approved/Rollback/Reject field is required.';
                $this->json_return($ajax);
            }
            if($item_head['status_approve']==$this->config->item('system_status_rollback'))
            {
                if(!($item_head['remarks_approve']))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Rollback remarks field is required.';
                    $this->json_return($ajax);
                }
            }
            else if($item_head['status_approve']==$this->config->item('system_status_rejected'))
            {
                if(!($item_head['remarks_approve']))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Reject remarks field is required.';
                    $this->json_return($ajax);
                }
            }
            else
            {
                if($item_head['status_approve']!=$this->config->item('system_status_approved'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Approved/Rollback/Reject field is required.';
                    $this->json_return($ajax);
                }
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $this->db->trans_start();  //DB Transaction Handle START

        $data=array();
        if($item_head['status_approve']==$this->config->item('system_status_rollback'))
        {
            $data['remarks_approve']=$item_head['remarks_approve'];
            $data['status_forward']=$this->config->item('system_status_pending');
            $this->db->set('revision_count_rollback', 'revision_count_rollback+1', FALSE);
        }
        else
        {
            $data['date_approved']=$time;
            $data['user_approved']=$user->user_id;
            $data['remarks_approve']=$item_head['remarks_approve'];
            $data['status_approve']=$item_head['status_approve'];
            $this->db->set('revision_count_approved', 'revision_count_approved+1', FALSE);
        }
        Query_helper::update($this->config->item('table_pos_setup_notice_request'),$data,array('id='.$id));

        $this->db->trans_complete();   //DB Transaction Handle END

        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function system_delete($id)
    {
        if(isset($this->permissions['action3'])&&($this->permissions['action3']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            $this->db->from($this->config->item('table_pos_setup_notice_request').' item');
            $this->db->select('item.*');
            $this->db->join($this->config->item('table_pos_setup_notice_types').' type','type.id=item.type_id','INNER');
            $this->db->select('type.name notice_type');
            $this->db->where('item.id',$item_id);
            $this->db->where('item.status !=',$this->config->item('system_status_delete'));
            $this->db->order_by('item.id','DESC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Forward Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Notice.';
                $this->json_return($ajax);
            }
            if($data['item']['status']==$this->config->item('system_status_inactive'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Notice In-Active.';
                $this->json_return($ajax);
            }

            $data['info_basic']=Notice_helper::get_basic_info($data['item']);
            $data['files']=Query_helper::get_info($this->config->item('table_pos_setup_notice_file_videos'),'*',array('notice_id='.$item_id,'status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));

            $data['user_group_ids']=explode(',',trim($data['item']['user_group_ids'],','));
            $data['urls']=json_decode($data['item']['url_links'],true);

            $data['notice_types']=Query_helper::get_info($this->config->item('table_pos_setup_notice_types'),'*',array('status ="'.$this->config->item('system_status_active').'"'));
            $user=User_helper::get_user();
            if($user->user_group==1)
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),'*',array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            }
            else
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),'*',array('id !=1','status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            }

            $data['title']="Notice Delete :: ". $data['item']['id'];
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/delete",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/delete/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_delete()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        $item_head=$this->input->post('item');
        if($id>0)
        {
            if(!((isset($this->permissions['action3']) && ($this->permissions['action3']==1))))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
            //if($item_head['status']!=$this->config->item('system_status_active') || $item_head['status']!=$this->config->item('system_status_inactive') || $item_head['status']!=$this->config->item('system_status_delete'))
            if($item_head['status']!=$this->config->item('system_status_delete'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Delete Status Field is required.';
                $this->json_return($ajax);
            }
            if(!$item_head['reason_delete'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Delete Status Reason Field is required.';
                $this->json_return($ajax);
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $this->db->trans_start();  //DB Transaction Handle START

        $data=array();

        $data['date_updated']=$time;
        $data['user_updated']=$user->user_id;
        $data['reason_delete']=$item_head['reason_delete'];
        $data['status']=$item_head['status'];
        Query_helper::update($this->config->item('table_pos_setup_notice_request'),$data,array('id='.$id));

        $this->db->trans_complete();   //DB Transaction Handle END

        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function system_details($id)
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_pos_setup_notice_request').' item');
            $this->db->select('item.*');
            $this->db->join($this->config->item('table_pos_setup_notice_types').' type','type.id=item.type_id','INNER');
            $this->db->select('type.name notice_type');
            $this->db->where('item.id',$item_id);
            $this->db->where('item.status !=',$this->config->item('system_status_delete'));
            $this->db->order_by('item.id','DESC');
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                System_helper::invalid_try('Detail Non Exists',$item_id);
                $ajax['status']=false;
                $ajax['system_message']='Invalid Notice.';
                $this->json_return($ajax);
            }
            $data['info_basic']=Notice_helper::get_basic_info($data['item']);
            $data['files']=Query_helper::get_info($this->config->item('table_pos_setup_notice_file_videos'),'*',array('notice_id='.$item_id,'status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));

            $data['user_group_ids']=explode(',',trim($data['item']['user_group_ids'],','));
            $data['urls']=json_decode($data['item']['url_links'],true);
            $data['notice_types']=Query_helper::get_info($this->config->item('table_pos_setup_notice_types'),'*',array('status ="'.$this->config->item('system_status_active').'"'));
            $user=User_helper::get_user();
            if($user->user_group==1)
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),'*',array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            }
            else
            {
                $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),'*',array('id !=1','status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            }

            /* data show hide parameter*/
            $data['user_groups_show']=true;
            /*list action button */
            //$data['action_buttons'][]=array();
            $data['action_buttons'][]=array(
                'label'=>'All List',
                'href'=>site_url($this->controller_url.'/index/list_all')
            );
            $data['action_buttons'][]=array(
                'label'=>'Pending List',
                'href'=>site_url($this->controller_url)
            );
            $data['title']="Notice Details :: ". $data['item']['id'];
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->common_view_location."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$item_id);
            $this->json_return($ajax);

        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
}
