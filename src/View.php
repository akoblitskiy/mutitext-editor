<?php
namespace Multitext;
class View {
    protected $params;
    public function __construct($params)
    {
        $this->params = $params;
    }

    public function generate($templateView, $contentView, $data, $params) {
        if (is_array($data)) {
            extract($data);
        }
        include __DIR__ . '/../view/'. $templateView;
    }
}