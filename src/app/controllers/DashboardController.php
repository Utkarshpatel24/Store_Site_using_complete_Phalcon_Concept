<?php

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class DashboardController extends Controller{

    /**
     * Function to display Dashboard
     *
     * @param array $data
     * @return void
     */
    public function indexAction($data = [])
    {
        if (count($this->session->mydetails) == 0 ) {
            $this->response->redirect('/user/login');
        }



    }
    /**
     * Function to display customer list
     *
     * @return void
     */
    public function customerlistAction()
    {
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $paginator   = new PaginatorModel(
            [
                'model'  => Users::class,
                'limit' => 3,
                'page'  => $currentPage,
            ]
        );
        
        $page = $paginator->paginate();
        // print_r($page->getItems());
        // die();
        $disp ="";
        foreach ($page->getItems() as $key => $val) {
            if ($val['role'] != 'admin') {
                $action = 'approve';
                $act = 'app';
                if ($val['status'] == 'approved') {
                    $act = 'dis';
                    $action = 'dissapprove';
                }
                $disp.='<tr class="inner-box">
                        <th scope="row">
                            <div class="event-date">
                                <span>'.$val['user_id'].'</span>
                                
                            </div> 
                        </th>
                        <td>
                            <div class="event-img">
                                <img src="https://bootdey.com/img/Content/avatar/avatar2.png" width="150px" height="150px" alt="" />
                            </div>
                        </td>
                        
                        <td>
                            <div class="r-no">
                                <span>'.$val['name'].'</span><br>
                                <span>'.$val['email'].'</span><br>
                                <span>'.$val['role'].'</span>
                            </div>
                        </td>
                        <td>
                            <div class="primary-btn">
                                <a class="btn btn-primary" href="/user/changestatus?action='.$act.'-'.$val['user_id'].'">'.$action.'</a>
                            </div>
                        </td>
                        <td>
                            <div class="primary-btn">
                            <a class="btn btn-primary" href="/user/changestatus?action=del-'.$val['user_id'].'">Delete</a>
                            </div>
                        </td>
                    </tr>';    
            }
            
        }



        $this->view->display = $disp;
        $this->view->page = $page;

        $this->view->setVar('page', $page);



    }
    /**
     * Function for pagenation
     *
     * @return void
     */
    public function listAction()
    {
        $postdata=$this->request->getPost();

        
         $currentPage = $this->request->getQuery('page', 'int', 1);
        // $currentPage=1;
        $paginator   = new PaginatorModel(
            [
                'model'  => Blogs::class,
                "parameters" => [
                    "category LIKE '%".$postdata['searchby']."%'",
                     "order" => "'".$postdata['orderby']."DESC'",
                    
                    ],
                'limit' => 2,
                'page'  => $currentPage,
            ]
        );
        
        $page = $paginator->paginate();
        echo "Pagenation";
        $disp='';
        foreach ($page->getItems() as $key => $val) {
            $disp.=' <div class="col">
            <div class="card shadow-sm">
              
              <img  src="https://nscdn.nstec.com/does-suddenlink-routers-allow-use-of-vpn-.jpg" width="100%" height="225">
              <div class="card-body">
                  <h5>'.$val['title'].'</h5>
                <p class="card-text">'.$val['category'].'</p>
                <div class="d-flex justify-content-between align-items-center">
                  
                  <button class="btn btn-primary" name="b_id" value="'.$val['blog_id'].'">Read Blog</button>
                </div>
              </div>
            </div>
          </div>';
        }
        $data=array();
        array_push($data, $disp);
        array_push($data, $page);
        $this->view->data=$data;

    }

    /**
     * Function to place order
     *
     * @return void
     */
    public function placeOrderAction()
    {

        $products = Products :: find();
        $disp = "";
        foreach ($products as $key => $val) {
            $disp .='<option value="'.$val->product_id.'">'.$val->product_name.'</option>';
        }
        $this->view->options = $disp;

        $postdata = $this->request->getpost();
        if (count($postdata) != 0) {
            print_r($postdata);
            // die;
            $order = new Orders();
            $order->assign(
                $this->request->getPost(),
                [
                    'name',
                    'address',
                    'zip_code',
                    'product_id'
                ]
            );
            
            $order->save();
            $this->response->redirect('/dashboard/orderList/?bearer='.$_GET['bearer']);
            
        }
    }
    /**
     * Function to display list of order
     *
     * @return void
     */
    public function orderListAction()
    {
        $order = Orders :: find();
        $disp ='';
        foreach ($order as $key => $val) {
            $disp .='<tr>
            <td>'.$val->id.'</td>
            <td>'.$val->product_id.'</td>
            <td>'.$val->name.'</td>
            <td>'.$val->address.'</td>
            <td>'.$val->zip_code.'</td>
            <td><a href="/dashboard/orderDelete/'.$val->id.'?bearer='.$_GET['bearer'].'">Delete</a></td>
          </tr>';
        }
        $this->view->orderlist = $disp;
    }
    /**
     * Function to delete order
     *
     * @param [type] $id
     * @return void
     */
    public function orderDeleteAction($id)
    {
        echo $id;
        $order = Orders :: findFirst($id);
        $order->delete();
        $this->response->redirect("/dashboard/orderList/?bearer=".$_GET['bearer']);
    }

}           