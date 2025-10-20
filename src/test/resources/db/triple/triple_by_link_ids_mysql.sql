PREPARE triple_by_link_ids FROM
    'SELECT
                s.triple_id,
                u.triple_id AS user_triple_id,
                s.user_id,
                s.from_phrase_id,
                s.verb_id,
                s.to_phrase_id,
                s.code_id,
                s.`usage`,
                s.triple_condition_id,
                IF(u.triple_name    IS NULL, s.triple_name,    u.triple_name)    AS triple_name,
                IF(u.name_given     IS NULL, s.name_given,     u.name_given)     AS name_given,
                IF(u.name_generated IS NULL, s.name_generated, u.name_generated) AS name_generated,
                IF(u.description    IS NULL, s.description,    u.description)    AS description,
                IF(u.weight         IS NULL, s.weight,         u.weight)         AS weight,
                IF(u.phrase_type_id IS NULL, s.phrase_type_id, u.phrase_type_id) AS phrase_type_id,
                IF(u.view_id        IS NULL, s.view_id,        u.view_id)        AS view_id,
                IF(u.impact         IS NULL, s.impact,         u.impact)         AS impact,
                IF(u.excluded       IS NULL, s.excluded,       u.excluded)       AS excluded,
                IF(u.share_type_id  IS NULL, s.share_type_id,  u.share_type_id)  AS share_type_id,
                IF(u.protect_id     IS NULL, s.protect_id,     u.protect_id)     AS protect_id
           FROM triples s
      LEFT JOIN user_triples u ON s.triple_id = u.triple_id
            AND u.user_id = ?
          WHERE s.from_phrase_id = ?
            AND s.to_phrase_id = ?
            AND s.verb_id = ?';