<?php

/*

    cfg/db/sql_sync_sequences.php - check and fix the sql sequences in all database dialects
    -----------------------------

    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    gbi46 in GitHub and Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\db;

include_once MODEL_USER_PATH . 'user_message.php';

use cfg\user\user_message;
use Exception;

class sql_sync_sequences
{

    private function sync_postgres($result, sql_db $db_con): user_message
    {
        global $debug;

        $usr_msg = new user_message();
        while ($row = pg_fetch_assoc($result)) {
            $sequence = $row['sequence_name'];
            $table = $row['table_name'];
            $column = $row['column_name'];
            if ($debug > 0) {
                echo "Checking sequence '$sequence' for table '$table' column '$column'...\n";
            }

            // Get max value of the column
            $max_query = "SELECT MAX(" . $column . ") AS max_val FROM " . $table;
            $max_result = pg_query($db_con->postgres_link, $max_query);
            $max_row = pg_fetch_assoc($max_result);
            $max_val = $max_row['max_val'] ?? 0;

            // Get current sequence value
            $curr_seq_query = "SELECT last_value FROM \"$sequence\"";
            $curr_seq_result = pg_query($db_con->postgres_link, $curr_seq_query);
            $curr_seq_row = pg_fetch_assoc($curr_seq_result);
            $curr_val = $curr_seq_row['last_value'] ?? 0;

            // Compare and update if needed
            if ($max_val > $curr_val) {
                $set_val_sql = "SELECT setval('$sequence', $max_val)";
                pg_query($db_con->postgres_link, $set_val_sql);
                echo "Sequence '$sequence' updated to $max_val\n";
            } else {
                if ($debug > 0) {
                    echo "Sequence '$sequence' is already up to date ($curr_val >= $max_val)\n";
                }
            }
        }
        return $usr_msg;
    }

    private function sync_mysql($result, sql_db $db_con): user_message
    {
        global $debug;

        $usr_msg = new user_message();
        while ($row = mysqli_fetch_assoc($result)) {
            $table = $row['TABLE_NAME'];
            $column = $row['COLUMN_NAME'];

            if ($debug > 0) {
                echo "Checking AUTO_INCREMENT for table '$table' column '$column'...\n";
            }

            // Get max value
            $max_sql = "SELECT MAX(`$column`) AS max_val FROM `$table`";
            $max_result = mysqli_query($db_con->mysql, $max_sql);
            $max_row = mysqli_fetch_assoc($max_result);
            $max_val = $max_row['max_val'] ?? 0;

            // Get current AUTO_INCREMENT value
            $status_sql = "SHOW TABLE STATUS LIKE '$table'";
            $status_result = mysqli_query($db_con->mysql, $status_sql);
            $status_row = mysqli_fetch_assoc($status_result);
            $auto_increment = $status_row['Auto_increment'] ?? 0;

            // Compare and update
            if ($max_val + 1 > $auto_increment) {
                $set_ai_sql = "ALTER TABLE `$table` AUTO_INCREMENT = " . ($max_val + 1);
                mysqli_query($db_con->mysql, $set_ai_sql);
                echo "→ Updated AUTO_INCREMENT for '$table' to " . ($max_val + 1) . "\n";
            } else {
                if ($debug > 0) {
                    echo "→ AUTO_INCREMENT for '$table' is already up to date ($auto_increment >= $max_val + 1)\n";
                }
            }
        }
        return $usr_msg;
    }

    public function sync(sql_db $db_con): user_message
    {
        $usr_msg = new user_message();
        switch ($db_con->db_type) {
            case sql_db::POSTGRES:
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

                try {
                    $result = pg_query($db_con->postgres_link, $sql);
                } catch (Exception $e) {
                    $db_con->log_db_exception('sequence reset', $e, $sql, $log_level);
                }
                $usr_msg->add($this->sync_postgres($result, $db_con));
                break;
            case sql_db::MYSQL:
                $sql = "
                    SELECT TABLE_NAME, COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = 'zukunft'
                    AND EXTRA = 'auto_increment';
                ";
                $result = mysqli_query($db_con->mysql, $sql);
                $usr_msg->add($this->sync_mysql($result, $db_con));
        }
        return $usr_msg;
    }

}
