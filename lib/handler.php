<?php

class Handler {
    public $template = null;

    public function load($path, array $args = array()){
        return $this->template->load($path, $args);
    }

    public function render($path, array $args = array()){
        return $this->template->render($path, $args);
    }

    public function isAjax(){
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function write($content, $type = 'text/html', $status_code = 200){
        header('Content-type: '. $type);
        http_response_code($status_code);

        echo $content;
    }

    public function json(array $data = array(), $status_code = 200){
        return $this->write(json_encode($data), 'application/json', $status_code);
    }

    public function redirect($path){
        header('Location: '. $path);
        die();
    }
}


class FourOhFour extends Handler {
    public function get(){
        echo 'not found, fam. 404';
    }
}


class FiveOhOh extends Handler {
    public function get(){
        echo '500!';
    }
}
