PREPARE formula_link_by_usr_cfg FROM
   'SELECT     formula_link_id,
               formula_link_type_id,
               order_nbr,
               description,
               excluded,
               share_type_id,
               protect_id
          FROM user_formula_links
         WHERE formula_link_id = ?
           AND user_id = ?';