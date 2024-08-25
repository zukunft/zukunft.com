PREPARE view_norm_by_name FROM
    'SELECT view_id,
            view_name,
            code_id,
            description,
            view_type_id,
            excluded,
            share_type_id,
            protect_id,
            user_id
       FROM views
      WHERE view_name = ?';