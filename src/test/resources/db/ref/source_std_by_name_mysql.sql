PREPARE source_std_by_name FROM
    'SELECT source_id,
            source_name,
            code_id,
            `url`,
            description,
            source_type_id,
            excluded,
            user_id
       FROM sources
      WHERE source_name = ?';