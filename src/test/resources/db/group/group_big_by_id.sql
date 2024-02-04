PREPARE group_big_by_id (text) AS
    SELECT group_id,
           group_name,
           description
      FROM groups_big
     WHERE group_id = $1;