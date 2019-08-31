<?php
require_once __DIR__ .'/handler.php';
require_once __DIR__ .'/template.php';


function sortByLengthReverse($a, $b){
    return strlen($b) - strlen($a);
}


class FourOhFourException extends Exception{}


class Router {
    public $routes = array();
    public $template = null;
    public $four_oh_four = null;
    public $five_oh_oh = null;

    public function __construct(array $routes = array(),
                                Template $template = null,
                                Handler $four_oh_four = null,
                                Handler $five_oh_oh = null){
        uksort($routes, 'sortByLengthReverse');
        $this->routes = $routes;
        $this->template = $template;
        $this->four_oh_four = $four_oh_four ? $four_oh_four : new FourOhFour();
        $this->five_oh_oh = $five_oh_oh ? $five_oh_oh : new FiveOhOh();
    }

    public function run(){
        try{
            if(isset($_SERVER['PATH_INFO'])){
                $req = $_SERVER['PATH_INFO'];
            }else{
                $req = $_SERVER['REQUEST_URI'];
            }

            $method = strtolower($_SERVER['REQUEST_METHOD']);
            // echo '<pre>';
            // var_dump('>>>>>>>>>>>>', $_SERVER);
            // return;
            if($req == '/' or $req == ''){
                return $this->dispatch($method, $this->routes['/']);
            }

            foreach($this->routes as $pattern => $handler){
                $pattern = sprintf('|^%s$|', $pattern);

                preg_match($pattern, $req, $matches);

                if($matches && count($matches) > 0){
                    $args = array_slice($matches, 1);
                    return $this->dispatch($method, $handler, $args);
                }
            }

            throw new FourOhFourException();
        }catch(FourOhFourException $e){
            $this->four_oh_four->exception = $e;
            return $this->dispatch('get', $this->four_oh_four);
        }catch (Exception $e) {
            $this->five_oh_oh->exception = $e;
            return $this->dispatch('get', $this->five_oh_oh);
        }
    }

    public function dispatch($method, Handler $handler, array $args = array()){
        if(method_exists($handler, $method)){
            $handler->template = $this->template;

            return call_user_func_array(array($handler, $method), $args);
        }else{
            throw new FourOhFourException();
        }
    }
}