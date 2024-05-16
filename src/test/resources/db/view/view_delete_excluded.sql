PREPARE view_delete_excluded (bigint) AS
    DELETE FROM views
           WHERE view_id = $1
             AND excluded = 1;
