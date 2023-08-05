PREPARE ref_type FROM
   'SELECT ref_type_id,
           type_name,
           description,
           code_id,
           base_url
      FROM ref_types
  ORDER BY ref_type_id
     LIMIT ?
    OFFSET ?';