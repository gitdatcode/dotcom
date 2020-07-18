<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/handler.php';


class StaticPage extends BaseHandler {
    protected $page = '404.html';

    public function get(){
        $content = $this->load($this->page);

        return $this->page($content);
    }
}

/**
 * Homepage controller
 */
class Index extends StaticPage {
    protected $page = 'index.html';
}

class AboutController extends StaticPage {
    protected $page = 'pages/about.html';
}

class CodeJamController extends StaticPage {
    protected $page = 'pages/code_jam.html';
}

class AILearningClubController extends StaticPage {
    protected $page = 'pages/ai_learning_club.html';
}
