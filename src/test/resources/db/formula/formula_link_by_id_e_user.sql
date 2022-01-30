SELECT formula_link_id,
       link_type_id,
       excluded,
       share_type_id,
       protect_id
  FROM user_formula_links
 WHERE formula_link_id = 2
   AND user_id = 1;