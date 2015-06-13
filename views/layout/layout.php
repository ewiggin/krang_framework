<!DOCTYPE html>
<html lang="fr">
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

    <title>Krang Framework</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Raleway:500,800' rel='stylesheet' type='text/css'>
    <link href="assets/css/app.css" rel="stylesheet">
   
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="<?=$body_class?>">

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
    <!-- /header -->

    <!-- content -->
    <div class="container">
        <?php
        // include del fitxer on hi ha el contingut
        include $view_file;
        ?>
    </div>
    <!-- /content -->
    <!-- Footer -->
    <script src="assets/js/jquery.1.11.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- /footer -->        
    
</body>
</html>
