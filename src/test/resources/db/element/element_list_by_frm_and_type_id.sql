PREPARE element_list_by_frm_and_type_id (bigint, bigint, bigint) AS
    SELECT element_id,
           formula_id,
           user_id,
           order_nbr,
           element_type_id,
           ref_id
      FROM elements
     WHERE formula_id = $1
       AND element_type_id = $2
       AND user_id = $3;