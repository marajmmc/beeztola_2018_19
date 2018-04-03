<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Transfer extends CI_Controller
{
    public function index()
    {
        //$this->users();
        //$this->payment();
        //$this->sale_details();
        //$this->sale();
    }
    private function users()
    {
        $source_tables=array(
            'setup_user'=>'arm_pos.pos_setup_user',
            'setup_user_info'=>'arm_pos.pos_setup_user_info',
            'setup_user_outlet'=>'arm_pos.pos_setup_user_outlet'
        );
        $destination_tables=array(
            'setup_user'=>$this->config->item('table_pos_setup_user'),
            'setup_user_info'=>$this->config->item('table_pos_setup_user_info'),
            'setup_user_outlet'=>$this->config->item('table_pos_setup_user_outlet')
        );

        $users=Query_helper::get_info($source_tables['setup_user'],'*',array());

        $results=Query_helper::get_info($source_tables['setup_user_info'],'*',array('revision=1'));
        $user_infos=array();
        foreach($results as $result)
        {
            $user_infos[$result['user_id']]=$result;
        }

        $results=Query_helper::get_info($source_tables['setup_user_outlet'],'*',array('revision=1'));
        $user_outlets=array();
        foreach($results as $result)
        {
            $user_outlets[$result['user_id']][]=$result;
        }


        $this->db->trans_start();  //DB Transaction Handle START

        foreach($users as $user)
        {
//            if($user['id']==1)
//            {
//                $user['password']=md5("Arm!@#$");
//            }
            if(strlen($user['user_name'])==4)
            {
                $user['employee_id']=$user['user_name'];
            }
            Query_helper::add($destination_tables['setup_user'],$user,false);
            $data_user_info=$user_infos[$user['id']];
            unset($data_user_info['id']);
            $data_user_info['designation_id']=$data_user_info['designation'];
            unset($data_user_info['designation']);
            Query_helper::add($destination_tables['setup_user_info'],$data_user_info,false);
            if(isset($user_outlets[$user['id']]))
            {
                foreach($user_outlets[$user['id']] as $data)
                {
                    unset($data['id']);
                    Query_helper::add($destination_tables['setup_user_outlet'],$data,false);
                }
            }
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Success Transfer users';
        }
        else
        {
            echo 'Failed Transfer users';
        }
    }
    private function payment()
    {
        $source_tables=array(
            'user_pos_new'=>$this->config->item('table_pos_setup_user'),
            'user_login_old'=>'arm_login.setup_user',
            'outlets'=>'arm_ems.ems_csetup_customers',
            'payment_way'=>$this->config->item('table_login_setup_payment_way'),
            'payment'=>'arm_ems.ems_payment_payment'
        );
        $destination_tables=array(
            'payment'=>$this->config->item('table_pos_payment')
        );

        $results=Query_helper::get_info($source_tables['user_login_old'],array('id,user_name'),array());
        $payment_users_old=array();
        foreach($results as $result)
        {
            $payment_users_old[$result['id']]=$result['user_name'];
        }

        $results=Query_helper::get_info($source_tables['user_pos_new'],array('id,user_name'),array());
        $payment_users_new=array();
        foreach($results as $result)
        {
            $payment_users_new[$result['user_name']]=$result['id'];
        }
        $results=Query_helper::get_info($source_tables['payment_way'],array('id,name'),array());
        $payment_ways=array();
        foreach($results as $result)
        {
            $payment_ways[$result['name']]=$result['id'];
        }
        $results=Query_helper::get_info($source_tables['outlets'],array('id,name,customer_code'),array('type ="Outlet"'));
        $outlet_ids=array();
        foreach($results as $result)
        {
            $outlet_ids[$result['id']]=$result['id'];
        }
        $this->db->from($source_tables['payment']);
        $this->db->where_in('customer_id',$outlet_ids);
        $this->db->where('status','Active');//ignored deleted
        $this->db->order_by('id','ASC');
        $results=$this->db->get()->result_array();
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['id']=$result['id'];
            $data['date_payment']=$result['date_payment_customer'];
            $data['date_sale']=$result['date_payment_customer']-3600*24;
            $data['outlet_id']=$result['customer_id'];
            /*if(!(isset($payment_ways[$result['payment_way']])))
            {
                die('Payment way not found');
            }*/
            $data['payment_way_id']=$payment_ways[$result['payment_way']];
            $data['reference_no']=$result['cheque_no'];
            $data['amount_payment']=$result['amount_customer'];
            /*if(!($result['amount_customer']>0))
            {
                die('Payment amount is 0');
            }*/
            $data['bank_id_source']=$result['bank_id'];
            $data['bank_branch_source']=$result['bank_branch'];
            //$data['image_name']='no_image.jpg';
            //$data['image_location']='images/no_image.jpg';
            //$data['remarks_deposit']='';
            //$data['revision_count_deposit']=1;

            $data['date_deposit_updated']=$result['date_created'];
            $data['user_deposit_updated']=$payment_users_new[$payment_users_old[$result['user_created']]];
            if(!($result['arm_bank_id']>0))
            {
                $data['bank_account_id_destination']=0;
                //$data['status_deposit_forward']=$this->config->item('system_status_pending');

            }
            else
            {
                $data['bank_account_id_destination']=$result['arm_bank_id'];
                $data['status_deposit_forward']=$this->config->item('system_status_forwarded');
                $data['date_deposit_forwarded']=$result['date_created'];
                $data['user_deposit_forwarded']=$payment_users_new[$payment_users_old[$result['user_created']]];
                $data['date_receive']=$result['date_payment_receive'];
                $data['amount_bank_charge']=$result['amount']-$result['amount_customer'];
                $data['amount_receive']=$result['amount'];
                //$data['remarks_receive']='';
                $data['status_payment_receive']=$this->config->item('system_status_received');
                $data['date_payment_received']=$result['date_receive'];
                $data['user_payment_received']=$payment_users_new[$payment_users_old[$result['user_receive']]];
            }
            //$data['status']=$result['status'];
//            echo '<pre>';
//            print_r($data);
//            echo '</pre>';
            Query_helper::add($destination_tables['payment'],$data,false);
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Success transfer payment';
        }
        else
        {
            echo 'Failed transfer payment';
        }


    }
    private function sale_details()
    {
        $source_tables=array(
            'sale_details'=>'arm_pos.pos_sale_details'
        );
        $destination_tables=array(
            'sale_details'=>$this->config->item('table_pos_sale_details'),
        );
        $results=Query_helper::get_info($source_tables['sale_details'],'*',array());
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['id']=$result['id'];
            $data['sale_id']=$result['sale_id'];
            $data['variety_id']=$result['variety_id'];
            $data['pack_size_id']=$result['pack_size_id'];
            $data['pack_size']=$result['pack_size'];
            $data['price_unit_pack']=$result['price_unit'];
            $data['quantity']=$result['quantity_sale'];
            $data['amount_total']=$result['quantity_sale']*$result['price_unit'];
            $data['discount_percentage_variety']=0;
            $data['amount_discount_variety']=0;
            $data['amount_payable_actual']=$data['amount_total'];
            Query_helper::add($destination_tables['sale_details'],$data,false);

        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Success transfer Sale Details';
        }
        else
        {
            echo 'Failed transfer Sale Details';
        }

    }
    private function sale()
    {
        $source_tables=array(
            'sale'=>'arm_pos.pos_sale',
            'sale_details'=>'arm_pos.pos_sale_details'
        );
        $destination_tables=array(
            'sale'=>$this->config->item('table_pos_sale'),
            'sale_details'=>$this->config->item('table_pos_sale_details'),
            'sale_cancel'=>$this->config->item('table_pos_sale_cancel')
        );
    }

}
