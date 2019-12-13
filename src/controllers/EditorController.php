<?php
namespace Multitext\Controller;
use Multitext\Request;

include_once 'BaseController.php';

class EditorController extends BaseController {
    public function index(Request $request) {
        $this->websocketUrl = 'wss://' . $request->httpHost . ':443/websocket/';
        return [ 'wrapper.php', 'editor/editor.php', (array)$this ];
    }
}