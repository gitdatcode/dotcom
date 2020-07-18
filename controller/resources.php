<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/handler.php';


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

    public function prepare(){
        $this->ensureSettingOr500('resources_url');
    }

    /**
     * the get method will take care of both loading the page and searching
     * for resources via an ajax call. If it is an ajax call, an array
     * containing the search results and a flag stating if there are more
     * results will be retunred. Otherwise, the whole page is rendered
     */
    public function get(){
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
