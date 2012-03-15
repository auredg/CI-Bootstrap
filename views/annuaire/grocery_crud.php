<?php 
foreach($css_files as $file): ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
        

<?php foreach($js_files as $file): ?>
        <?php if(1): ?>
        <script src="<?php echo $file; ?>"></script>
        <?php else: ?>
	<p>Not loaded : <?php echo $file; ?></p>
        <?php endif; ?>
<?php endforeach; ?>


<?php echo $output ?>