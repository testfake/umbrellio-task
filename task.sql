-- Create table with data
CREATE TEMP TABLE users_temp(id bigserial, group_id bigint);
INSERT INTO users_temp(group_id) VALUES (1), (1), (1), (2), (1), (3);

-- Group users function
CREATE OR REPLACE FUNCTION group_users()
  RETURNS TABLE (min_id bigint, gr_id bigint, count bigint) AS
$$
  DECLARE
    r users_temp%rowtype;
  BEGIN
    -- Set using vars
    min_id := 0;
    count := 0;
    gr_id := 0;

    -- Group users rows by group_id
    FOR r IN SELECT * FROM users_temp ORDER BY id
    LOOP
      IF gr_id != r.group_id THEN
        IF gr_id != 0 THEN
          -- Return result
          RETURN NEXT;
        END IF;

        gr_id := r.group_id;
        min_id := r.id;
        count := 0;
      END IF;

      count := count + 1;
    END LOOP;

    -- Return last result
    IF gr_id != 0 THEN
      RETURN NEXT;
    END IF;
  END;
$$
LANGUAGE plpgsql;

-- Show result
SELECT * FROM group_users();
