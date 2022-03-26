SELECT      e.view_component_id,
            u.view_component_id AS user_entry_id,
            e.user_id,
            IF(y.order_nbr IS NULL, l.order_nbr, y.order_nbr) AS order_nbr,
            IF(u.view_component_name IS NULL,    e.view_component_name,    u.view_component_name)    AS view_component_name,
            IF(u.view_component_type_id IS NULL, e.view_component_type_id, u.view_component_type_id) AS view_component_type_id,
            IF(c.code_id IS NULL,                t.code_id,                c.code_id)                AS code_id,
            IF(u.word_id_row IS NULL,            e.word_id_row,            u.word_id_row)            AS word_id_row,
            IF(u.link_type_id IS NULL,           e.link_type_id,           u.link_type_id)           AS link_type_id,
            IF(u.formula_id IS NULL,             e.formula_id,             u.formula_id)             AS formula_id,
            IF(u.word_id_col IS NULL,            e.word_id_col,            u.word_id_col)            AS word_id_col,
            IF(u.word_id_col2 IS NULL,           e.word_id_col2,           u.word_id_col2)           AS word_id_col2,
            IF(y.excluded IS NULL,               l.excluded,               y.excluded)               AS link_excluded,
            IF(u.excluded IS NULL,               e.excluded,               u.excluded)               AS excluded
       FROM view_component_links l
  LEFT JOIN user_view_component_links y ON y.view_component_link_id = l.view_component_link_id
                                       AND y.user_id = 1,
            view_components e
  LEFT JOIN user_view_components u      ON u.view_component_id = e.view_component_id
                                       AND u.user_id = 1
  LEFT JOIN view_component_types t      ON e.view_component_type_id = t.view_component_type_id
  LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
      WHERE l.view_id = 2
        AND l.view_component_id = e.view_component_id
   ORDER BY order_nbr;