<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/handler.php';
require_once __DIR__ . '/signup.php';


/**
 * Contribute page controller
 */
class Contribute extends Signup {
    private $_required = ['name', 'username', 'email', 'help'];

    public function prepare(){
        $this->ensureSettingOr500('slack_board_webhook');
    }

    public function get(){
        $content = $this->load('contribute.html');

        return $this->page($content);
    }

    public function post(){
        if(!$this->isAjax()){
            $this->redirect('/contribute');
        }

        $errors = [];
        $slack = [];
        $message = 'Thank you for helping make DATCODE the best place for Black technolists! We will get back to you shortly.';

        foreach($this->_required as $field){
            $pass = false;

            if(isset($_POST[$field])){
                if($field == 'help') {
                    $pass = count($_POST[$field]) > 0;
                    $val = $_POST[$field];
                }else{
                    $val = strip_tags(trim($_POST[$field]));
                    $pass = $val != '';

                    if($field == 'notes' && $pass){
                        $pass = strlen($val) < 500;
                    }
                }
            }

            if(!$pass){
                $errors[] = $field;
            }else{
                $slack[$field] = $val;
            }
        }

        if(!count($errors)){
            $headers = [
                'Content-type' => 'application/json',
            ];
            $post = [
                'text' => 'A Member Wants To Contribute',
                'attachments' => [
                    [
                        'pretext' => '',
                        'fields' => [
                            [
                                'title' => 'Name',
                                'value' => $slack['name'],
                                'short' => false
                            ],
                            [
                                'title' => 'Username',
                                'value' => $slack['username'],
                                'short' => false
                            ],
                            [
                                'title' => 'Email Address',
                                'value' => $slack['email'],
                                'short' => false
                            ],
                            [
                                'title' => 'How they\'d like to help',
                                'value' => implode(", ", $slack['help']),
                                'short' => false
                            ],
                            [
                                'title' => 'Notes',
                                'value' => isset($slack['notes']) ? $slack['notes'] : 'none',
                                'short' => false
                            ],
                        ]
                    ]
                ]
            ];
            $url = sprintf('https://hooks.slack.com/services/%s', $this->getConfig('slack_board_webhook'));
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
