PREPARE ref_type (int) AS
    SELECT ref_type_id,
           type_name,
           description,
           code_id,
           base_url
      FROM ref_types
     LIMIT $1;