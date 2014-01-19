Simple PHP Active Record
=============
PHP PDO active record. Simple, low memory and fast for those who wants high performance ORM / Active Record.

JOIN, GROUP BY, complex WHERE use the execute() function.

The returned object for where(), query(), execute() is PDOStatement, please refer to the PHP help page :
http://sg2.php.net/manual/en/class.pdostatement.php

Usage :
```
<?php
include('SimpleMapper.php');
SimpleMapper\SimpleMapper::$pdo = new \PDO('mysql:host=localhost;dbname=admin_test', 'dbusername', 'dbpassword');

class Product extends SimpleMapper\SimpleMapper {
    public static $table = 'product';
    public static $pk = 'id'; /* optional row, default to 'id' */
    public $id;
    public $name;
    public $price;
    public function output() {
        echo 'Product: '.$this->id.' '.$this->name.' '.$this->price."<br />\n<br />\n";
    }
}
Product::initialize();

class Category extends SimpleMapper\SimpleMapper {
    public static $table = 'category';
    public static $pk = 'id'; /* optional row, default to 'id' */
    public $id;
    public $name;
    public $price;
    public $parent;
    public function output() {
        echo 'Category: '.$this->id.' '.$this->name.' '.$this->parent."<br />\n<br />\n";
    }
}
Category::initialize();

$product = new Product();
$product->name = 'test insert';
$product->price = rand(0,1000);
$product->save();

$product = new Product();
$product->name = 'test product';
$product->price = rand(0,1000);
$product->save();
$product->output();

$category = new Category();
$category->name = 'new category';
$category->price = rand(0,1000);
$category->parent = 'hello';
$category->price = rand(0,1000);
$category->save();
$category->output();

$testProduct = Product::get($product->id);
$testProduct->output();
$testProduct->delete();

echo "\n\n<br /><br />Multi rows<br />\n";
$testCategories = Category::where('id < :id_value LIMIT 0,5', array('id_value'=>$category->id));
while ($tempCategory = $testCategories->fetch()) {
    $tempCategory->output();
}
?>
```
