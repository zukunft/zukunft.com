PREPARE system_time_insert_011111 (timestamp, timestamp, smallint, text, bigint) AS
    INSERT INTO system_times (start_time, end_time, system_time_type_id, url, milliseconds)
         VALUES         ($1,$2,$3,$4,$5)
      RETURNING system_time_id;
