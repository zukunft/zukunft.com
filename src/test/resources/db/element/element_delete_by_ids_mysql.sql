PREPARE element_delete_by_ids FROM
    'DELETE FROM elements
          WHERE element_id IN (?)';