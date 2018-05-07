<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Transfer2 extends CI_Controller
{
    public function index()
    {
        $this->check_sales_validation();

        //$this->sale_details();
        //$this->sale();
        //$this->stock();
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
