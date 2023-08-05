PREPARE ref_type (int, int) AS
    SELECT ref_type_id,
           type_name,
           description,
           code_id,
           base_url
      FROM ref_types
  ORDER BY ref_type_id
     LIMIT $1
    OFFSET $2;