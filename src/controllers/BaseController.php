<?php
namespace Multitext\Controller;

abstract class BaseController {
    public function handle($request, $actionName) {
        return $this->$actionName($request);
    }
}