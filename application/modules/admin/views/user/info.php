<?php echo $form->messages(); ?>

<div class="row">
	<?php echo $form->open(); ?>

	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-header">
				<h3 class="box-title">User Info</h3>
			</div>
			<div class="box-body">
				<div class="col-md-4">
					<?php echo $form->bs3_text('First Name', 'first_name'); ?>
					<?php echo $form->bs3_text('Last Name', 'last_name'); ?>
					<?php echo $form->bs3_text('Username', 'username'); ?>
					<?php echo $form->bs3_text('Email', 'email'); ?>
					<?php echo $form->bs3_text('Phone', 'phone'); ?>

					<div class="form-group">
						<label>Gender</label></br>
						<label>
							<input type="radio" name="gender" class="minimal" checked value="0">&nbsp; Male&nbsp; </input>
						</label>
						<label>
							<input type="radio" name="gender" class="minimal" value="1">&nbsp; Female&nbsp; </input>
						</label>
					</div>

					<?php echo $form->bs3_upload('User Image', 'user_image'); ?>

					<?php echo $form->bs3_password('Password', 'password'); ?>
					<?php echo $form->bs3_password('Retype Password', 'retype_password'); ?>

					<?php if ( !empty($groups) ): ?>
					<div class="form-group">
						<label for="groups">Groups</label>
						<div>
						<?php foreach ($groups as $group): ?>
							<label class="checkbox-inline">
								<input type="checkbox" name="groups[]" value="<?php echo $group->id; ?>"> <?php echo $group->name; ?>
							</label>
						<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<div class="col-md-8">
					<div class="col-md-12"><label>Ad Regions and Radius</label></div>

					<div id="ad_region_add_area">
						<!-- One Ad area start -->
						<div class="col-md-10">
							<!-- place picker -->
							<div class="form-group">
								<div class="form-group">
									<input id="advanced-placepicker" class="form-control"
										   data-map-container-id="collapseTwo"
										   name="ad_region" id="ad_region" style="width: 100%;"/>

									<input type="hidden" id="latitude" name="latitude" value=""/>
									<input type="hidden" id="longitude" name="longitude" value=""/>
								</div>

								<div id="collapseTwo" class="collapse">
									<div class="another-map-class thumbnail"></div>
								</div>
							</div>
						</div>

						<div class="col-md-2">
							<button class="btn btn-block btn-primary" type="button" id="add_btn">Add</button>
						</div>
						<!-- End -->
					</div>

					<div class="form-group col-md-12">
						<input type="hidden" id="address_info" name="address_info" value="" style="width: 100%"/>
						<ul id="ad_region_container">
							<!-- Ad region text list -->
						</ul>
					</div>
				</div>
			</div>
		</div>

		<?php echo $form->bs3_submit(); ?>
	</div>

	<?php echo $form->close(); ?>
</div>

<!-- iCheck 1.0.1 -->
<script src="<?php echo base_url() ?>assets/dist/iCheck/icheck.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url() ?>assets/dist/iCheck/all.css">

<!-- jquery placepicker -->
<script src="<?php echo base_url() ?>assets/dist/jquery.placepicker.js"></script>

<script type="text/javascript"
		src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA3Lf_RKrQdUAiIVZ7422NDKZcL0Tdf8ZM&sensor=true&libraries=places">
</script>

<style>
	.another-map-class {
		width: 100%;
		height: 300px;
	}
</style>

<script>
	$(function () {
		var addresses = [];
		var lats = [];
		var lngs = [];
		var radiuses = [];
		var curAddress = '';
		var curLat = '';
		var curLng = '';

		var adInfos = [];

		// place picker
		// Advanced usage
		$("#advanced-placepicker").each(function () {
			var $collapse = $(this).parents('.form-group').next('.collapse');
			var $map = $collapse.find('.another-map-class');

			//initMap();

			var placepicker = $(this).placepicker({
				map: $map.get(0),
				placeChanged: function (place) {
					console.log("place changed: ", place.formatted_address, this.getLocation());
					$('#latitude').val(this.getLocation().latitude);
					$('#longitude').val(this.getLocation().longitude);

					curAddress = place.name;
					curLat = this.getLocation().latitude;
					curLng = this.getLocation().longitude;
				}
			}).data('placepicker');
		});

		var count = 0;
		$('#add_btn').on('click', function () {
			var address = $('#advanced-placepicker').val();
			if (address) {
				count++;
				var html = "<li id='ad_region_label" + count + "'>" +
					"<div style='height: 5px'>" +
					"<div class='pull-right btn-box-tool del_right_div' id='del" + count + "'>" +
					"<i class='fa fa-times'></i>" +
					"</div></div>" +
					"<p>" + address + "</p>" +
					"</li>";

				$('#ad_region_container').append(html);

				addresses.push(curAddress);
				lats.push(curLat);
				lngs.push(curLng);

				var adinfo = {
					address: curAddress,
					lat: curLat,
					lng: curLng
				};

				adInfos.push(adinfo);
				if (adInfos.length > 0) {
					$('#address_info').val(JSON.stringify(adInfos));
				}

				$('#advanced-placepicker').val('');
				$('#radius').val('')
			}
		});

		//iCheck for checkbox and radio inputs
		$('input[type="radio"].minimal').iCheck({
			checkboxClass: 'icheckbox_minimal-blue',
			radioClass   : 'iradio_minimal-blue'
		});
	});
</script>
