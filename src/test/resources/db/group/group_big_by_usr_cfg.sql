PREPARE group_big_by_usr_cfg (text, bigint) AS
    SELECT group_id,
           group_name,
           description
      FROM user_groups_big
     WHERE group_id = $1
       AND user_id = $2;