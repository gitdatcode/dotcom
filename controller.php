<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/handler.php';


function p($content){
    echo '<pre>';
    var_dump($content);
}


/**
 * Adds some common functionality to all of the controllers
 */
class BaseHandler extends Handler{
    protected $_app_config = [];

    public function __construct(){
        // appConfig is a simple function that returns an array
        // it is defined in the config.php file
        $this->_app_config = appConfig();
        $this->prepare();
    }

    // method that is called when the handler is initialized
    public function prepare(){}

    public function page($content, $title = false, array $args = array(), $status_code=200){
        $args['content'] = $content;
        $args['title'] = $title;
        $content = $this->load('base.html', $args);

        return $this->write($content, 'text/html', $status_code);
    }

    public function getConfig($setting){
        if(isset($this->_app_config, $setting)){
            return $this->_app_config[$setting];
        }

        return null;
    }

    public function ensureSettingOr500($setting){
        if(!isset($this->_app_config[$setting]) || !$this->_app_config[$setting]){
            $msg = sprintf('%s does not exist in the $app_config', $setting);
            throw new Exception($msg);
        }
    }

    public function postToSlack(array $data = array()){
        $headers = [
            'Content-type' => 'application/json',
        ];
        $post = [
            'text' => 'New Member Request',
            'attachments' => [
                [
                    'pretext' => 'Someone new is looking to join DATCODE!',
                    'title' => $data['first_name'] .' '. $data['last_name'],
                    'fields' => [
                        [
                            'title' => 'Email Address',
                            'value' => $data['email'],
                            'short' => false
                        ],
                        [
                            'title' => 'Socail Media Name',
                            'value' => $data['social'],
                            'short' => false
                        ],
                        [
                            'title' => 'How They Know DATCODE',
                            'value' => $data['hear'],
                            'short' => false
                        ],
                        [
                            'title' => 'More about '. $data['first_name'],
                            'value' => $data['about'],
                            'short' => false
                        ],
                    ]
                ]
            ]
        ];
        $url = sprintf('https://hooks.slack.com/services/%s', $this->getConfig('slack_signup_webhook'));
        $post = json_encode($post);

        $this->callAPI('POST', $url, $post, $headers);
    }

    function callAPI($method, $url, $data = false, array $headers = []){
        $curl = curl_init();

        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data){
                    if(is_array($data)){
                        $data = http_build_query($data);
                    }

                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }

                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;

            default:
                if ($data){
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [
            'result' => $result,
            'status_code' => $http_status,
        ];
    }
}
