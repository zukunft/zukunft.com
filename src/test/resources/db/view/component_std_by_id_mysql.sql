PREPARE component_std_by_id FROM
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
            protect_id,
            user_id
       FROM view_components
      WHERE view_component_id = ?';