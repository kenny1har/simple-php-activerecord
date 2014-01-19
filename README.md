Simple PHP Active Record
=============
PHP PDO active record. Simple, low memory and fast for those who wants high performance ORM / Active Record.

No configuration file. There is no association, aggregation support. Please use the get($pk) function for the reference object.
JOIN, GROUP BY, complex WHERE, please use the execute($sql) function.

query($sql) is almost the same as execute($sql), except query($sql) will map the result to the object while the execute($sql) will return PDOStatement object.

The returned object for where(), query(), execute() is PDOStatement, please refer to the PHP help page :
http://sg2.php.net/manual/en/class.pdostatement.php

Benchmark result :
```
| Library                          | Insert | findPk | complex| hydrate|  with  | memory usage |  time  |
| --------------------------------:| ------:| ------:| ------:| ------:| ------:| ------------:| ------:|
                             MyPDO |    228 |    139 |   1129 |    517 |    648 |      756,736 |   2.67 |
                               PDO |    135 |    132 |    945 |    418 |    463 |      777,768 |   2.10 |
                          Propel16 |    392 |    182 |   1407 |    904 |   1001 |   19,136,512 |   3.91 |
                 Propel16WithCache |    220 |    121 |   1311 |    797 |    840 |   19,136,512 |   3.30 |
                          Propel17 |    312 |    181 |   1371 |    931 |   1001 |   18,874,368 |   3.81 |
                 Propel17WithCache |    218 |    121 |   1179 |    750 |    738 |   18,874,368 |   3.02 |
                          Propel20 |    356 |    209 |   1485 |   1052 |   1103 |   23,592,960 |   4.22 |
                 Propel20WithCache |    239 |    135 |   1349 |    910 |    920 |   18,612,224 |   3.56 |
            Propel20FormatOnDemand |    238 |    135 |   1362 |    865 |    927 |    6,815,744 |   3.54 |
                        Doctrine24 |    566 |    479 |   2369 |   2137 |   2390 |   20,185,088 |   8.00 |
               Doctrine24WithCache |    559 |    477 |   1278 |   1530 |   1251 |   20,447,232 |   5.15 |
            Doctrine24ArrayHydrate |    582 |    481 |   1292 |    872 |    911 |   17,301,504 |   4.20 |
           Doctrine24ScalarHydrate |    563 |    479 |   1292 |    746 |    805 |   17,301,504 |   3.94 |
          Doctrine24WithoutProxies |    563 |    478 |   1277 |   1187 |   1540 |   18,874,368 |   5.10 |
```


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

Benchmark script :
for full environment, refer to https://github.com/vlastv/php-orm-benchmark
```
<?php

require_once dirname(__FILE__) . '/../AbstractTestSuite.php';
require_once __DIR__.'/../../SimpleMapper.php';
/**
 * This test suite just demonstrates the baseline performance without any kind of ORM
 * or even any other kind of slightest abstraction.
 */
class MyBook extends SimpleMapper\SimpleMapper {
    public static $table = 'book';
    public $id;
    public $title;
    public $isbn;
    public $price;
    public $author_id;
}
MyBook::initialize();

class MyAuthor extends SimpleMapper\SimpleMapper {
    public static $table = 'author';
    public $id;
    public $first_name;
    public $last_name;
    public $email;
}
MyAuthor::initialize();


class MyPDOTestSuite extends AbstractTestSuite
{
	function initialize()
	{
		$this->con = SimpleMapper\SimpleMapper::$pdo = new \PDO('sqlite::memory:');
		$this->initTables();
	}
	
	function clearCache()
	{
	}
	
	function beginTransaction()
	{
		$this->con->beginTransaction();
	}
	
	function commit()
	{
		$this->con->commit();
	}
	
	function runAuthorInsertion($i)
	{
		$author = new MyAuthor();
		$author->first_name = 'John'.$i;
		$author->last_name = 'Doe'.$i;
		$author->save();
		$this->authors[] = $author->id;
	}

	function runBookInsertion($i)
	{
		$book = new MyBook();
		$book->title = 'Hello'.$i;
		$book->isbn = '1234';
		$book->price = $i;
		$book->author_id = $this->authors[array_rand($this->authors)];
		$book->save();
		$this->books[] = $book->id;
	}
	
	function runPKSearch($i)
	{
		$author = MyAuthor::where('author.ID = :id LIMIT 1', array('id' => $this->authors[array_rand($this->authors)] ))->fetch();
	}
	
	function runHydrate($i)
	{
		$authors = MyBook::where('book.PRICE > :price LIMIT 5', array('price' => $i ));
		while ($row = $authors->fetch()) {

		}
	}

	function runComplexQuery($i)
	{
		$author = MyAuthor::execute('SELECT COUNT(*) FROM author WHERE (author.ID>:id OR (author.FIRST_NAME || author.LAST_NAME) = :name)', array('id'=>  $this->authors[array_rand($this->authors)] , 'name' => 'John Doe' ))->fetch();
	}
	
	function runJoinSearch($i)
	{
		$author = MyAuthor::execute('SELECT book.ID, book.TITLE, book.ISBN, book.PRICE, book.AUTHOR_ID, author.ID, author.FIRST_NAME, author.LAST_NAME, author.EMAIL FROM book LEFT JOIN author ON book.AUTHOR_ID = author.ID WHERE book.TITLE = :title LIMIT 1', array('title'=> 'Hello'.$i ))->fetch();
	}
}
```
