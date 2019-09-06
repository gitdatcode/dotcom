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
    }

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
        if(!isset($this->_app_config[$setting])){
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


/**
 * Homepage controller
 */
class Index extends BaseHandler {
    public function get(){
        $content = $this->load('index.html');

        return $this->page($content);
    }
}

class AboutController extends BaseHandler {
    public function get(){
        $content = $this->load('pages/about.html');

        return $this->page($content);
    }
}


/**
 * Signup page controller
 */
class Signup extends BaseHandler {
    private $_required = ['first_name', 'last_name', 'email', 'social', 'hear', 'about'];

    public function get(){
        $this->ensureSettingOr500('slack_signup_webhook');

        $content = $this->load('sign-up.html');

        return $this->page($content);
    }

    public function post(){
        $this->ensureSettingOr500('slack_signup_webhook');

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


/**
 * Resources page controller
 */
class Resources extends BaseHandler {
    public function getSearch(){
        if(isset($_GET['search']) && trim($_GET['search']) != ''){
            return strip_tags(trim($_GET['search']));
        }

        return '';
    }

    public function getPage(){
        if(isset($_GET['page']) && trim($_GET['page']) != ''){
            return strip_tags(trim($_GET['page']));
        }

        return 0;
    }

    public function searchResources($search = '', $page = 0) {
        $url = sprintf('%s/api/resource/search', $this->getConfig('resources_url'));
        $resp = $this->callAPI('get', $url, [
            'search' => $search,
            'page' => $page,
        ]);

        if($resp['status_code'] > 299){
            throw new Exception('there was an error on the resource server');
        }

        return json_decode($resp['result'], true);
    }

    public function renderSearchResults(array $resources = []) {
        return $this->render('resource/result_list.html', [
            'resources' => $resources,
        ]);
    }

    /**
     * the get method will take care of both loading the page and searching
     * for resources via an ajax call. If it is an ajax call, an array
     * containing the search results and a flag stating if there are more
     * results will be retunred. Otherwise, the whole page is rendered
     */
    public function get(){
        $this->ensureSettingOr500('resources_url');

        $search = $this->getSearch();
        $page = $this->getPage();
        $resources = $this->searchResources($search, $page);
        $results = $this->renderSearchResults($resources);

        // p($resources);
        if($this->isAjax()){
            return $this->json([
                'id' => 'result_set-' . $resources['data']['id'],
                'results' => $results,
                'all_tags' => $resources['data']['all_tags'],
            ]);
        }

        $page = $this->render('resource/page.html', [
            'page' => $page,
            'search' => $search,
            'content' => $results,
            'all_tags' => $resources['data']['all_tags'],
        ]);
        return $this->page($page);
    }
}


class ResourceView extends BaseHandler {
    public function get($resource_id){
        var_dump("viewing", $resource_id);
    }
}


/**
 * this class uses the json backup from the old blog to maintain a "blog" on
 * the site. This should be replaced
 */
class BlogController extends BaseHandler {
    public function get($slug=false){
        $json_file = file_get_contents(__DIR__ . '/model/blog_data.json');
        $json = json_decode($json_file, true);

        if($slug){
            foreach($json['data']['posts'] as $post){
                if(strtolower($post['slug']) == strtolower($slug)){
                    $content = $this->load('blog/post.html', [
                        'content' => $post['html'],
                    ]);

                    return $this->page($content, 'DATCODE | '.$post['title']);
                }
            }
        }

        $content = $this->load('blog/list.html', [
            'posts' => $json['data']['posts'],
        ]);

        return $this->page($content);
    }
}



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
