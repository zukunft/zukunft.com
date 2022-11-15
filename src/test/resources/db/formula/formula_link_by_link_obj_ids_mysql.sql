PREPARE formula_link_by_link_obj_ids FROM
    'SELECT
               s.formula_link_id,
               u.formula_link_id AS user_formula_link_id,
               s.user_id,
               s.formula_id,
               s.phrase_id,
               IF(u.link_type_id  IS NULL, s.link_type_id,  u.link_type_id)  AS link_type_id,
               IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded,
               IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
               IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id)    AS protect_id
          FROM formula_links s
     LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id
           AND u.user_id = ?
         WHERE s.formula_id = ? AND s.phrase_id = ?';