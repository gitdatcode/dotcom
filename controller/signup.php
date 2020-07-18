<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/handler.php';


/**
 * Signup page controller
 */
class Signup extends BaseHandler {
    private $_required = ['first_name', 'last_name', 'email', 'social', 'hear', 'about'];

    public function prepare(){
        $this->ensureSettingOr500('slack_signup_webhook');
    }

    public function get(){
        $content = $this->load('sign-up.html');

        return $this->page($content);
    }

    public function post(){
        if(!$this->isAjax()){
            $this->redirect('/sign-up');
        }

        $errors = [];
        $slack = [];
        $message = 'Thank you for signing up! We will get back to you shortly.';

        foreach($this->_required as $field){
            $pass = false;

            if(isset($_POST[$field])){
                $val = strip_tags(trim($_POST[$field]));
                $pass = $val != '';

                if($field == 'about' && $pass){
                    $pass = strlen($val) < 500;
                }
            }

            if(!$pass){
                $errors[] = $field;
            }else{
                $slack[$field] = $val;
            }
        }

        if(!count($errors)){
            $this->postToSlack($slack);
        }else{
            $message = 'Please correct the form errors to continue your submission.';
        }

        $resp = [
            'errors' => $errors,
            'message' => $message,
        ];

        return $this->json($resp);
    }
}
