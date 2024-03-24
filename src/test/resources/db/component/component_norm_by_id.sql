PREPARE component_norm_by_id (bigint) AS
    SELECT component_id,
           component_name,
           code_id,
           ui_msg_code_id,
           description,
           component_type_id,
           word_id_row,
           link_type_id,
           formula_id,
           word_id_col,
           word_id_col2,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM components
     WHERE component_id = $1;