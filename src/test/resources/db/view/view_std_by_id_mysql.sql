PREPARE view_std_by_id FROM
    'SELECT view_id,
        view_name,
        code_id,
        view_type_id,
        comment,
        excluded,
        user_id
   FROM views
  WHERE view_id = ?';