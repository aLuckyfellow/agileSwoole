<?php

namespace Component\Orm\Query;

use Component\Orm\Connection\AsynMysql as Connection;
use Kernel\AgileCore;

class AsynMysql extends Mysql implements IQuery
{
	const INSERT = 'INSERT';
	const SELECT = 'SELECT';
	const UPDATE = 'UPDATE';
	const DELETE = 'DELETE';

    /**
     * @var \Component\Orm\Connection\AsynMysql
     */
	protected $connection;
	public function __construct()
	{
		$this->connection = AgileCore::getInstance()->get('pool')->getConnection('mysql');
	}

	public function execute(): string
	{
		$query     = $this->__toString();
		$bind      = array_merge($this->_values, $this->_bind);
		$statement = $this->connection->prepare($query);
		$statement->execute($bind);

		$this->_reset();

		if($this->_type===static::INSERT) {
			return strval($this->connection->insert_id);
		} else {
			return strval($statement->affected_rows);
		}
	}

	public function fetchAll(bool $object = false) : array
	{
		$query     = $this->__toString();

        /**
         * @var \Swoole\Coroutine\MySQL\Statement
         */
		$statement = $this->connection->prepare($query);
        $result = $statement->execute($this->_bind);

		//$result = $statement->fetchAll($fetch);

		$this->_reset();

		if(!isset($result[0])) {
			return [];
		}
		return $result;
	}

}