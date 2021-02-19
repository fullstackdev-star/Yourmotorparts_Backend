<div class="row">

	<div class="col-md-3">
		<?php echo modules::run('adminlte/widget/box_open', 'Shortcuts'); ?>
			<?php echo modules::run('adminlte/widget/app_btn', 'fa fa-user', 'Account', 'panel/account'); ?>
			<?php echo modules::run('adminlte/widget/app_btn', 'fa fa-sign-out', 'Logout', 'panel/logout'); ?>
		<?php echo modules::run('adminlte/widget/box_close'); ?>
	</div>

	<div class="col-md-3">
		<?php echo modules::run('adminlte/widget/info_box', 'green', $count['users'], 'All Users', 'fa fa-users', 'user'); ?>
	</div>

	<div class="col-md-3">
		<?php echo modules::run('adminlte/widget/info_box', 'red', $count['sellers'], 'Sellers', 'fa fa-users', 'user/sellers'); ?>
	</div>

	<div class="col-md-3">
		<?php echo modules::run('adminlte/widget/info_box', 'blue', $count['buyers'], 'Buyers', 'fa fa-users', 'user/buyers'); ?>
	</div>

	<div class="col-md-12">
		<!-- LINE CHART -->
		<div class="box box-info">
			<div class="box-header with-border">
				<h3 class="box-title">Login Status</h3>

				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
					</button>
					<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<div class="box-body chart-responsive">
				<div class="chart" id="line-chart" style="height: 300px;"></div>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->

		<!-- BAR CHART -->
		<div class="box box-success" hidden>
			<div class="box-header with-border">
				<h3 class="box-title">Bar Chart</h3>

				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
					</button>
					<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<div class="box-body chart-responsive">
				<div class="chart" id="bar-chart" style="height: 300px;"></div>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	</div>
</div>

<link rel="stylesheet" href="<?php echo base_url();?>assets/dist/morris.js/morris.css">

<script src="<?php echo base_url();?>assets/dist/raphael/raphael.min.js"></script>
<script src="<?php echo base_url();?>assets/dist/morris.js/morris.min.js"></script>

<script>
	$(function () {

		// LINE CHART
		var line = new Morris.Line({
			element: 'line-chart',
			resize: true,
			data: [
				{y: '2011 Q1', item1: 2666, item2: 10666},
				{y: '2011 Q2', item1: 2778, item2: 20378},
				{y: '2011 Q3', item1: 4912, item2: 13912},
				{y: '2011 Q4', item1: 3767, item2: 13367},
				{y: '2012 Q1', item1: 6810, item2: 15810},
				{y: '2012 Q2', item1: 5670, item2: 15630},
				{y: '2012 Q3', item1: 4820, item2: 14120},
				{y: '2012 Q4', item1: 15073, item2: 13073},
				{y: '2013 Q1', item1: 10687, item2: 10087},
				{y: '2013 Q2', item1: 8432, item2: 15432},
			],
			xkey: 'y',
			ykeys: ['item1', 'item2'],
			labels: ['Sellers', 'Buyers'],
			lineColors: ['#00a0ee', '#f56954'],
			hideHover: 'auto'
		});

		//BAR CHART
		var bar = new Morris.Bar({
			element: 'bar-chart',
			resize: true,
			data: [
				{y: '2006', a: 100, b: 90},
				{y: '2007', a: 75, b: 65},
				{y: '2008', a: 50, b: 40},
				{y: '2009', a: 75, b: 65},
				{y: '2010', a: 50, b: 40},
				{y: '2011', a: 75, b: 65},
				{y: '2012', a: 100, b: 90}
			],
			barColors: ['#00a65a', '#f56954'],
			xkey: 'y',
			ykeys: ['a', 'b'],
			labels: ['CPU', 'DISK'],
			hideHover: 'auto'
		});
	});
</script>