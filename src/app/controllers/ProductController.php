<?php

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class ProductController extends Controller
{
    /**
     * Function For product
     *
     * @return void
     */
    public function indexAction()
    {
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $paginator   = new PaginatorModel(
            [
                'model'  => Products::class,
                'limit' => 3,
                'page'  => $currentPage,
            ]
        );
        
        $page = $paginator->paginate();
        // print_r($page->getItems());
        // die();
        $disp ="";
        foreach ($page->getItems() as $key => $val) {
            $disp.="<tr>
            <td>".$val['product_id']."</td>
            <td>".$val['product_name']."</td>
            <td>".$val['product_category']."</td>
            <td>".$val['product_price']."</td>
            <td><a href='/product/productEdit/".$val['product_id']."?bearer=".$this->request->getQuery('bearer')."'>Edit</a>&nbsp;<a href='/product/productDelete/".$val['product_id']."?bearer=".$this->request->getQuery('bearer')."'>Delete</a></td>
            </tr>";

        }
        
        $this->view->display = $disp;
        $this->view->page = $page;
    }
    /**
     * Function to add Product
     *
     * @return void
     */
    public function addproductAction()
    {
        $postdata = $this->request->getPost();

        if (count($postdata)) {
                
            $product = new Products();
            $product->assign(
                $this->request->getPost(),
                [
                  'product_name',
                  'product_category',
                  'product_price'
                ]  
            );
            $product->user_id = $this->session->get('mydetails')['user_id'];
            $result = $product->save();
            
            $this->response->redirect('/product/index/?bearer='.$this->request->getQuery('bearer'));
        }
    }
    /**
     * Function to delete Product
     *
     * @param [type] $id
     * @return void
     */
    public function productDeleteAction($id)
    {
        echo $id;
        $product = Products::findFirst($id);
        $product->delete();
        $this->response->redirect('/product/index/?bearer='.$this->request->getQuery('bearer'));

    }
    /**
     * Function to editProduct
     *
     * @param [type] $id
     * @return void
     */
    public function productEditAction($id)
    {
        $postdata = $this->request->getPost();
        if (count($postdata) != 0) {
            $product = Products :: findFirst($id);
            $product->assign(
                $this->request->getPost(),
                [
                  'product_name',
                  'product_category',
                  'product_price'
                ]  
            );
            $product->user_id = $this->session->get('mydetails')['user_id'];
            $product->update();
            $this->response->redirect('/product/index/?bearer='.$this->request->getQuery('bearer'));
        }
    }
}