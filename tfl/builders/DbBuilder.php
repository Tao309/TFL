<?php

namespace tfl\builders;

use tfl\observers\DbObserver;

/**
 * Class DB
 *
 * @property \PDO pdo
 */
class DbBuilder
{
    use DbObserver;

	const CONCAT_EQUAL = '=';
	const CONCAT_NOT_EQUAL = '!=';
	const CONCAT_IN = 'IN';
	const CONCAT_NOT_IN = 'NOT IN';

    const TYPE_INSERT = 'insert';
    const TYPE_DELETE = 'delete';
    const TYPE_SAVE = 'save';
    const TYPE_ERROR = 'error';

    const VALUE_IF_NULL = '-------';

    const TYPE_SHOW_MODAL_WINDOW = 'modal_window';

    private $type;
    private $lastInsertId = 0;

    private $pdo;

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): DbBuilder
    {
        if (is_null($this->pdo)) {
            $config = require_once zROOT . 'config/sql.php';

            foreach ([
                         'DB_HOST',
                         'DB_USER',
                         'DB_PASSWORD',
                         'DB_NAME',
                     ] as $key) {
                if (!isset($config[$key]) || empty($config[$key])) {
                    die('Can not use all variables for connecting');
                }
            }

            $dsn = 'mysql:host=' . $config['DB_HOST'] . ';';
            $dsn .= 'dbname=' . $config['DB_NAME'] . ';';
            $dsn .= 'charset=' . $config['DB_CHARSET'];

            try {
                $pdo = new \PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD']);
            } catch (\PDOException $e) {
                die('Connect error: ' . $e->getMessage());
            }

            $this->pdo = $pdo;
        }

        return $this;
    }

    private function query($query = null): string
    {
        if ($this->getInit()) {
            return $this->getSqlRow();
        }

        return $query;
    }

    private function prepare($sql)
    {
        $sql = $this->query($sql);

        $prepare = $this->pdo->prepare($sql);

        if (!$prepare) {
            \tfl\utils\tDebug::printData($this->pdo->errorInfo());
        }

        return $prepare;
    }

    /*
     * @todo add
        $this->bindParam(1, $calories, PDO::PARAM_INT);
        $this->bindValue(2, $colour, PDO::PARAM_STR);
     */

    private function execute($sql)
    {
        $prepare = $this->prepare($sql);

        if (!$prepare->execute()) {
            ob_start();
            $prepare->debugDumpParams();
            $debug = ob_get_clean();

            $data = [
                'error' => $prepare->errorInfo()[2],
                'debug' => $debug,
            ];
            \tfl\utils\tDebug::printData($data);
        }

        if ($this->type = self::TYPE_INSERT) {
            $this->lastInsertId = $this->pdo->lastInsertId();
        }

        return $prepare;
    }

    public function findColumn($sql = null)
    {
        return $this->find($sql)[0];
    }

    public function find($sql = null)
    {
        $exec = $this->execute($sql);

        return $exec->fetch(\PDO::FETCH_ASSOC);
    }

    public function findAll($sql = null, $useAllCount = false)
    {
        $exec = $this->execute($sql);

        $rows = $exec->fetchAll(\PDO::FETCH_ASSOC);

        if ($useAllCount) {
            return [
                'rows' => $rows,
                'allCount' => $this->foundRows(),
            ];
        }

        return $rows;
    }

    protected function foundRows()
    {
        $query = 'SELECT FOUND_ROWS() AS count';
        return $this->find($query)['count'];
    }

    protected function updateRow($sql = null)
    {
	    $this->type = self::TYPE_SAVE;
        $exec = $this->execute($sql);

        return $this;
    }

    protected function deleteRow($sql = null)
    {
        $this->type = self::TYPE_DELETE;
        $exec = $this->execute($sql);

        return $this;
    }

    protected function insertRow($sql = null)
    {
        $this->type = self::TYPE_INSERT;
        $exec = $this->execute($sql);

        return $this;
    }

    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

}