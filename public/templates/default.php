<html>
<head><title><?php echo $site_title; ?></title></head>
<body>
<?php if (count($main_navigation) > 0): ?>
    <ul class="main_navigation">
        <?php foreach ($main_navigation as $item): ?>
            <li class="item">
                <a href="<?php echo $item['href']; ?>"><?php echo $item['menu_name']; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<h1 class="page_title"><?php echo $page_title; ?></h1>
<p class="contents"><?php echo $contents; ?></p>
<h2>Available template variables:</h2>
<?php dump($this->templateVariables); ?>
</body>
</html>
