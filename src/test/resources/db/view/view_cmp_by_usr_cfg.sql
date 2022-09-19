PREPARE view_cmp_by_usr_cfg (int, int) AS
    SELECT view_component_id,
           view_component_name,
           comment,
           view_component_type_id,
           word_id_row,
           link_type_id,
           formula_id,
           word_id_col,
           word_id_col2,
           excluded,
           share_type_id,
           protect_id
      FROM user_view_components
     WHERE view_component_id = $1
       AND user_id = $2;
