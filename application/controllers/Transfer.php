<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Transfer extends CI_Controller
{
    public function index()
    {
        //$this->farmer();
        //$this->users();
        //$this->payment();
        //$this->sale_details();
        //$this->sale();
        //$this->stock();
    }
    private function farmer()
    {
        $source_tables=array(
            'farmer'=>'arm_pos.pos_setup_farmer_farmer',
            'farmer_outlet'=>'arm_pos.pos_setup_farmer_outlet'
        );
        $destination_tables=array(
            'farmer'=>$this->config->item('table_pos_setup_farmer_farmer'),
            'farmer_outlet'=>$this->config->item('table_pos_setup_farmer_outlet')
        );
        $farmers=Query_helper::get_info($source_tables['farmer'],'*',array());
        $farmer_outlets=Query_helper::get_info($source_tables['farmer_outlet'],'*',array());
        $this->db->trans_start();  //DB Transaction Handle START

        foreach($farmers as $result)
        {
            $data=array();
            $data['id']=$result['id'];
            $data['name']=$result['name'];
            $data['farmer_type_id']=$result['type_id'];
            if($result['type_id']>1)
            {

                $data['farmer_type_id']=2;
                $data['status_card_require']=$this->config->item('system_status_yes');
            }
            else
            {
                $data['farmer_type_id']=1;
                $data['status_card_require']=$this->config->item('system_status_no');
            }
            $data['mobile_no']=$result['mobile_no'];
            $data['nid']=$result['nid'];
            $data['address']=$result['address'];
            $data['time_card_off_end']=$result['time_card_off_end'];
            if(in_array($result['id'],array(1,429,1520)))
            {
                $data['status']=$this->config->item('system_status_inactive');
            }
            else
            {
                $data['status']=$this->config->item('system_status_active');
            }
            $data['time_card_off_end']=$result['time_card_off_end'];
            $data['date_created']=$result['date_created'];
            $data['user_created']=$result['user_created'];
            $data['date_updated']=$result['date_updated'];
            $data['user_updated']=$result['user_updated'];
            Query_helper::add($destination_tables['farmer'],$data,false);
        }
        foreach($farmer_outlets as $result)
        {
            $data=array();
            $data['id']=$result['id'];
            $data['farmer_id']=$result['farmer_id'];
            $data['outlet_id']=$result['customer_id'];
            $data['revision']=$result['revision'];
            $data['date_created']=$result['date_created'];
            $data['user_created']=$result['user_created'];
            $data['date_updated']=$result['date_updated'];
            $data['user_updated']=$result['user_updated'];
            Query_helper::add($destination_tables['farmer_outlet'],$data,false);
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Success Transfer Farmer';
        }
        else
        {
            echo 'Failed Transfer Farmer';
        }
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
            $not_found_user_ids=array(191,192);
            if(in_array($result['user_created'],$not_found_user_ids))
            {
                $data['user_deposit_updated']=1;
            }
            else
            {
                $data['user_deposit_updated']=$payment_users_new[$payment_users_old[$result['user_created']]];
            }

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
                if(in_array($result['user_created'],$not_found_user_ids))
                {
                    $data['user_deposit_forwarded']=1;
                }
                else
                {
                    $data['user_deposit_forwarded']=$payment_users_new[$payment_users_old[$result['user_created']]];
                }


                $data['date_receive']=$result['date_payment_receive'];
                $data['amount_bank_charge']=$result['amount']-$result['amount_customer'];
                $data['amount_receive']=$result['amount'];
                //$data['remarks_receive']='';
                $data['status_payment_receive']=$this->config->item('system_status_received');
                $data['date_payment_received']=$result['date_receive'];
                if(in_array($result['user_receive'],$not_found_user_ids))
                {
                    $data['user_payment_received']=1;
                }
                else
                {
                    $data['user_payment_received']=$payment_users_new[$payment_users_old[$result['user_receive']]];
                }
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
            'sale'=>'arm_pos.pos_sale'
        );
        $destination_tables=array(
            'sale'=>$this->config->item('table_pos_sale'),
            'sale_cancel'=>$this->config->item('table_pos_sale_cancel')
        );
        $results=Query_helper::get_info($source_tables['sale'],'*',array());
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['id']=$result['id'];
            $data['outlet_id']=$result['customer_id'];
            $data['outlet_id_commission']=$result['customer_id'];
            $data['farmer_id']=$result['farmer_id'];
            $data['discount_self_percentage']=$result['discount_percentage'];
            $data['amount_total']=$result['amount_total'];
            $data['amount_discount_variety']=0;
            $data['amount_discount_self']=$result['amount_total']-$result['amount_payable'];
            $data['amount_payable']=$result['amount_payable'];
            $data['amount_payable_actual']=$result['amount_payable'];
            $data['amount_cash']=$result['amount_cash']+$result['amount_previous_paid'];
            $data['date_sale']=$result['date_sale'];
            $data['remarks']=$result['remarks'];
            $data['status']=$result['status'];
            if($result['status']==$this->config->item('system_status_inactive'))
            {
                $cancel_data=array();
                $cancel_data['sale_id']=$result['id'];
                $cancel_data['date_cancel']=$result['date_canceled'];


                $cancel_data['date_cancel_requested']=$result['date_canceled'];
                $cancel_data['user_cancel_requested']=$result['user_canceled'];
                $cancel_data['remarks_cancel_requested']=$result['remarks'].'<br>--System Request';

                $cancel_data['date_cancel_approved']=$result['date_canceled'];
                $cancel_data['user_cancel_approved']=$result['user_canceled'];
                $cancel_data['remarks_cancel_approved']=$result['remarks'].'<br>--System Approve';

                $cancel_data['status_approve']=$this->config->item('system_status_approved');

                Query_helper::add($destination_tables['sale_cancel'],$cancel_data,false);

                $data['date_cancel']=$result['date_canceled'];
                $data['date_cancel_approved']=$result['date_canceled'];
                $data['user_cancel_approved']=$result['user_canceled'];
                $data['remarks_cancel_approved']=$result['remarks'].'<br>--System Approve';
            }
            $data['date_created']=$result['date_created'];
            $data['user_created']=$result['user_created'];
            Query_helper::add($destination_tables['sale'],$data,false);

        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Success transfer Sale';
        }
        else
        {
            echo 'Failed transfer Sale';
        }

    }
    private function stock()
    {
        //stock transfer after sale transfer complted
        $source_tables=array(
            'outlets'=>'arm_ems.ems_csetup_customers',
            'po_receives'=>'arm_ems.ems_sales_po_receives',
            'po_details'=>'arm_ems.ems_sales_po_details',
            'po'=>'arm_ems.ems_sales_po',
            'sale'=>$this->config->item('table_pos_sale'),
            'sale_details'=>$this->config->item('table_pos_sale_details')
        );
        $destination_tables=array(
            'stock'=>$this->config->item('table_pos_stock_summary_variety')

        );
        //sale quantity
        $this->db->from($source_tables['sale_details'].' details');
        $this->db->select('SUM(details.quantity) out_sale');
        $this->db->select('details.variety_id,details.pack_size_id,details.pack_size');
        $this->db->join($source_tables['sale'].' sale','sale.id = details.sale_id','INNER');
        $this->db->select('sale.outlet_id');
        $this->db->where('sale.status','Active');//ignored deleted
        $this->db->group_by('sale.outlet_id');
        $this->db->group_by('details.variety_id');
        $this->db->group_by('details.pack_size_id');
        $this->db->order_by('sale.outlet_id','ASC');
        $results=$this->db->get()->result_array();
        $sales=array();
        foreach($results as $result)
        {
            $sales[$result['outlet_id']][$result['variety_id']][$result['pack_size_id']]=$result;
        }


        $results=Query_helper::get_info($source_tables['outlets'],array('id,name,customer_code'),array('type ="Outlet"'));
        $outlet_ids=array();
        foreach($results as $result)
        {
            $outlet_ids[$result['id']]=$result['id'];
        }

        $this->db->from($source_tables['po_receives'].' por');
        $this->db->select('SUM(por.quantity_receive-pod.quantity_return) in_wo');
        //$this->db->select('por.quantity_receive');//sum
        $this->db->join($source_tables['po_details'].' pod','pod.id =por.sales_po_detail_id','INNER');
        $this->db->select('pod.variety_id,pod.pack_size_id,pod.pack_size');
        //$this->db->select('pod.quantity_return');//sum
        $this->db->join($source_tables['po'].' po','po.id =pod.sales_po_id','INNER');
        $this->db->select('po.customer_id outlet_id');
        $this->db->where('po.status_received',$this->config->item('system_status_received'));
        $this->db->where_in('po.customer_id',$outlet_ids);
        $this->db->where('por.revision',1);
        $this->db->where('pod.revision',1);
        $this->db->group_by('po.customer_id');
        $this->db->group_by('pod.variety_id');
        $this->db->group_by('pod.pack_size_id');
        $this->db->order_by('po.customer_id','ASC');
        $results=$this->db->get()->result_array();
        $time=time();
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['outlet_id']=$result['outlet_id'];
            $data['variety_id']=$result['variety_id'];
            $data['pack_size_id']=$result['pack_size_id'];
            $data['in_wo']=$result['in_wo'];
            $data['out_sale']=0;
            if(isset($sales[$result['outlet_id']][$result['variety_id']][$result['pack_size_id']]))
            {
                $data['out_sale']=$sales[$result['outlet_id']][$result['variety_id']][$result['pack_size_id']]['out_sale'];
            }
            $data['current_stock']=$data['in_wo']-$data['out_sale'];
            $data['date_updated']=$time;
            $data['user_updated']=1;
            Query_helper::add($destination_tables['stock'],$data,false);

        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Success transfer Stock';
        }
        else
        {
            echo 'Failed transfer Stock';
        }
    }
}
