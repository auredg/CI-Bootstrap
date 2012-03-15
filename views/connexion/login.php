<?php if(validation_errors() !== ''): ?>
<div class="formerror">
    <ul>
        <?php echo validation_errors('<li class="error">', '</li>') ?>
    </ul>
</div>
<?php endif; ?>

<?php echo carbone_form('connexion/login', 'form_connexion') ?>