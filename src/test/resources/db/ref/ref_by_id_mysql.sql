PREPARE ref_by_id FROM
    'SELECT
            s.ref_id,
            u.ref_id AS user_ref_id,
            s.user_id,
            s.phrase_id,
            s.ref_type_id,
            s.external_key,
            s.source_id,
            IF(u.`url`       IS NULL,s.`url`,      u.`url`)       AS `url`,
            IF(u.description IS NULL,s.description,u.description) AS description,
            IF(u.excluded    IS NULL,s.excluded,   u.excluded)    AS excluded
       FROM refs s
  LEFT JOIN user_refs u ON s.ref_id = u.ref_id AND u.user_id = ?
      WHERE s.ref_id = ?';