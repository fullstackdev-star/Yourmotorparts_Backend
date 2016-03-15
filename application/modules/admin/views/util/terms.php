<?php echo $form->messages(); ?>

<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-body">
				<?php echo $form->open(); ?>
					<?php echo $form->bs3_textarea('Terms of Service', 'terms', $constant['terms']); ?>
					<?php echo $form->bs3_submit(); ?>
				<?php echo $form->close(); ?>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="<?php echo base_url(); ?>assets/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="<?php echo base_url(); ?>assets/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>

<script>
	$(function () {
		//bootstrap WYSIHTML5 - text editor
		$('textarea').wysihtml5()
	})
</script>

<style>
	.wysihtml5-sandbox {
		height: 500px;
	}
</style>
