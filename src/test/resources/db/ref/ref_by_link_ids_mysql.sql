PREPARE ref_by_link_ids FROM
    'SELECT
            s.ref_id,
            u.ref_id AS user_ref_id,
            s.user_id,
            s.phrase_id,
            s.ref_type_id,
            IF(u.external_key  IS NULL, s.external_key,  u.external_key)  AS external_key,
            IF(u.`url`         IS NULL, s.`url`,         u.`url`)         AS `url`,
            IF(u.description   IS NULL, s.description,   u.description)   AS description,
            IF(u.source_id     IS NULL, s.source_id,     u.source_id)     AS source_id,
            IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded,
            IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
            IF(u.protect_id    IS NULL, s.protect_id,    u.protect_id)    AS protect_id
       FROM refs s
  LEFT JOIN user_refs u ON s.ref_id = u.ref_id AND u.user_id = ?
      WHERE s.phrase_id = ?
        AND s.ref_type_id = ?';