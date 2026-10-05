PREPARE group_list_by_phr FROM
   'SELECT     group_id,
               group_name,
               description
          FROM `groups`
         WHERE REGEXP_LIKE(group_id, ?, 'c')

  UNION SELECT group_id,
               group_name,
               description
          FROM groups_prime
         WHERE REGEXP_LIKE(group_id, ?, 'c')

  UNION SELECT group_id,
               group_name,
               description
          FROM groups_big
         WHERE REGEXP_LIKE(group_id, ?, 'c')';