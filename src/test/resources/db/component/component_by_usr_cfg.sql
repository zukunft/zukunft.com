PREPARE component_by_usr_cfg (bigint, bigint) AS
    SELECT component_id,
           component_name,
           description,
           component_type_id,
           view_style_id,
           word_id_row,
           link_type_id,
           formula_id,
           word_id_col,
           word_id_col2,
           excluded,
           share_type_id,
           protect_id
      FROM user_components
     WHERE component_id = $1
       AND user_id = $2;
