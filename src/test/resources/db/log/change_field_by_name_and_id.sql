PREPARE change_field_by_name_and_id (text, bigint) AS
    SELECT change_field_id,
           change_field_name,
           description,
           code_id
      FROM change_fields
     WHERE change_field_name = $1
       AND table_id = $2
  ORDER BY change_field_id;