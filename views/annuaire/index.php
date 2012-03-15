<?php if(!empty($msg)): ?>
<div class="formerror">
    <ul>
        <li class="error"><?php echo $msg ?></li>
    </ul>
</div>
<?php endif; ?>

<?php echo table_list('annuaire') ?>
