<?php
/**
 * @var $contentView
 * @var $params
 */
?>
<html>
<head>
    <title>Multitext</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
<?php include $contentView; ?>
</div>

<?php
    $defaultScripts = $params['scripts']['default'] ? : [];
    $contentScripts = $params['scripts'][$contentView] ? : [];
    foreach (array_merge($defaultScripts, $contentScripts) as $scriptName) { ?>
        <script language="javascript" type="text/javascript" src="<?= $scriptName ?>"></script>
<?php } ?>
</body>