<head>
    <?php echo $map['js']; ?>
</head>
<div style="position: relative; height: 100%">
<?php echo $map['html']; ?>
</div>
<script>
    $("#map_canvas").height($(window).height() - 200);
</script>