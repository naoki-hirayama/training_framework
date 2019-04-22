<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php if (isset($title)) : echo $this->escape($title) . '-'; endif; ?>Mini Blog</title>
</head>
<div id="header">
    <h1>
        <a href="<?php echo $base_url ?>">Mini Blog</a>
    </h1>
</div>

<div>
    <?php echo $_content ?>
</div>

</html>