PREPARE view_cmp_by_name (int, text) AS
    SELECT     s.view_component_id,
               u.view_component_id AS user_view_component_id,
               s.user_id,
               CASE WHEN (u.view_component_name <> '' IS NOT TRUE) THEN s.view_component_name    ELSE u.view_component_name    END AS view_component_name,
               CASE WHEN (u.comment             <> '' IS NOT TRUE) THEN s.comment                ELSE u.comment                END AS comment,
               CASE WHEN (u.view_component_type_id    IS     NULL) THEN s.view_component_type_id ELSE u.view_component_type_id END AS view_component_type_id,
               CASE WHEN (u.word_id_row               IS     NULL) THEN s.word_id_row            ELSE u.word_id_row            END AS word_id_row,
               CASE WHEN (u.link_type_id              IS     NULL) THEN s.link_type_id           ELSE u.link_type_id           END AS link_type_id,
               CASE WHEN (u.formula_id                IS     NULL) THEN s.formula_id             ELSE u.formula_id             END AS formula_id,
               CASE WHEN (u.word_id_col               IS     NULL) THEN s.word_id_col            ELSE u.word_id_col            END AS word_id_col,
               CASE WHEN (u.word_id_col2              IS     NULL) THEN s.word_id_col2           ELSE u.word_id_col2           END AS word_id_col2,
               CASE WHEN (u.excluded                  IS     NULL) THEN s.excluded               ELSE u.excluded               END AS excluded,
               CASE WHEN (u.share_type_id             IS     NULL) THEN s.share_type_id          ELSE u.share_type_id          END AS share_type_id,
               CASE WHEN (u.protect_id                IS     NULL) THEN s.protect_id             ELSE u.protect_id             END AS protect_id,
               CASE WHEN (c.code_id             <> '' IS NOT TRUE) THEN l.code_id                ELSE c.code_id                END AS code_id
          FROM view_components s
     LEFT JOIN user_view_components u ON s.view_component_id = u.view_component_id AND u.user_id = $1
     LEFT JOIN view_component_types l ON s.view_component_type_id = l.view_component_type_id
     LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
         WHERE s.view_component_name = $2;