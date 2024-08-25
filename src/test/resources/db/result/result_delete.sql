PREPARE result_delete (text) AS
    DELETE FROM results
          WHERE group_id = $1;