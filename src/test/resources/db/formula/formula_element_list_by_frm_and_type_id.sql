PREPARE formula_element_list_by_frm_and_type_id (bigint, bigint, bigint) AS
    SELECT formula_element_id,
           formula_id,
           user_id,
           order_nbr,
           formula_element_type_id,
           ref_id
      FROM formula_elements
     WHERE formula_id = $1
       AND formula_element_type_id = $2
       AND user_id = $3;