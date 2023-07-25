PREPARE phrase_group_list_by_ids_fast (int[]) AS
    SELECT
            phrase_group_id,
            phrase_group_name,
            auto_description,
            word_ids,
            triple_ids,
            id_order
       FROM phrase_groups
      WHERE phrase_group_id = ANY ($1)
   ORDER BY phrase_group_id
      LIMIT 20;