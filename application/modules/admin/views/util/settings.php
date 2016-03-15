<?php echo $form->messages(); ?>

<div class="row">

	<div class="col-md-6">
		<div class="box box-primary">
			<div class="box-body">
				<?php echo $form->open(); ?>
					<?php echo $form->bs3_text('Contact Phone Number', 'contact_phone', $constant['contact_phone']); ?>
					<?php echo $form->bs3_text('Contact Email', 'contact_email', $constant['contact_email']); ?>
					<?php echo $form->bs3_text('Default Pending Duration (days)', 'pending_time', $constant['pending_time']); ?>

					<?php echo $form->bs3_submit(); ?>
					
				<?php echo $form->close(); ?>
			</div>
		</div>
	</div>
	
</div>