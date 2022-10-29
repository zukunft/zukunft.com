PREPARE phrase_list_by_3ids_triple_part FROM
    'SELECT s.triple_id,
            u.triple_id AS user_triple_id,
            s.user_id,
            s.from_phrase_id,
            s.to_phrase_id,
            s.verb_id,
            s.word_type_id,
            s.triple_condition_id,
            s.triple_condition_type_id,
            IF(u.name_given     IS NULL, s.name_given,     u.name_given)     AS name_given,
            IF(u.name_generated IS NULL, s.name_generated, u.name_generated) AS name_generated,
            IF(u.description    IS NULL, s.description,    u.description)    AS description,
            IF(u.`values`       IS NULL, s.`values`,       u.`values`)       AS `values`,
            IF(u.excluded       IS NULL, s.excluded,       u.excluded)       AS excluded,
            IF(u.share_type_id  IS NULL, s.share_type_id,  u.share_type_id)  AS share_type_id,
            IF(u.protect_id IS NULL, s.protect_id, u.protect_id) AS protect_id
       FROM triples s
  LEFT JOIN user_triples u ON s.triple_id = u.triple_id AND u.user_id = ?
      WHERE s.triple_id IN (?,?,?)';