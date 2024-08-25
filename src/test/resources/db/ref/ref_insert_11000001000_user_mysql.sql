PREPARE ref_insert_11000001000_user FROM
    'INSERT INTO user_refs (ref_id,user_id,description)
          VALUES (?,?,?)';