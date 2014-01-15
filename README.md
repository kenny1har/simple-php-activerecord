Simpe Mapper
=============
Simple mapper using PHP PDO. Simple, low memory and fast for those who wants high performance ORM.

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
	public function output() {
		echo 'Category: '.$this->id.' '.$this->name.' '.$this->price."<br />\n<br />\n";
	}
}
Category::initialize();

$product = new Product();
$product->name = 'test product';
$product->price = rand(0,1000);
$product->save();
$product->output();

$category = new Category();
$category->name = 'new category';
$category->price = rand(0,1000);
$category->save();
$category->output();

$testProduct = Product::get($product->id);
$testProduct->output();

echo "\n\n<br /><br />Multi rows<br />\n";
$testCategories = Category::where('id < :id_value LIMIT 0,5', array('id_value'=>$category->id));
while ($tempCategory = $testCategories->fetch()) {
	$tempCategory->output();
}
?>
```
