PREPARE group_by_name (text) AS
    SELECT group_id,
           group_name,
           description
      FROM groups
     WHERE group_name = $1;