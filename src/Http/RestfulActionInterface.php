<?php

namespace Common\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RestfulActionInterface
{
    public function get();
    public function getList();
    public function post();
    public function put();
    public function patch();
    public function patchList();
    public function delete();
    public function deleteList();
    public function head();
    public function options();
}
