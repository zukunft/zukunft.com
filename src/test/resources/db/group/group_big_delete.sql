PREPARE group_big_delete (text) AS
    DELETE FROM groups_big
          WHERE group_id = $1;