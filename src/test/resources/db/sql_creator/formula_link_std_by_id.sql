SELECT formula_link_id,
       formula_id,
       phrase_id,
       formula_link_type_id,
       excluded
  FROM formula_links
 WHERE formula_link_id = $1;