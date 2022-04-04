<?php

use Phalcon\Mvc\Model;

class Orders extends Model
{
    public $id;
    public $product_id;
    public $name;
    public $address;
    public $zipcode;

}