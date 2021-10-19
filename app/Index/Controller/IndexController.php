<?php


namespace App\Index\Controller;

use Library\Abstracts\Controller\AbstractController;

class IndexController extends AbstractController
{
    public function index()
    {
        return bananaSwoole(true,'web');
    }
}