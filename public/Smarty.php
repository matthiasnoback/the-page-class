<?php

class Smarty
{
    public $caching;
    public $debugging;
    public $template_dir;
    public $compile_dir;
    public $use_sub_dirs;
    private $templateVariables = [];

    public function register_function($name, $callable)
    {
    }

    public function display($template, $cacheKey)
    {
        extract($this->templateVariables);

        include('templates/' . $template . '.php');
    }

    /**
     * @return bool
     */
    public function is_cached()
    {
    }

    public function clear_cache()
    {
    }

    public function assign($name, $value)
    {
        $this->templateVariables[$name] = $value;
    }

    public function trigger_error($message)
    {
        throw new \RuntimeException($message);
    }
}
