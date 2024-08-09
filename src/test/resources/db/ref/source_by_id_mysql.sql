PREPARE source_by_id FROM
    'SELECT s.source_id,
            u.source_id AS user_source_id,
            s.user_id,
            s.source_name,
            s.code_id,
            IF(u.source_name    IS NULL, s.source_name,    u.source_name)    AS source_name,
            IF(u.`url`          IS NULL, s.`url`,          u.`url`)          AS `url`,
            IF(u.description    IS NULL, s.description,    u.description)    AS description,
            IF(u.source_type_id IS NULL, s.source_type_id, u.source_type_id) AS source_type_id,
            IF(u.excluded       IS NULL, s.excluded,       u.excluded)       AS excluded,
            IF(u.share_type_id  IS NULL, s.share_type_id,  u.share_type_id)  AS share_type_id,
            IF(u.protect_id     IS NULL, s.protect_id,     u.protect_id)     AS protect_id
       FROM sources s
  LEFT JOIN user_sources u ON s.source_id = u.source_id AND u.user_id = ?
      WHERE s.source_id = ?';