SELECT formula_link_id,
       formula_id,
       phrase_id
  FROM formula_links
 WHERE phrase_id = ?;