<?php

use Phalcon\Mvc\Model;

class Products extends Model
{
    public $id;
    public $user_id;
    public $p_name;
    public $p_category;
    public $p_price;

}