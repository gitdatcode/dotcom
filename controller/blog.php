<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/handler.php';



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
