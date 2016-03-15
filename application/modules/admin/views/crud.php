<?php
if(isset($form)) {
    echo $form->messages();
}
?>

<?php if ( !empty($crud_note) ) echo "<p>$crud_note</p>"; ?>

<?php if(!empty($user_info)): ?>
    <div class="box box-primary widget-user">
        <!-- Add the bg color to the header using any of the bg-* classes -->
        <div class="widget-user-header bg-aqua-active">
            <h3 class="widget-user-username"><?php echo $user_info->full_name; ?></h3>
            <span class="pull-right badge bg-blue"><?php echo $user_info->gender==1?'Female':'Male'; ?></span>
            <h5 class="widget-user-desc"><?php echo $user_info->email; ?></h5>
            <h5 class="widget-user-desc"><?php echo $user_info->phone; ?></h5>
        </div>
        <div class="widget-user-image">
            <img class="img-circle" src="<?php echo $user_info->photo?$user_info->photo:base_url('assets/images/avatar_default.jpg'); ?>" alt="User Avatar">
        </div>
        <div class="box-footer">
            <div class="row">
                <div class="col-sm-4">
                    <button type="button" class="btn btn-block btn-danger btn-lg" onclick="onClickedBlockUser()"><?php echo $user_info->active==1?'Block User':'Unblock User'; ?></button>
                </div>
                <!-- /.col -->
                <div class="col-sm-4 border-right">
                    <div class="description-block">
                        <h5 class="description-header"></h5>
                        <span class="description-text"></span>
                    </div>
                    <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-4">
                    <div class="description-block">
                        <h5 class="description-header"></h5>
                        <span class="description-text"></span>
                    </div>
                    <!-- /.description-block -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
    </div>

    <?php if(!empty($map)): ?>
    <div>
        <h4>User Addresses</h4>
        <head>
            <?php echo $map['js']; ?>
        </head>
        <div class="box box-primary">
            <?php echo $map['html']; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($user_info) && $user_info->user_type==1): ?>
        <div class="col-md-12">
            <h4>User Parts</h4>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if(!empty($product_make)): ?>
    <div class="form-group">
        <label>Make</label>
        <select id="make_picker" class="form-control select2" style="width: 100%;">
            <?php foreach($makes as $make): ?>
                <?php if($make->id==$selected_make_id): ?>
                    <option id="<?php echo $make->id; ?>" selected="selected"><?php echo $make->title; ?></option>
                <?php else: ?>
                    <option id="<?php echo $make->id; ?>"><?php echo $make->title; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

<?php if(!empty($product_category)): ?>
    <div class="form-group">
        <label>Category</label>
        <select id="category_picker" class="form-control select2" style="width: 100%;">
            <?php foreach($categories as $category): ?>
                <?php if($category->id==$selected_category_id): ?>
                    <option id="<?php echo $category->id; ?>" selected="selected"><?php echo $category->title; ?></option>
                <?php else: ?>
                    <option id="<?php echo $category->id; ?>"><?php echo $category->title; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

<?php if(!empty($product_image_category)): ?>
    <div class="form-group">
        <label>Product (id)</label>
        <select id="product_picker" class="form-control select2" style="width: 100%;">
            <?php foreach($products as $product): ?>
                <?php if($product->id==$selected_product_id): ?>
                    <option id="<?php echo $product->id; ?>" selected="selected"><?php echo $product->name.' ('.($product->id).')'; ?></option>
                <?php else: ?>
                    <option id="<?php echo $product->id; ?>"><?php echo $product->name.' ('.($product->id).')'; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

<?php if(empty($user_info) || $user_info->user_type==1): ?>
    <?php if ( !empty($crud_output) ) echo $crud_output; ?>
<?php endif; ?>

<?php if(isset($upload_url)): ?>
    <?= form_open_multipart(base_url($upload_url))?>
    <?= form_upload(array('id' => 'txtFileImport', 'class' => 'inputfile', 'name' => 'fileImport', 'accept' => '.csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'))?>
    <?= form_close()?>
<?php endif; ?>

<style>
    .text-wrap1 {
        max-width: 150px;
        display: block;
        word-break: break-all;
        word-wrap: break-word;
    }

    ul {
        list-style-type: none;
    }

    ul.h-images {
        margin: 0;
        padding: 0;
        white-space: nowrap;
        width: 120px;
        height: 70px;
        overflow-x: scroll;
        overflow-scrolling: touch;
        -webkit-overflow-scrolling: touch;
    }

    ul.h-images li {
        display: inline;
        width: 80px;
        height: 50px;
        padding: 5px;
    }

    .inputfile {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }
</style>

<script>
    $(function() {
        var allow_bulk_upload = false;
        <?php if(isset($allow_bulk_upload)): ?>
        allow_bulk_upload = '<?php echo $allow_bulk_upload?>';
        <?php endif; ?>
        if(allow_bulk_upload) {
            $('.tDiv3').prepend('<div id="btn_import" class="fbutton" onclick="onClickedImportButton()"><span class="export" style="color: blue;">Import</span></div><div class="btnseparator"></div>');
        }

        $('#product_picker').on('change', function() {
            var selectedValue = $(this).val();
            var selectedId = selectedValue.split("(")[1].split(")")[0];
            window.location.href = '<?php echo base_url();?>admin/product/images/' + selectedId;
        });

        $('#category_picker').on('change', function() {
            //var selectedValue = $(this).val();
            //var selectedId = selectedValue.split("(")[1].split(")")[0];
            var selectedId = $(this).children(":selected").attr("id");
            var currentUrl = document.URL;
            if(currentUrl.includes("add"))
            {
                window.location.href = '<?php echo base_url();?>admin/product/index/' + selectedId + '/' + $('#make_picker').children(":selected").attr("id") + '/add';
            } else if(currentUrl.includes("edit")) {
                var lastParam = currentUrl.split('/edit')[1];
                window.location.href = '<?php echo base_url();?>admin/product/index/' + selectedId + '/' + $('#make_picker').children(":selected").attr("id") + '/edit' + lastParam;
            } else {
                window.location.href = '<?php echo base_url();?>admin/product/index/' + selectedId + '/' + $('#make_picker').children(":selected").attr("id");
            }
        });

        $('#make_picker').on('change', function() {
            var selectedId = $(this).children(":selected").attr("id");
            var currentUrl = document.URL;
            if(currentUrl.includes("add")) {
                window.location.href = '<?php echo base_url();?>admin/product/index/' + $('#category_picker').children(":selected").attr("id") + '/' + selectedId + '/add';
            } else if(currentUrl.includes("edit")) {
                var lastParam = currentUrl.split("/edit")[1];
                window.location.href = '<?php echo base_url();?>admin/product/index/' + $('#category_picker').children(":selected").attr("id") + '/' + selectedId + '/edit' + lastParam;
            } else {
                window.location.href = '<?php echo base_url();?>admin/product/index/' + $('#category_picker').children(":selected").attr("id") + '/' + selectedId;
            }
        });

        $('#txtFileImport').change(function() {
            $('form').submit();
        });
    });

    function onClickedImportButton() {
        $("#txtFileImport").click();
    }

    function onClickedBlockUser() {
        <?php if(!empty($user_info)): ?>
        var r = confirm("Are you sure to " + "<?php echo $user_info->active==1?'block':'unblock'; ?>" + " this user?");
        if (r == true) {
            $.post( "<?php echo base_url();?>api/user/block_user/<?php echo $user_info->id;?>", function( data ) {
                window.location.href = '<?php echo base_url();?>admin/user';
            });
        }
        <?php endif; ?>
    }

</script>
