<?php

class Template {
    public $path;

    public function __construct($template_path){
        $this->path = $template_path;
    }

    /**
     * Load a template from within a template
     */
    public function render($path, array $args = array()){
        $temp = new Template($this->path);

        return $temp->load($path, $args);
    }

    /**
     * load a template from another class
     */
    public function load($path, array $arguments = array()){
        $this->arguments = $arguments;

        ob_start();
        $file = sprintf('%s/%s', $this->path, $path);
        require $file;

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function get($arg, $default = null){
        if(array_key_exists($arg, $this->arguments)){
            return $this->arguments[$arg];
        }

        return $default;
    }
}