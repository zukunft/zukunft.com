PREPARE component_by_id (bigint, bigint) AS
    SELECT     s.component_id,
               u.component_id AS user_component_id,
               s.user_id,
               s.code_id,
               s.ui_msg_code_id,
               CASE WHEN (u.component_name <> '' IS NOT TRUE) THEN s.component_name    ELSE u.component_name    END AS component_name,
               CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description       ELSE u.description       END AS description,
               CASE WHEN (u.component_type_id    IS     NULL) THEN s.component_type_id ELSE u.component_type_id END AS component_type_id,
               CASE WHEN (u.view_style_id        IS     NULL) THEN s.view_style_id     ELSE u.view_style_id     END AS view_style_id,
               CASE WHEN (u.word_id_row          IS     NULL) THEN s.word_id_row       ELSE u.word_id_row       END AS word_id_row,
               CASE WHEN (u.link_type_id         IS     NULL) THEN s.link_type_id      ELSE u.link_type_id      END AS link_type_id,
               CASE WHEN (u.formula_id           IS     NULL) THEN s.formula_id        ELSE u.formula_id        END AS formula_id,
               CASE WHEN (u.word_id_col          IS     NULL) THEN s.word_id_col       ELSE u.word_id_col       END AS word_id_col,
               CASE WHEN (u.word_id_col2         IS     NULL) THEN s.word_id_col2      ELSE u.word_id_col2      END AS word_id_col2,
               CASE WHEN (u.excluded             IS     NULL) THEN s.excluded          ELSE u.excluded          END AS excluded,
               CASE WHEN (u.share_type_id        IS     NULL) THEN s.share_type_id     ELSE u.share_type_id     END AS share_type_id,
               CASE WHEN (u.protect_id           IS     NULL) THEN s.protect_id        ELSE u.protect_id        END AS protect_id
          FROM components s
     LEFT JOIN user_components u  ON s.component_id = u.component_id AND u.user_id = $1
         WHERE s.component_id = $2;