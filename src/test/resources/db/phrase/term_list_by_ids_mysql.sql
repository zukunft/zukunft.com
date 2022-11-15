PREPARE term_list_by_ids FROM
    'SELECT s.term_id,
            u.term_id AS user_term_id,
            s.user_id,
            IF(u.term_name     IS NULL,s.term_name,     u.term_name)     AS term_name,
            IF(u.description   IS NULL,s.description,   u.description)   AS description,
            IF(u.`usage`       IS NULL,s.`usage`,       u.`usage`)       AS `usage`,
            IF(u.excluded      IS NULL,s.excluded,      u.excluded)      AS excluded,
            IF(u.share_type_id IS NULL,s.share_type_id, u.share_type_id) AS share_type_id,
            IF(u.protect_id    IS NULL,s.protect_id,    u.protect_id)    AS protect_id
       FROM terms s
  LEFT JOIN user_terms u ON s.term_id = u.term_id AND u.user_id = ?
      WHERE s.term_id IN (?)
   ORDER BY s.term_id';