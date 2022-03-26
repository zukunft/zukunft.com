PREPARE view_components_by_view_id FROM
    'SELECT     s.view_component_link_id,
                u.view_component_link_id AS user_view_component_link_id,
                s.user_id,
                s.view_id,
                s.view_component_id,
                IF(u.order_nbr                IS NULL,  s.order_nbr,               u.order_nbr)                AS order_nbr,
                IF(u.position_type            IS NULL,  s.position_type,           u.position_type)            AS position_type,
                IF(u.excluded                 IS NULL,  s.excluded,                u.excluded)                 AS excluded,
                IF(u.share_type_id            IS NULL,  s.share_type_id,           u.share_type_id)            AS share_type_id,
                IF(u.protect_id               IS NULL,  s.protect_id,              u.protect_id)               AS protect_id,
                IF(ul2.comment                IS NULL, l2.comment,                 ul2.comment)                AS comment2,
                IF(ul3.view_component_type_id IS NULL,  l3.view_component_type_id, ul3.view_component_type_id) AS view_component_type_id3,
                IF(ul3.word_id_row            IS NULL,  l3.word_id_row,            ul3.word_id_row)            AS word_id_row3,
                IF(ul3.link_type_id           IS NULL,  l3.link_type_id,           ul3.link_type_id)           AS link_type_id3,
                IF(ul3.formula_id             IS NULL,  l3.formula_id,             ul3.formula_id)             AS formula_id3,
                IF(ul3.word_id_col            IS NULL,  l3.word_id_col,            ul3.word_id_col)            AS word_id_col3,
                IF(ul3.word_id_col2           IS NULL,  l3.word_id_col2,           ul3.word_id_col2)           AS word_id_col23,
                IF(ul3.excluded               IS NULL,  l3.excluded,               ul3.excluded)               AS excluded3,
                IF(ul3.share_type_id          IS NULL,  l3.share_type_id,          ul3.share_type_id)          AS share_type_id3,
                IF(ul3.protect_id             IS NULL,  l3.protect_id,             ul3.protect_id)             AS protect_id3
           FROM view_component_links s
      LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id
            AND u.user_id = ?
      LEFT JOIN view_components l           ON s.view_component_id = l.view_component_id
      LEFT JOIN view_components l2          ON s.view_component_id = l2.view_component_id
      LEFT JOIN user_view_components ul2    ON l2.view_component_id = ul2.view_component_id
            AND ul2.user_id = ?
      LEFT JOIN view_components l3          ON s.view_component_id = l3.view_component_id
      LEFT JOIN user_view_components ul3    ON l3.view_component_id = ul3.view_component_id
            AND ul3.user_id = ?
          WHERE s.view_id = ?
       ORDER BY s.order_nbr';
