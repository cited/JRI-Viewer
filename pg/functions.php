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


        function getGEOM($table, $ignore) {
            $columns = [];
            $rows = $this->getAll($table);
            if(count($rows)) {
                $row = $rows[0];
                foreach($row as $k => $v) {
                    if($k != $ignore) $columns[] = $k;
                }
            }


            $query = "
                SELECT
                    row_to_json(fc) AS featurecollection
                FROM (
                    SELECT
                        'FeatureCollection' AS type,
                        array_to_json(array_agg(feature)) AS features
                    FROM (
                        SELECT
                            'Feature' AS type,
                            ST_AsGeoJSON(geom)::json AS geometry,
                            row_to_json((SELECT l FROM (SELECT ".implode(',', $columns).") AS l)) AS properties
                        FROM
                            {$table}
                    ) AS feature
                ) AS fc;
            ";

            $result = pg_query($this->connection, $query);

            if (!$result) {
                echo "An error occurred executing the query.\n";
                exit;
            }

            $row = pg_fetch_assoc($result);
            return $row;
        }
    }
?>
