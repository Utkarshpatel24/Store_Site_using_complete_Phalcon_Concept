<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    /**
     * Function to display shop
     *
     * @return void
     */
    public function indexAction()
    {

        $this->dispatcher->forward(
            [
                'controller' => 'shop',
                'action' => 'index'
            ]
        );
      
    }
}
