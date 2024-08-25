PREPARE view_delete (bigint) AS
    DELETE FROM views
           WHERE view_id = $1;