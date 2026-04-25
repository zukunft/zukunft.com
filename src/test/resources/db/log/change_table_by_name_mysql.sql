PREPARE change_table_by_name FROM
    'SELECT change_table_id,
            change_table_name,
            description,
            code_id
       FROM change_tables
      WHERE change_table_name = ?
   ORDER BY change_table_id';