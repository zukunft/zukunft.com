PREPARE group_list_by_phr (text) AS
    SELECT group_id,
           group_name,
           description
      FROM groups
     WHERE group_id like $3
UNION
    SELECT group_id,
           group_name,
           description
      FROM groups_prime
     WHERE group_id like $3
UNION
    SELECT group_id,
           group_name,
           description
      FROM groups_big
     WHERE group_id like $3;
