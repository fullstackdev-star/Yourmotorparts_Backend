<header class="main-header">
	<a href="" class="logo"><b><?php echo $site_name; ?></b></a>
	<nav class="navbar navbar-static-top" role="navigation">
		<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</a>
		<div class="navbar-custom-menu">
			<ul class="nav navbar-nav">
				<li class="dropdown messages-menu">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-envelope-o"></i>
						<span class="label label-success"><?php echo count($new_messages); ?></span>
					</a>
					<ul class="dropdown-menu">
						<li class="header">You have <?php echo count($new_messages); ?> new messages</li>
						<li>
							<!-- inner menu: contains the actual data -->
							<ul class="menu messages">
								<?php foreach($new_messages as $new_message): ?>
								<li><!-- start message -->
									<a href="#">
										<!--<div class="pull-left">
											<img src="<?php /*echo $new_message->user?$new_message->user->photo:base_url().'assets/images/avatar_default.png'; */?>" class="img-circle" alt="User Image">
										</div>-->
										<h4>
											<?php echo $new_message->username; ?>
											<small><?php echo $new_message->created_at; ?>&nbsp;&nbsp;<i class="fa fa-clock-o"></i></small>
										</h4>
										<p><?php echo $new_message->message; ?></p>
									</a>
								</li>
								<?php endforeach; ?>
							</ul>
						</li>
						<li class="footer"><a href="<?php echo base_url().'admin/message'; ?>">See All Messages</a></li>
					</ul>
				</li>

				<li class="dropdown user user-menu">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<span class="hidden-xs"><?php echo $user->first_name; ?></span>
					</a>
					<ul class="dropdown-menu">
						<li class="user-header">
							<p><?php echo $user->first_name; ?></p>
						</li>
						<li class="user-footer">
							<div class="pull-left">
								<a href="panel/account" class="btn btn-default btn-flat">Account</a>
							</div>
							<div class="pull-right">
								<a href="panel/logout" class="btn btn-default btn-flat">Sign out</a>
							</div>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</nav>
</header>