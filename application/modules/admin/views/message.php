<div class="row">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header">
                <i class="fa fa-envelope"></i>
                <h3 class="box-title">Inbox</h3>
            </div>

            <?php foreach($array as $key=>$value): ?>
            <div class="box-body chat" id="chat-box">
                <!-- chat item -->
                <div class="item">
                    <img src="<?php echo base_url()?>dist/img/user4-128x128.jpg" alt="user image" class="online">

                    <p class="message">
                        <a href="#" class="name">
                            <small class="text-muted pull-right"><i class="fa fa-clock-o"></i> 2:15</small>
                            Mike Doe
                        </a>
                        I would like to meet you to discuss the latest news about
                        the arrival of the new theme. They say it is going to be one the
                        best themes on the market
                    </p>
                    <!-- /.attachment -->
                </div>
                <!-- /.item -->
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
