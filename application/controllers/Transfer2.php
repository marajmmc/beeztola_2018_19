<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Transfer2 extends CI_Controller
{
    public function index()
    {
        //$this->check_sales_validation();
        //$this->sale();
        //$this->farmer();
    }
    private function check_sales_validation()
    {
        $source_tables=array(
            'sale'=>'arm_pos.pos_sale',
            'sale_details'=>'arm_pos.pos_sale_details',
            'new_sale'=>$this->config->item('table_pos_sale'),
            'new_sale_details'=>$this->config->item('table_pos_sale_details'),
            'new_sale_cancel'=>$this->config->item('table_pos_sale_cancel'),
            'stock'=>$this->config->item('table_pos_stock_summary_variety')
        );
        $beeztola_last_sale=Query_helper::get_info($source_tables['new_sale'],'*',array(),1,0,array('id DESC'));
        $pos_sales=Query_helper::get_info($source_tables['sale'],array(),array('id>= '.$beeztola_last_sale['id']),2,0,array('id ASC'));
        if($beeztola_last_sale['id']!=$pos_sales[0]['id'])
        {
            echo "<br> Sale id did not match";
            die();
        }
        else
        {
            echo "<br> Sale Id ok";
            echo '<br>New starting Id '.$pos_sales[1]['id'];
        }
        $beeztola_last_sale_details=Query_helper::get_info($source_tables['new_sale_details'],'*',array(),1,0,array('id DESC'));
        $pos_sales_details=Query_helper::get_info($source_tables['sale_details'],array(),array('id>= '.$beeztola_last_sale_details['id']),2,0,array('id ASC'));
        if($beeztola_last_sale_details['id']!=$pos_sales_details[0]['id'])
        {
            echo '<br>Sale details id did not match<br>';
            die();
        }
        else
        {
            echo '<br>Sale Details Id ok';
            echo '<br>New starting Sale details Id '.$pos_sales_details[1]['id'];
        }
        $results=Query_helper::get_info($source_tables['stock'],'*',array());
        $stocks=array();
        foreach($results as $result)
        {
            $stocks[$result['outlet_id']][$result['variety_id']][$result['pack_size_id']]=$result;
        }
        $this->db->from($source_tables['sale_details'].' details');
        $this->db->select('details.variety_id,details.pack_size_id');
        $this->db->join($source_tables['sale'].' sale','sale.id = details.sale_id','INNER');
        $this->db->select('sale.customer_id outlet_id');
        $this->db->order_by('details.id','ASC');
        $results=$this->db->get()->result_array();
        echo '<br><br><br><br>Checking Stock table validation';
        foreach($results as $result)
        {
            if(!(isset($stocks[$result['outlet_id']][$result['variety_id']][$result['pack_size_id']])))
            {
                echo '<br>Stock Column not found-'.$result['outlet_id'].'-'.$result['variety_id'].'-'.$result['pack_size_id'];
            }
        }
        echo '<br>finished';
    }
    private function sale()
    {
        $source_tables=array(
            'sale'=>'arm_pos.pos_sale',
            'sale_details'=>'arm_pos.pos_sale_details',
            'new_sale'=>$this->config->item('table_pos_sale'),
            'new_sale_details'=>$this->config->item('table_pos_sale_details'),
            'new_sale_cancel'=>$this->config->item('table_pos_sale_cancel'),
            'stock'=>$this->config->item('table_pos_stock_summary_variety')
        );

        $destination_tables=array(
            'sale'=>$this->config->item('table_pos_sale'),
            'sale_cancel'=>$this->config->item('table_pos_sale_cancel'),
            'sale_details'=>$this->config->item('table_pos_sale_details'),
            'stock'=>$this->config->item('table_pos_stock_summary_variety')
        );
        $results=Query_helper::get_info($source_tables['stock'],'*',array());
        $stocks=array();
        foreach($results as $result)
        {
            $stocks[$result['outlet_id']][$result['variety_id']][$result['pack_size_id']]=$result;
        }
        $data_stock_sales=array();
        $beeztola_last_sale=Query_helper::get_info($source_tables['new_sale'],'*',array(),1,0,array('id DESC'));
        $pos_sales=Query_helper::get_info($source_tables['sale'],array(),array('id> '.$beeztola_last_sale['id']),0,0,array('id ASC'));
        $beeztola_last_sale_details=Query_helper::get_info($source_tables['new_sale_details'],'*',array(),1,0,array('id DESC'));
        $pos_sales_details=Query_helper::get_info($source_tables['sale_details'],array(),array('id> '.$beeztola_last_sale_details['id']),0,0,array('id ASC'));
        $pos_sales_array=array();
        $this->db->trans_start();  //DB Transaction Handle START
        //sales add start
        foreach($pos_sales as $result)
        {
            $pos_sales_array[$result['id']]=$result;

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
        //sales adding complete
        //details add start
        foreach($pos_sales_details as $result)
        {
            if(isset($data_stock_sales[$pos_sales_array[$result['sale_id']]['customer_id']][$result['variety_id']][$result['pack_size_id']]))
            {
                $data_stock_sales[$pos_sales_array[$result['sale_id']]['customer_id']][$result['variety_id']][$result['pack_size_id']]+=$result['quantity_sale'];
            }
            else
            {
                $data_stock_sales[$pos_sales_array[$result['sale_id']]['customer_id']][$result['variety_id']][$result['pack_size_id']]=$result['quantity_sale'];
            }
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
        //details adding finished
        //stock update
        foreach($data_stock_sales as $outlet_id=>$outlet_stock)
        {
            foreach($outlet_stock as $variety_id=>$pack_sizes)
            {
                foreach($pack_sizes as $pack_size_id=>$out_sale)
                {
                    $data=array();
                    $data['out_sale']=$stocks[$outlet_id][$variety_id][$pack_size_id]['out_sale']+$out_sale;
                    $data['current_stock']=$stocks[$outlet_id][$variety_id][$pack_size_id]['current_stock']-$out_sale;
                    Query_helper::update($destination_tables['stock'],$data,array('id ='.$stocks[$outlet_id][$variety_id][$pack_size_id]['id']),false);
                }
            }
        }
        //stock update finish

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
    private function farmer()
    {
        $source_tables=array(
            'farmer'=>'arm_pos.pos_setup_farmer_farmer',
            'farmer_outlet'=>'arm_pos.pos_setup_farmer_outlet',
            'farmer_new'=>$this->config->item('table_pos_setup_farmer_farmer'),
            'farmer_outlet_new'=>$this->config->item('table_pos_setup_farmer_outlet')
        );
        $destination_tables=array(
            'farmer'=>$this->config->item('table_pos_setup_farmer_farmer'),
            'farmer_outlet'=>$this->config->item('table_pos_setup_farmer_outlet')
        );
        $beeztola_last_farmer=Query_helper::get_info($source_tables['farmer_new'],'*',array(),1,0,array('id DESC'));
        $pos_farmer=Query_helper::get_info($source_tables['farmer'],array(),array('id> '.$beeztola_last_farmer['id']),0,0,array('id ASC'));
        $beeztola_last_farmer_outlet=Query_helper::get_info($source_tables['farmer_outlet_new'],'*',array(),1,0,array('id DESC'));
        $pos_farmer_outlet=Query_helper::get_info($source_tables['farmer_outlet'],array(),array('id> '.$beeztola_last_farmer_outlet['id']),0,0,array('id ASC'));

        $this->db->trans_start();  //DB Transaction Handle START

        foreach($pos_farmer as $result)
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
        foreach($pos_farmer_outlet as $result)
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
}
