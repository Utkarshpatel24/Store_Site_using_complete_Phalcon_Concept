<?php

use Phalcon\Mvc\Controller;


class ShopController extends Controller
{
    /**
     * Function for Shop
     *
     * @return void
     */
    public function indexAction()
    {
        $products = Products::find();
        $disp = "";
        foreach ($products as $key => $product) {
            $disp .= ' <div class="col">
                    <div class="card shadow-sm">
                    <svg class="bd-placeholder-img card-img-top" width="100%" height="225" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Thumbnail" preserveAspectRatio="xMidYMid slice" focusable="false"><title>Placeholder</title><rect width="100%" height="100%" fill="#55595c"/><text x="50%" y="50%" fill="#eceeef" dy=".3em">Thumbnail</text></svg>
        
                    <div class="card-body">
                        <h5>'.$product->product_name.'</h5>
                        <p class="card-text">Category : '.$product->product_category.'</p>
                        <div class="d-flex justify-content-between align-items-center">
                        <p><strong>$'.$product->product_price.'</strong>&nbsp;<del><small class="link-danger">$180</small></del></p>
                        <button class="btn btn-primary">Add To Cart</button>
                        </div>
                    </div>
                    </div>
                </div>
                ';
        }

        $this->view->productDisplay = $disp;
        
        
    }
}