PREPARE change_field_by_name_and_id FROM
    'SELECT change_field_id,
            change_field_name,
            description,
            code_id
       FROM change_fields
      WHERE change_field_name = ?
        AND table_id = ?
   ORDER BY change_field_id';