PREPARE value_delete (text) AS
    DELETE FROM values
     WHERE group_id = $1;