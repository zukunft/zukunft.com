SELECT      e.view_component_id,
            u.view_component_id AS user_entry_id,
            e.user_id,
            CASE WHEN (y.order_nbr                 IS     NULL) THEN l.order_nbr              ELSE y.order_nbr              END AS order_nbr,
            CASE WHEN (u.view_component_name <> '' IS NOT TRUE) THEN e.view_component_name    ELSE u.view_component_name    END AS view_component_name,
            CASE WHEN (u.view_component_type_id    IS     NULL) THEN e.view_component_type_id ELSE u.view_component_type_id END AS view_component_type_id,
            CASE WHEN (c.code_id <> ''             IS NOT TRUE) THEN t.code_id                ELSE c.code_id                END AS code_id,
            CASE WHEN (u.word_id_row               IS     NULL) THEN e.word_id_row            ELSE u.word_id_row            END AS word_id_row,
            CASE WHEN (u.link_type_id              IS     NULL) THEN e.link_type_id           ELSE u.link_type_id           END AS link_type_id,
            CASE WHEN (u.formula_id                IS     NULL) THEN e.formula_id             ELSE u.formula_id             END AS formula_id,
            CASE WHEN (u.word_id_col               IS     NULL) THEN e.word_id_col            ELSE u.word_id_col            END AS word_id_col,
            CASE WHEN (u.word_id_col2              IS     NULL) THEN e.word_id_col2           ELSE u.word_id_col2           END AS word_id_col2,
            CASE WHEN (y.excluded                  IS     NULL) THEN l.excluded               ELSE y.excluded               END AS link_excluded,
            CASE WHEN (u.excluded                  IS     NULL) THEN e.excluded               ELSE u.excluded               END AS excluded
       FROM view_component_links l
  LEFT JOIN user_view_component_links y ON y.view_component_link_id = l.view_component_link_id
                                       AND y.user_id = 1,
            view_components e
  LEFT JOIN user_view_components u ON u.view_component_id = e.view_component_id
                                  AND u.user_id = 1
  LEFT JOIN view_component_types t ON e.view_component_type_id = t.view_component_type_id
  LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
      WHERE l.view_id = 2
        AND l.view_component_id = e.view_component_id
   ORDER BY order_nbr;