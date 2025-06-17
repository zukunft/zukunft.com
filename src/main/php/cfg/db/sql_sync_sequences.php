<?php

define('ROOT_PATH', dirname(__DIR__, 5) . DIRECTORY_SEPARATOR);

const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;

include_once PHP_PATH . 'zu_lib.php';
include_once DB_PATH . 'sql_db.php';

use cfg\db\sql_db;

class sync_sequences {
    private sql_db $db_con;

    public function __construct() {
        $this->db_con = new sql_db();
        $this->db_con->open();
    }

    private function sync_postgres($result) {
        while ($row = pg_fetch_assoc($result)) {
            $sequence = $row['sequence_name'];
            $table = $row['table_name'];
            $column = $row['column_name'];
            echo "Checking sequence '$sequence' for table '$table' column '$column'...\n";

            // Get max value of the column
            $max_query = "SELECT MAX(" . $column . ") AS max_val FROM " . $table;
            $max_result = pg_query($this->db_con->postgres_link, $max_query);
            $max_row = pg_fetch_assoc($max_result);
            $max_val = $max_row['max_val'] ?? 0;

            // Get current sequence value
            $curr_seq_query = "SELECT last_value FROM \"$sequence\"";
            $curr_seq_result = pg_query($this->db_con->postgres_link, $curr_seq_query);
            $curr_seq_row = pg_fetch_assoc($curr_seq_result);
            $curr_val = $curr_seq_row['last_value'] ?? 0;

            // Compare and update if needed
            if ($max_val > $curr_val) {
                $setval_sql = "SELECT setval('$sequence', $max_val)";
                pg_query($this->db_con->postgres_link, $setval_sql);
                echo "Sequence '$sequence' updated to $max_val\n";
            } else {
                echo "Sequence '$sequence' is already up to date ($curr_val >= $max_val)\n";
            }
        }
    }

    private function sync_mysql($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $table = $row['TABLE_NAME'];
            $column = $row['COLUMN_NAME'];

            echo "Checking AUTO_INCREMENT for table '$table' column '$column'...\n";

            // Get max value
            $max_sql = "SELECT MAX(`$column`) AS max_val FROM `$table`";
            $max_result = mysqli_query($this->db_con->mysql, $max_sql);
            $max_row = mysqli_fetch_assoc($max_result);
            $max_val = $max_row['max_val'] ?? 0;

            // Get current AUTO_INCREMENT value
            $status_sql = "SHOW TABLE STATUS LIKE '$table'";
            $status_result = mysqli_query($this->db_con->mysql, $status_sql);
            $status_row = mysqli_fetch_assoc($status_result);
            $auto_increment = $status_row['Auto_increment'] ?? 0;

            // Compare and update
            if ($max_val + 1 > $auto_increment) {
                $set_ai_sql = "ALTER TABLE `$table` AUTO_INCREMENT = " . ($max_val + 1);
                mysqli_query($this->db_con->mysql, $set_ai_sql);
                echo "→ Updated AUTO_INCREMENT for '$table' to " . ($max_val + 1) . "\n";
            } else {
                echo "→ AUTO_INCREMENT for '$table' is already up to date ($auto_increment >= $max_val + 1)\n";
            }
        }
    }

    public function sync($dbType) {
        switch($dbType) {
            case 'Postgres':
                $sql = "
                    SELECT t.relname AS table_name,
                        a.attname AS column_name,
                        s.relname AS sequence_name
                    FROM pg_class t
                    JOIN pg_attribute a ON t.oid = a.attrelid
                    JOIN pg_depend d ON d.refobjid = t.oid AND d.refobjsubid = a.attnum
                    JOIN pg_class s ON s.oid = d.objid
                    WHERE d.deptype = 'a'
                    AND s.relkind = 'S';
                ";

                $result = pg_query($this->db_con->postgres_link, $sql);
                $this->sync_postgres($result);
            break;
            case 'MySQL':
                $sql = "
                    SELECT TABLE_NAME, COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = 'zukunft'
                    AND EXTRA = 'auto_increment';
                ";
                $result = mysqli_query($this->db_con->mysql, $sql);
                $this->sync_mysql($result);
        }
    }

    public function run() {
        return $this->sync($this->db_con->db_type);
    }
}
