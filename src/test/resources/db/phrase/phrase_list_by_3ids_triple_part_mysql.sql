PREPARE phrase_list_by_3ids_triple_part FROM
    'SELECT s.word_link_id,
            u.word_link_id AS user_word_link_id,
            s.user_id,
            s.from_phrase_id,
            s.to_phrase_id,
            s.verb_id,
            s.word_type_id,
            s.word_link_condition_id,
            s.word_link_condition_type_id,
            IF(u.name           IS NULL, s.name,           u.name)           AS name,
            IF(u.name_generated IS NULL, s.name_generated, u.name_generated) AS name_generated,
            IF(u.description    IS NULL, s.description,    u.description)    AS description,
            IF(u.`values`       IS NULL, s.`values`,       u.`values`)       AS `values`,
            IF(u.excluded       IS NULL, s.excluded,       u.excluded)       AS excluded,
            IF(u.share_type_id  IS NULL, s.share_type_id,  u.share_type_id)  AS share_type_id,
            IF(u.protect_id IS NULL, s.protect_id, u.protect_id) AS protect_id
       FROM word_links s
  LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id AND u.user_id = ?
      WHERE s.word_link_id IN (?,?,?)';