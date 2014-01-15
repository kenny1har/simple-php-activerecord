<?php
namespace SimpleMapper;
class SimpleMapper {
	public static $pk = 'id';
	public static $columns = '*';
	public static $pdo;
	public static $params;

	public static function initialize() {
		if (!isset(static::$table))
			static::$table = substr(get_called_class(), strrpos(get_called_class(), '\\')+1);
		static::$params = array();
		foreach (get_class_vars(get_called_class()) as $key => $value)
			if (!in_array($key, array('pdo', 'params', 'table', 'columns', 'pk')))
				static::$params[$key] = $value;
	}
	public static function get($id) {
		return static::query('SELECT '.static::$columns.' FROM '.static::$table.' WHERE '.static::$pk.' = :id', array('id'=>$id))->fetch();
	}
	public static function where($where, $params) {
		return static::query('SELECT '.static::$columns.' FROM '.static::$table.' WHERE '.$where, $params);
	}
	public static function query($sql, $params) {
		$query = self::$pdo->prepare($sql);
		$query->setFetchMode(\PDO::FETCH_CLASS, get_called_class());
		$query->execute($params);
		return $query;
	}
	public static function execute($sql, $params) {
		$query = self::$pdo->prepare($sql);
		$query->execute($params);
		return $query;
	}
	public function save() {
		$tempParams = static::$params;
		if (!isset($this->{static::$pk}))
			unset($tempParams[static::$pk]);
		$sets = '';
		$paramsTemp = array();
		foreach ($tempParams as $key => $value) {
			$paramsTemp[$key.'_value'] = $this->$key;
			$sets .= $key.' = :'.$key.'_value, ';
		}
		$sets = substr($sets, 0, -2);
		if (isset($this->{static::$pk})) {
			$sql = "UPDATE ".static::$table." SET $sets WHERE $pk = :".static::$pk."_value";
			static::execute($sql, $paramsTemp);
		} else {
			$fields = implode(', ', array_keys($tempParams));
			$values = ':'.implode('_value, :', array_keys($tempParams)).'_value';
			$sql = "INSERT INTO ".static::$table." ($fields) VALUES ($values)";
			static::execute($sql, $paramsTemp);
			$this->{static::$pk} = self::$pdo->lastInsertId();
		}
	}
	public function delete() {
		return static::execute('DELETE FROM '.static::$table.' WHERE '.static::$pk.' = :id', array('id'=>$this->{static::$pk}));
	}
}
?>
