<?PHP
		define('DB_HOST', 'localhost');
		define('DB_NAME', 'exhibit1836_users');
		define('DB_SCMA', 'public');
		define('DB_USER', 'exhibit1836');
		define('DB_PASS', 'Tristan1902');
		define('DB_PORT', 5432);

    class Database {
        private $connection;

        function __construct($db_host, $db_name, $db_user, $db_pass, $db_port, $db_schema) {
          $this->connection = pg_connect("dbname={$db_name} user={$db_user} password={$db_pass} host={$db_host} port={$db_port}");
        }

    	function modify($str) {
        		return ucwords(str_replace("_", " ", $str));
    	}

				function getConn() {
					return $this->connection;
				}
        function getAll($table, $where = '', $orderby = '') {
            $orderby = $orderby ? 'ORDER BY '.$orderby : '';
            $where = $where ? 'WHERE '.$where : '';

            $query = "SELECT * FROM {$table} {$where} {$orderby}";
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch all rows
            $rows = array();
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row;
            }

            return $rows;
        }


        function get($table, $where) {
            if(is_numeric($where)) {
                $where = "id = ".intval($where);
            }
            else if (empty($where)) {
                $where = "1";
            }

            $query = "SELECT * FROM {$table} WHERE $where";
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch one rows
            $row = pg_fetch_assoc($result);

            return $row;
        }


        /* Select Query */
        function select($query) {
            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            // Fetch all rows
            $rows = array();
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row;
            }

            return $rows;
        }
    }
?>
