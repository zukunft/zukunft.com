PREPARE group_by_id (text) AS
    SELECT group_id,
           group_name,
           description
      FROM groups
     WHERE group_id = $1;