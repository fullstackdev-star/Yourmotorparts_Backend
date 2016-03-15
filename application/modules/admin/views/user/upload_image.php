<?php echo $form->messages(); ?>

<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-body">
                <?php echo $form->open(); ?>
                <?php echo $form->bs3_upload('Upload Image', 'upload_image'); ?>
                <?php echo $form->bs3_text('First Name', 'first_name'); ?>
                </br>
                <?php echo $form->bs3_submit(); ?>
                <?php echo $form->close(); ?>
            </div>
        </div>
    </div>
</div>