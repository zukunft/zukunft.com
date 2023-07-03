PREPARE component_by_id FROM
    'SELECT s.component_id,
            u.component_id AS user_component_id,
            s.user_id,
            s.code_id,
            s.ui_msg_code_id,
            IF(u.component_name    IS NULL, s.component_name,    u.component_name)    AS component_name,
            IF(u.description       IS NULL, s.description,       u.description)       AS description,
            IF(u.component_type_id IS NULL, s.component_type_id, u.component_type_id) AS component_type_id,
            IF(u.word_id_row       IS NULL, s.word_id_row,       u.word_id_row)       AS word_id_row,
            IF(u.link_type_id      IS NULL, s.link_type_id,      u.link_type_id)      AS link_type_id,
            IF(u.formula_id        IS NULL, s.formula_id,        u.formula_id)        AS formula_id,
            IF(u.word_id_col       IS NULL, s.word_id_col,       u.word_id_col)       AS word_id_col,
            IF(u.word_id_col2      IS NULL, s.word_id_col2,      u.word_id_col2)      AS word_id_col2,
            IF(u.excluded          IS NULL, s.excluded,          u.excluded)          AS excluded,
            IF(u.share_type_id     IS NULL, s.share_type_id,     u.share_type_id)     AS share_type_id,
            IF(u.protect_id        IS NULL, s.protect_id,        u.protect_id)        AS protect_id
       FROM components s
  LEFT JOIN user_components u  ON s.component_id = u.component_id AND u.user_id = ?
      WHERE s.component_id = ?';