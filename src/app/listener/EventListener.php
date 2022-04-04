<?php

// namespace App\Listener;


use Phalcon\Di\Injectable;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;





class EventListener extends Injectable
{
    

    /**
     * Function to Handle ACL
     *
     * @param [type] $data
     * @return void
     */
    public function beforeHandleRequest($data)
    {
        //$data = $data->getData();
        
       
        $controller = $this->router->getControllerName();
        if($controller == null)
        $controller = '';
        $action = $this->router->getActionName();
        if($action == null)
        $action = '';
        $aclfile = APP_PATH. '/security/acl.cache';
        if (true != is_file($aclfile)) {
            $acl =new Memory();

          
            $acl->addRole('admin');
            $acl->addRole('manager');
            $acl->addRole('accountant');
           
            
            $acl->addComponent(
                'product',
                [
                    'index',
                    'addproduct'
                ]
            );
            $acl->addComponent(
                'dashboard',
                [
                    'index',
                    'orderList'
                ]
            );
            
                
            $acl->allow('admin', '*', '*');
            $acl->allow('manager', 'product', 'index');
            $acl->allow('manager', 'dashboard', 'index');
            $acl->allow('manager', 'product', 'addproduct');
            $acl->allow('accountant', 'dashboard', ['index', 'orderList']);

            file_put_contents(
                $aclfile,
                serialize($acl)
            );
        } else {
            $acl = unserialize(
                file_get_contents($aclfile)
            );
         
        }

        $bearer = $this->request->getQuery('bearer');
       
        if ($bearer) {
            try {
                $key = "example_key";
                $decoded = JWT::decode($bearer, new Key($key, 'HS256'));
               
                $role = $decoded->role;
                if (true === $acl->isAllowed($role, $controller, $action)) {
                    echo "Access Granted";
                } else {
                    echo "Access Denied";
                    //echo $this->locale->_("Access Denied");
                    die();
                }
            } catch(Exception $e) {
                echo $e->getMessage();
                die();
            }
        } 
        // else {
        //     echo "Token Not Passed";
        //     die();
        // }

    }

}