<?php
namespace Multitext\Controller;
use Multitext\Request;

include_once 'BaseController.php';

class MainController extends BaseController {
    public function index(Request $request) {
        return [ 'wrapper.php', 'default.php', (array)$this ];
    }
}