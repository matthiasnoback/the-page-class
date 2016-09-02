<?php

require __DIR__ . '/bootstrap.php';

$page = new Page($_SERVER['REQUEST_URI']);

$page->show_page();
