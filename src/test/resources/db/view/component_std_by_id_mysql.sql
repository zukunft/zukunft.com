PREPARE component_std_by_id FROM
    'SELECT component_id,
            component_name,
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
      WHERE component_id = ?';