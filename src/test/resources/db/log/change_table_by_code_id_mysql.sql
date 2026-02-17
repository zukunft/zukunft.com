PREPARE change_table_by_code_id FROM
    'SELECT change_table_id,
            change_table_name,
            description,
            code_id
       FROM change_tables
      WHERE code_id = ?
   ORDER BY change_table_id';