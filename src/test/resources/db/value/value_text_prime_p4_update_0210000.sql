PREPARE value_text_prime_p4_update_0210000 (text, smallint, smallint, smallint, smallint) AS
    UPDATE values_text_prime
       SET text_value = $1,
           last_update = Now()
     WHERE phrase_id_1 = $2
       AND phrase_id_2 = $3
       AND phrase_id_3 = $4
       AND phrase_id_4 = $5;