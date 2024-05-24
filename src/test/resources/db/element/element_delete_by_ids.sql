PREPARE element_delete_by_ids (bigint[]) AS
    DELETE FROM elements
          WHERE element_id IN ($4);