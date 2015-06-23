<!DOCTYPE html>
<html lang="<?=lang?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, minimal-ui">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

     <title>Krang Framework :: Home</title>

    <!-- Bootstrap core CSS -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/assets/components/bootstrap-material-design/dist/css/ripples.min.css" rel="stylesheet" />
    <link href="public/assets/components/bootstrap-material-design/dist/css/roboto.min.css" rel="stylesheet" />
    <link href="public/assets/components/bootstrap-material-design/dist/css/material-fullpalette.min.css" rel="stylesheet" />
    <link href="public/assets/css/app.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

    <!-- flash messages -->
    <?php if(!empty($flash_message)) : ?>
        <div class="flash_messages">
            <div class="alert alert-<?=$flash_type?>" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?=$flash_message?>
            </div>
        </div>
    <?php endif; ?>

    <!-- header code -->
    <div class="container-fluid main">
        <?php
        // include del fitxer on hi ha el contingut
        include $view_file;
        ?>
    </div>
    <!-- Footer -->
    <!-- End footer -->        
    
    

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="public/assets/components/jquery/dist/jquery.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/components/bootstrap-material-design/dist/js/material.min.js"></script>
    <script src="public/assets/components/bootstrap-material-design/dist/js/ripples.min.js"></script>
    <script type="text/javascript">
        $.material.init();
    </script>
</body>
</html>
