PREPARE word_link_by_not_changed_not_owned (int, int) AS
    SELECT user_id
      FROM word_links
     WHERE word_link_id = $1
       AND (excluded <> 1 OR excluded is NULL)
       AND user_id <> $2;