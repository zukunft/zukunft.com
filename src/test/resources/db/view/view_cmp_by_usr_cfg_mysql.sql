PREPARE view_cmp_by_usr_cfg FROM
   'SELECT view_component_id,
           view_component_name,
           description,
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
     WHERE view_component_id = ?
       AND user_id = ?';
