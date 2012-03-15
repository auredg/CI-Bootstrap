<?php echo doctype('html5') ?>
<html>

<head>
    
    <meta charset="<?php echo $charset ?>" />
    
    <title><?php echo $titre ?> :: <?php echo $soustitre ?></title>
    
    <meta name="description" content="<?php echo $description ?>" />
    <meta name="keywords" content="<?php echo $keywords ?>" />    
    
    <?php foreach($css as $_css): ?>
    <link rel="stylesheet" href="<?php echo $_css['href'] ?>" media="<?php echo $_css['media'] ?>" />
    <?php endforeach; ?>
    
</head>

<body>
    
    <div id="main">
    
        
        <div id="content">
            <div class="inner">
                <?php echo $output ?>
            </div>
        </div>
    
    </div>
        
    <?php foreach($js as $_js): ?>
    <script type="<?php echo $_js['type'] ?>" src="<?php echo $_js['src'] ?>"></script>
    <?php endforeach; ?>
    
</body>

</html>
