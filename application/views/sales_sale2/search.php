<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div id="container_farmer_search">
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_OUTLET_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if(sizeof($CI->user_outlets)==1)
                {
                    ?>
                    <input type="hidden" id="outlet_id" value="<?php echo $CI->user_outlets[0]['customer_id'] ?>" />
                    <label class="control-label"><?php echo $CI->user_outlets[0]['name'] ?></label>
                    <?php
                }
                else
                {
                    ?>
                    <select id="outlet_id" class="form-control">
                        <?php
                        foreach($CI->user_outlets as $row)
                        {?>
                            <option value="<?php echo $row['customer_id']?>"><?php echo $row['name'];?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="row show-grid" id="container_farmer_type">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CUSTOMER_TYPE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="farmer_type_id" class="form-control">
                    <option value=""><?php echo $CI->lang->line('SELECT');?></option>
                    <?php
                    foreach($farmer_types as $row)
                    {?>
                        <option value="<?php echo $row['value']?>"><?php echo $row['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div style="display: none;" class="row show-grid" id="farmer_id_container">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CUSTOMER_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="farmer_id" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">
                    <?php echo $CI->lang->line('LABEL_MOBILE_NO');?> |<br>
                    Scan Dealer Card |<br>
                    Scan Old Invoice
                </label>
            </div>
            <div class="col-sm-4 col-xs-4">
                <input type="text" id="code" class="form-control" value=""/>
            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="action_button">
                    <button id="button_action_farmer_search" type="button" class="btn"><?php echo $CI->lang->line('LABEL_SEARCH');?></button>
                </div>
            </div>
        </div>

    </div>

    <div id="container_farmer_new" style="display: none;">
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_MOBILE_NO');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" id="mobile_no" class="form-control" value=""/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" id="name" class="form-control" value=""/>
            </div>
        </div>

        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_NID');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" id="nid" class="form-control" value=""/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DISTRICT_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="district_id" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                </select>
            </div>
        </div>
        <div style="display:none;" class="row show-grid" id="upazilla_id_container">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_UPAZILLA_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="upazilla_id" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                </select>
            </div>
        </div>
        <div style="display:none;" class="row show-grid" id="union_id_container">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_UNION_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="union_id" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_ADDRESS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea class="form-control" id="farmer_address"></textarea>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
            </div>
            <div class="col-sm-4 col-xs-8">
                <div class="action_button">
                    <button id="button_action_farmer_save" type="button" class="btn"><?php echo $CI->lang->line('ACTION_SAVE');?></button>
                </div>
                <div class="action_button">
                    <button id="button_action_farmer_cancel" type="button" class="btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function search_farmer(code_scan_type)
    {
        var outlet_id=$('#outlet_id').val();
        var code=$('#code').val();
        if((outlet_id>0)&& (code.length>0))
        {
            $.ajax({
                url:'<?php echo site_url($CI->controller_url.'/index/search_farmer') ?>',
                type: 'POST',
                datatype: "JSON",
                data:{outlet_id:outlet_id,code:code,code_scan_type:code_scan_type},
                success: function (data, status)
                {
                    if(data['farmer_new']!==undefined && data['farmer_new']==true)
                    {
                        $('#container_farmer_new').show();
                        $('#upazilla_id_container').hide();
                        $('#union_id_container').hide();
                        if(data['hide_code']!==undefined && data['hide_code']==true)
                        {
                            $('#mobile_no').val('');
                        }
                        else
                        {
                            $('#mobile_no').val(code);
                        }
                    }
                    if(data['hide_code']!==undefined && data['hide_code']==true)
                    {
                        $('#code').val('');
                    }

                },
                error: function (xhr, desc, err)
                {
                    console.log("error");

                }
            });
        }
    }
    jQuery(document).ready(function()
    {
        system_off_events();
        var first_char_time=0;
        var last_char_time=0;
        $(document).on("change","#outlet_id",function()
        {
            $('#container_farmer_new').hide();
            $("#farmer_type_id").val("");
            $("#farmer_id").val("");
            $('#farmer_id_container').hide();

        });
        $(document).off("change", "#farmer_type_id");
        $(document).on("change","#farmer_type_id",function()
        {
            $('#container_farmer_new').hide();
            $("#farmer_id").val("");
            var farmer_type_id=$('#farmer_type_id').val();
            var outlet_id=$('#outlet_id').val();
            if((outlet_id>0)&&farmer_type_id>0)
            {
                $('#farmer_id_container').show();
                $.ajax({
                    url:'<?php echo site_url($CI->controller_url.'/get_dropdown_farmers_by_outlet_farmer_type_id') ?>',
                    type: 'POST',
                    datatype: "JSON",
                    data:{outlet_id:outlet_id,farmer_type_id:farmer_type_id},
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });

            }
            else
            {
                $('#farmer_id_container').hide();

            }
        });
        $(document).off("change", "#farmer_id");
        $(document).on("change","#farmer_id",function()
        {
            $('#code').val($('#farmer_id').val());
        });

        $(document).on("change","#district_id",function()
        {
            $("#upazilla_id").val("");
            $("#union_id").val("");
            var district_id=$("#district_id").val();
            $('#upazilla_id_container').hide();
            $('#union_id_container').hide();
            if(district_id>0)
            {
                $('#upazilla_id_container').show();
                $.ajax({
                    url: '<?php echo site_url("common_controller/get_dropdown_upazillas_by_districtid/");?>',
                    type: 'POST',
                    datatype: "JSON",
                    data:{district_id:district_id},
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }
        });
        $(document).on("change","#upazilla_id",function()
        {
            $("#union_id").val("");
            var upazilla_id=$("#upazilla_id").val();
            $('#union_id_container').hide();
            if(upazilla_id>0)
            {
                $('#union_id_container').show();
                $.ajax({
                    url: '<?php echo site_url('common_controller/get_dropdown_unions_by_upazillaid'); ?>',
                    type: 'POST',
                    datatype: "JSON",
                    data:{
                        upazilla_id:upazilla_id,
                        html_container_id:'#union_id'
                    },
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }
        });

        $(document).off("click", "#button_action_farmer_search");
        $(document).on("click","#button_action_farmer_search",function()
        {
            var code_scan_type='TYPE';
            if(first_char_time==0)
            {
                code_scan_type='COPY';
            }
            else if((last_char_time-first_char_time)<150)
            {
                code_scan_type='SCAN';
            }
            search_farmer(code_scan_type);

        });
        $(document).off("keypress", "#code");
        $(document).on("keypress","#code",function(event)
        {
            var code=$('#code').val();
            if(code.length==1)
            {
                last_char_time=first_char_time=Date.now();
            }
            else if(code.length>1)
            {
                last_char_time=Date.now();
            }
            else
            {
                first_char_time=last_char_time=0;
            }
            $('#container_farmer_new').hide();

            if(event.which == 13)
            {
                var code_scan_type='TYPE';
                if(first_char_time==0)
                {
                    code_scan_type='COPY';
                }
                else if((last_char_time-first_char_time)<150)
                {
                    code_scan_type='SCAN';
                }
                search_farmer(code_scan_type);
            }


        });
        $(document).off("click", "#button_action_farmer_cancel");
        $(document).on("click","#button_action_farmer_cancel",function()
        {
            $('#container_farmer_new').hide();

        });
        $(document).off("click", "#button_action_farmer_save");
        $(document).on("click","#button_action_farmer_save",function()
        {
            var sure = confirm('Are you Sure to Create this Customer?');
            if(!sure)
            {
                return;
            }
            var outlet_id=$('#outlet_id').val();
            var mobile_no=$('#mobile_no').val();
            var name=$('#name').val();
            var nid=$('#nid').val();
            var address=$('#farmer_address').val();
            var union_id=$('#union_id').val();
            $.ajax({
                url:'<?php echo site_url($CI->controller_url.'/index/save_farmer') ?>',
                type: 'POST',
                datatype: "JSON",
                data:{outlet_id:outlet_id,mobile_no:mobile_no,name:name,address:address,nid:nid,union_id:union_id},
                success: function (data, status)
                {

                },
                error: function (xhr, desc, err)
                {
                    console.log("error");

                }
            });
        });

    });
</script>