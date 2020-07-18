<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/handler.php';


class HypeReport extends BaseHandler {

    private $_titles = [
        'Submit your hype!',
        'Tell us something good',
    ];

    public function get(){
        $i = array_rand($this->_titles);
        $title = $this->_titles[$i];
        $args = [
            'current_month' => date('F', mktime(0, 0, 0, date('m'), 10)),
            'current_year' => date('Y'),
        ];
        $hype = $this->load('hype_report/form.html', $args);

        return $this->page($hype, $title);
    }
}