SELECT DISTINCT id, name
FROM (SELECT DISTINCT id, name, excluded
      FROM (
               SELECT DISTINCT w.word_id                                                                       AS id,
                               CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN w.word_name ELSE u.word_name END AS name,
                               CASE
                                   WHEN (u.excluded IS NULL) THEN COALESCE(w.excluded, 0)
                                   ELSE COALESCE(u.excluded, 0) END                                            AS excluded
               FROM (SELECT from_phrase_id AS id
                     FROM (
                              SELECT DISTINCT l.from_phrase_id,
                                              CASE
                                                  WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded, 0)
                                                  ELSE COALESCE(u.excluded, 0) END AS excluded
                              FROM triples l
                                       LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                  AND u.user_id = 1
                              WHERE l.to_phrase_id = 2
                                AND l.verb_id = 1) AS a
                     WHERE excluded = 0) a,
                    words w
                        LEFT JOIN user_words u ON u.word_id = w.word_id
                        AND u.user_id = 1
               WHERE w.word_id NOT IN (SELECT from_phrase_id
                                       FROM (
                                                SELECT DISTINCT l.from_phrase_id,
                                                                CASE
                                                                    WHEN (u.excluded IS NULL)
                                                                        THEN COALESCE(l.excluded, 0)
                                                                    ELSE COALESCE(u.excluded, 0) END AS excluded
                                                FROM triples l
                                                         LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                                    AND u.user_id = 1
                                                WHERE l.to_phrase_id <> 2
                                                  AND l.verb_id = 1
                                                  AND l.from_phrase_id IN (SELECT from_phrase_id AS id
                                                                           FROM (
                                                                                    SELECT DISTINCT l.from_phrase_id,
                                                                                                    CASE
                                                                                                        WHEN (u.excluded IS NULL)
                                                                                                            THEN COALESCE(l.excluded, 0)
                                                                                                        ELSE COALESCE(u.excluded, 0) END AS excluded
                                                                                    FROM triples l
                                                                                             LEFT JOIN user_triples u
                                                                                                       ON u.triple_id =
                                                                                                          l.triple_id
                                                                                                           AND
                                                                                                          u.user_id = 1
                                                                                    WHERE l.to_phrase_id = 2
                                                                                      AND l.verb_id = 1) AS a
                                                                           WHERE excluded = 0)) AS o
                                       WHERE excluded = 0)
                 AND w.word_id = a.id) AS w
      WHERE excluded = 0
      UNION
      SELECT DISTINCT id, name, excluded
      FROM (
               SELECT DISTINCT l.triple_id * -1                                                                AS id,
                               CASE
                                   WHEN (u.name_given <> '' IS NOT TRUE) THEN l.name_given
                                   ELSE u.name_given END                                                          AS name,
                               CASE
                                   WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded, 0)
                                   ELSE COALESCE(u.excluded, 0) END                                               AS excluded
               FROM triples l
                        LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                   AND u.user_id = 1
               WHERE l.from_phrase_id IN (SELECT from_phrase_id
                                          FROM (
                                                   SELECT DISTINCT l.from_phrase_id,
                                                                   CASE
                                                                       WHEN (u.excluded IS NULL)
                                                                           THEN COALESCE(l.excluded, 0)
                                                                       ELSE COALESCE(u.excluded, 0) END AS excluded
                                                   FROM triples l
                                                            LEFT JOIN user_triples u
                                                                      ON u.triple_id = l.triple_id
                                                                          AND u.user_id = 1
                                                   WHERE l.to_phrase_id <> 2
                                                     AND l.verb_id = 1
                                                     AND l.from_phrase_id IN (SELECT from_phrase_id AS id
                                                                              FROM (
                                                                                       SELECT DISTINCT l.from_phrase_id,
                                                                                                       CASE
                                                                                                           WHEN (u.excluded IS NULL)
                                                                                                               THEN COALESCE(l.excluded, 0)
                                                                                                           ELSE COALESCE(u.excluded, 0) END AS excluded
                                                                                       FROM triples l
                                                                                                LEFT JOIN user_triples u
                                                                                                          ON u.triple_id =
                                                                                                             l.triple_id
                                                                                                              AND
                                                                                                             u.user_id =
                                                                                                             1
                                                                                       WHERE l.to_phrase_id = 2
                                                                                         AND l.verb_id = 1) AS a
                                                                              WHERE excluded = 0)) AS o
                                          WHERE excluded = 0)
                 AND l.verb_id = 1
                 AND l.to_phrase_id = 2) AS t
      WHERE excluded = 0) AS p
WHERE excluded = 0
ORDER BY p.name;