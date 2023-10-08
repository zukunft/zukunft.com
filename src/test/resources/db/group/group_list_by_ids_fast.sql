PREPARE group_list_by_ids_fast (int[], int, int) AS
    SELECT
            group_id,
            group_name,
            description
       FROM groups
      WHERE group_id = ANY ($1)
   ORDER BY group_id
      LIMIT $2
     OFFSET $3;