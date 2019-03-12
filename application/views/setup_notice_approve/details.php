<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array
(
    'label'=>'Pending List',
    'href'=>site_url($CI->controller_url)
);
$action_buttons[]=array
(
    'label'=>'All List',
    'href'=>site_url($CI->controller_url.'/index/list_all')
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
    <?php
    $data['accordion']['collapse']='in';
    echo $CI->load->view("info_basic", $data, true);
    ?>
    <table class="table table-bordered table-responsive system_table_details_view">
        <tbody>
        <tr>
            <td class="widget-header header_caption"><label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DESCRIPTION');?></label></td>
            <td class="warning header_value" colspan="3"><label class="control-label"><?php echo $item['description'];?></label></td>
        </tr>
        </tbody>
    </table>

    <?php
    $group['assign']=array();
    $group['un_assign']=array();
    foreach($user_groups as $user_group)
    {
        if(in_array($user_group['id'],$user_group_ids))
        {
            $group['assign'][]=$user_group['name'];
        }
        else
        {
            $group['un_assign'][]=$user_group['name'];
        }
    }
    ?>
    <div class="row show-grid">
        <div class="col-xs-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title text-center">
                        <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#accordion_assign_group" href="#">+ User group (<?php echo sizeof($group['assign'])+sizeof($group['un_assign'])?>)</a></label>
                    </h4>
                </div>
                <div id="accordion_assign_group" class="panel-collapse collapse in">
                    <table class="table table-responsive table-bordered">
                        <tbody>
                        <tr>
                            <th>Assign (<?php echo sizeof($group['assign'])?>)</th>
                            <td>
                                <?php
                                $serial=0;
                                foreach($group['assign'] as $value)
                                {
                                    ++$serial;
                                    ?>
                                    <div class="col-xs-3">
                                        <button type="button" class="btn btn-secondary btn-sm btn-block bg-success"><?php echo $serial.'. '.$value?></button>
                                        <br/>
                                    </div>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Un Assign (<?php echo sizeof($group['un_assign'])?>)</th>
                            <td>
                                <?php
                                $serial=0;
                                foreach($group['un_assign'] as $value)
                                {
                                    ++$serial;
                                    ?>
                                    <div class="col-xs-3">
                                        <button type="button" class="btn btn-secondary btn-sm btn-block bg-danger"><?php echo $serial.'. '.$value?></button>
                                        <br/>
                                    </div>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xs-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title text-center">
                        <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#accordion_link_url" href="#">+ External Link (URL) (<?php echo sizeof($urls);?>)</a></label>
                    </h4>
                </div>
                <div id="accordion_link_url" class="panel-collapse collapse in">
                    <table class="table table-responsive table-bordered">
                        <thead>
                        <tr>
                            <th class="bg-info" style="width: 50px">#SL</th>
                            <th class="bg-info">Link (Url)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(sizeof($urls)>0)
                        {
                            foreach($urls as $key=>$value)
                            {
                                ?>
                                <tr>
                                    <td>
                                        <label class="control-label label_url_link"><?php echo $key+1?></label>
                                    </td>
                                    <td>
                                        <a href="<?php echo $value;?>" class="external" target="_blank">
                                            <?php echo $value;?>
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title text-center">
                        <label class=""><a class="external text-danger" data-toggle="collapse" data-target="#accordion_file_image" href="#">+ Files / Images / Videos (<?php echo sizeof($files);?>)</a></label>
                    </h4>
                </div>
                <div id="accordion_file_image" class="panel-collapse collapse in">
                    <div class="row show-grid">
                        <table class="table table-responsive table-bordered">
                            <thead>
                            <tr>
                                <th style="width: 50px">#SL</th>
                                <th style="width: 50px">File Type</th>
                                <th>File Name & Preview</th>
                                <th><?php echo $CI->lang->line('LABEL_REMARKS');?></th>
                                <th><?php echo $CI->lang->line('LABEL_LINK_URL');?></th>
                                <th style="width: 50px"><?php echo $CI->lang->line('LABEL_ORDER');?></th>
                                <th style="width: 50px">Revision (Edit)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(sizeof($files)>0)
                            {
                                $serial=0;
                                foreach($files as $file)
                                {
                                    ++$serial;
                                    $file_location = $this->config->item('system_base_url_picture') . $file['file_location'];
                                    $image_ext = explode('.', $file['file_name']);
                                    $image_type=false;
                                    if(in_array(strtolower(end($image_ext)) , array('gif', 'png', 'jpg', 'jpeg', 'bmp')))
                                    {
                                        $image_type=true;
                                    }
                                    if($file['file_name']=='no_image.jpg')
                                    {
                                        $file['file_name']='';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $serial?></td>
                                        <td>
                                            <?php
                                            if($file['file_type']==$this->config->item('system_file_type_image'))
                                            {
                                                if($image_type)
                                                {
                                                    echo $this->config->item('system_file_type_image');
                                                }
                                                else
                                                {
                                                    echo 'File';
                                                }
                                            }
                                            else
                                            {
                                                echo $file['file_type'];
                                            }
                                            ?>
                                        </td>
                                        <td><a href='<?php echo $file_location;?>' class="external" target="_blank"><?php echo $file['file_name']?></a></td>
                                        <td><?php echo $file['remarks']?></td>
                                        <td>
                                            <?php
                                            if($file['link_url'])
                                            {
                                            ?>
                                            <a href='<?php echo $file['link_url'];?>' class="external" target="_blank"><?php echo $file['link_url']?></a></td>
                                        <?php
                                        }
                                        ?>
                                        </td>
                                        <td><?php echo $file['ordering']?></td>
                                        <td><?php echo $file['revision_count']?></td>
                                    </tr>
                                <?php
                                }
                            }
                            else
                            {
                                ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="alert alert-danger text-center">
                                            <h6>There is no file or images.</h6>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_off_events();
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
    });
</script>

