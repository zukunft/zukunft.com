SELECT component_id,
       component_name,
       description,
       component_type_id,
       word_id_row,
       link_type_id,
       formula_id,
       word_id_col,
       word_id_col2,
       excluded
  FROM components
 WHERE component_id = ?;