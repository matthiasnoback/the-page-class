<?php
require __DIR__ . '/bootstrap.php';

try {
    $page = new Page($_SERVER['REQUEST_URI']);
    $page->show_page();
} catch (\Throwable $error) {
    echo $error->getMessage();
    throw $error;
}
