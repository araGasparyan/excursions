<?php
/**
 * Abstract model
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 aragasparyan.com
 */

namespace LinesC\Model;

abstract class AbstractModel
{
    /**
     * Date format used to store the date variables in this class
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Date format used to store the date variables in this class
     */
    const DATE_FORMAT_NO_TIME = 'Y-m-d';

    /**
     * Database connection
     *
     * @var \PDO
     */
    protected $database;

    /**
     * Cunstructor for the model
     *
     * @param \PDO $database
     */
    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }

    /**
     * Get the database object
     *
     * @return \PDO
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set the value database object
     *
     * @param \PDO $database
     *
     * @return self
     */
    public function setDatabase(\PDO $database)
    {
        $this->database = $database;
        
        return $this;
    }

    /**
     * Primary key name
     *
     * @var string
     */
    abstract protected function primaryKey();

    /**
     * Database table name for the model
     *
     * @var string
     */
    abstract protected function tableName();

    /**
     * Database fields
     *
     * @var array
     */
    abstract protected function tableFields();

    /**
     * Convert object to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        foreach ($this->tableFields() as $dbField => $classField) {
            $data[$dbField] = $this->$classField;
        }

        return $data;
    }

    /**
     * Populate object from associative array
     *
     * @param array $data Array from DB table
     *
     * @return void
     */
    public function hydrateFromArray($data)
    {
        foreach ($this->tableFields() as $dbField => $classField) {
            if (isset($data[$dbField])) {
                $this->$classField = $data[$dbField];
            }
        }
    }

    /**
     * Find record in table with given ID and load it's values into object
     *
     * @param integer|array $primaryKey ID
     *
     * @return boolean true if record was found, else false
     *
     * @throws \PDOException
     */
    public function find($primaryKey)
    {

        $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE ';
        $bind = [];

        if (is_array($primaryKey) && is_array($this->primaryKey())) {
            foreach ($primaryKey as $key => $value) {
                $sql .= $key . ' = ? AND ';
                $bind[] = $value;
            }

            $sql .= '2>1';
        } elseif (is_array($this->primaryKey())) {
            $sql .= $this->primaryKey()[0] . ' = ?';
            $bind[] = $primaryKey;
        } else {
            $sql .= $this->primaryKey() . ' = ?';
            $bind[] = $primaryKey;
        }

        $stmt = $this->database->prepare($sql);
        $stmt->execute($bind);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $this->hydrateFromArray($data);

            return true;
        }

        return false;
    }

    /**
     * Insert data into table
     *
     * @param string $table Table
     * @param array $bind Bind Params
     *
     * @return integer Last Insert ID
     */
    protected function _insert(string $table, array $bind)
    {
        $cols = [];
        $values = [];

        foreach ($bind as $col => $val) {
            $cols[] = $col;
            $values[] = '?';
        }

        // Build the statement
        $sql = 'INSERT INTO '
            . $table
            . ' (' . implode(', ', $cols) . ') '
            . 'VALUES (' . implode(', ', $values) . ')';

        // Execute the statement and return last inserted id
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_values($bind));

        return $this->database->lastInsertId();
    }

    /**
     * Insert object into DB
     *
     * @return integer Id of the inserted row
     */
    public function insert()
    {
        $datetime = new \DateTime();
        if (method_exists($this, 'setCreatedDate')) {
            $this->setCreatedDate($datetime);
        }
        if (method_exists($this, 'setUpdatedDate')) {
            $this->setUpdatedDate($datetime);
        }

        $bind = $this->toArray();
        if (!is_array($this->primaryKey())) {
            $primaryKey = $this->primaryKey();
            $primaryField = $this->tableFields()[$primaryKey];

            // Don't try to insert any value for the primary key
            unset($bind[$primaryKey]);
        }

        return $this->_insert($this->tableName(), $bind);
    }

    /**
     * Helper method to get single result as object
     *
     * @param string $field
     * @param string $value
     *
     * @return self|null
     */
    public static function findBy($field, $value)
    {
        /** @var \GetIt\Model\AbstractModel $className */
        /** @var \GetIt\Model\AbstractModel $model */
        $className = get_called_class();
        $model = new $className(getDbConnection());

        $resp = null;

        if ($result = $model->selectOne(array($field => $value))) {
            $model->hydrateFromArray($result);

            $resp = $model;
        }

        return $resp;
    }

    /**
     * Retrieve records from table or records that match the $whereClause provided
     *
     * @param array $whereClause = (column => value). Defaulted to empty array.
     * @param integer $limit
     * @param string|null $order by: id DESC
     *
     * @return array of database records
     */
    public function select($whereClause = [], $limit = 1, $order = null)
    {
        return $this->_select($whereClause, $limit, $order);
    }

    /**
     * Retrieve all records from table or records that match the $whereClause
     * provided
     *
     * @param array $whereClause = (column => value). Defaulted to empty array.
     * @param string|null $order by: id DESC
     *
     * @return array of database records
     */
    public function selectAll($whereClause = [], $order = null)
    {
        return $this->_select($whereClause, null, $order);
    }

    /**
     * Retrieve one record from table
     *
     * @param array $whereClause
     * @param string|null $order by: id DESC
     *
     * @return array containing a single database record
     */
    public function selectOne($whereClause = [], $order = null)
    {
        $row = $this->_select($whereClause, 1, $order);

        return (count($row)) ? $row[0] : [];
    }

    /**
     * Return all
     *
     * @todo Move finders to Repository
     *
     * @return array
     */
    public static function all()
    {
        /** @var \GetIt\Model\AbstractModel $className */
        /** @var \GetIt\Model\AbstractModel $model */
        $className = get_called_class();
        $model = new $className(getDbConnection());

        $resp = [];

        $results = $model->selectAll();
        foreach ($results as $result) {
            $model = new $className(getDbConnection());
            $model->hydrateFromArray($result);
            $resp[] = $model;
        }

        return $resp;
    }


    /**
     * Retrieve all records from table or records that match the $whereClause
     * and $limit provided
     *
     * @param array $whereClause = (column => value). Defaulted to empty array.
     * @param int $limit of contacts to retrieve. Defaulted to null.
     * @param string|null $order by: id DESC
     *
     * @return array of database records
     */
    protected function _select($whereClause = [], $limit = null, $order = null)
    {
        $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE ';
        $bind = [];

        foreach ($whereClause as $column => $value) {
            if (array_key_exists($column, $this->tableFields())) {
                if (is_null($value)) {
                    $sql .= $column . ' IS NULL AND ';
                } elseif ('not null' == $value) {
                    $sql .= $column . ' IS NOT NULL AND ';
                } else {
                    if (strpos($value, '%') === false) {
                        $sql .= $column . ' = ? AND ';
                    } else {
                        $sql .= $column . ' LIKE ? AND ';
                    }

                    $bind[] = $value;
                }
            }
        }

        $sql .= '2>1 ';

        if ($order) {
            $sql .= 'OEDER BY ' . $order;
        }

        if ($limit && is_numeric($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = $this->database->prepare($sql);
        $stmt->execute($bind);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Update object in DB
     *
     * Return true if row was modified
     *
     * @return boolean
     */
    public function update()
    {
        $datetime = new \DateTime();
        if (method_exists($this, 'setUpdatedDate')) {
            $this->setUpdatedDate($datetime);
        }

        $bind = $this->toArray();
        $primaryKey = $this->primaryKey();

        $resp = $this->_update($this->tableName(), $bind, $primaryKey);

        return $resp;
    }

    /**
     * Update table row with given id.
     * Assume that $bind['id'] is table primary key value
     *
     * @param string $table Table
     * @param array $bind Bind Params
     * @param string $key Key to reference
     *
     * @return bool
     */
    protected function _update($table, $bind, $key = 'id')
    {
        $keys = [];
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $keys[$v] = $bind[$v];
                unset($bind[$v]);
            }
        } else {
            $id = $bind[$key];
            unset($bind[$key]);
        }

        /**
         * Build "col = ?" pairs for the statement,
         * except for Zend_Db_Expr which is treated literally.
         */
        $set = array();
        foreach ($bind as $col => $val) {
            $set[] = $col . ' = ?';
        }

        if (count($keys)) {
            $where = implode(' AND ', array_map(function ($v) {
                return $v . ' = ?';
            }, array_keys($keys)));

            $bindValues = array_values($bind);
            $bindValues = array_merge($bindValues, array_values($keys));
        } else {
            $where = $key . ' = ?';
            $bindValues = array_values($bind);
            $bindValues[] = $id;
        }

        // Build the UPDATE statement
        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $set) . ' WHERE ' . $where;

        // Execute the statement and return the number of affected rows
        $stmt = $this->database->prepare($sql);
        $stmt->execute($bindValues);
        $stmt->rowCount();

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete row in DB with given ID
     *
     * @param string $table Table
     * @param integer $data ID
     * @param string $key ID Field
     *
     * @return boolean True, if we have deleted the row
     */
    protected function _delete($table, $data, $key = 'id')
    {

        if (is_array($data)) {
            $sql = 'DELETE FROM ' . $table . ' WHERE ' . $key;
            $where = $data;
        } else {
            $sql = 'DELETE FROM ' . $table . ' WHERE ' . $key . ' = ?';
            $where = array($data);
        }

        $stmt = $this->database->prepare($sql);
        $stmt->execute($where);

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete object from DB
     *
     * @return boolean Return true is row was deleted from DB
     */
    public function delete()
    {
        if (is_array($this->primaryKey())) {
            $primaryKey = implode(' AND ', array_map(function ($v) {
                return $v . ' = ?';
            }, array_values($this->primaryKey())));

            $data = array_map(function ($v) {
                $field = $this->tableFields()[$v];

                return $this->$field;
            }, $this->primaryKey());
        } else {
            $primaryKey = $this->primaryKey();
            $primaryField = $this->tableFields()[$this->primaryKey()];
            $data = $this->$primaryField;
        }

        $resp = $this->_delete($this->tableName(), $data, $primaryKey);

        return $resp;
    }
}
