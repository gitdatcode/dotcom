<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/handler.php';

/**
 * default 404 controller
 */
class FourOhFourHandler extends BaseHandler {
    protected $_messages = [
        "what chu doin'?",
        "you doing wayyy too much rn",
        "😬",
        "👀",
    ];
    protected $_template = '404.html';
    protected $_status_code = 404;

    public function get(){
        $i = array_rand($this->_messages);
        $message = $this->_messages[$i];
        $four = $this->load($this->_template, ['message' => $message]);

        return $this->page($four, $this->_status_code, [], $this->_status_code);
    }
}

class FiveOhOhHandler extends FourOhFourHandler {
    protected $_messages = [
        'internal server error',
        'yo, what did you do?',
    ];
    protected $_template = '500.html';
    protected $_status_code = 500;

    private function exceptionToSlack(){
        ob_start();
        var_dump($this->exception);
        $exc = ob_get_clean();
        $headers = [
            'Content-type' => 'application/json',
        ];
        $post = [
            'text' => '500',
            'attachments' => [
                [
                    'pretext' => 'there was a 500 on the DATCODE site',
                    'fields' => [
                        [
                            'title' => 'Exception trace',
                            'value' => $exc,
                            'short' => false
                        ],
                    ]
                ]
            ]
        ];
        $url = sprintf('https://hooks.slack.com/services/%s', $this->getConfig('slack_admin_webhook'));
        $post = json_encode($post);

        $this->callAPI('POST', $url, $post, $headers);
    }

    public function get(){
        $this->exceptionToSlack();
        return parent::get();
    }
}
