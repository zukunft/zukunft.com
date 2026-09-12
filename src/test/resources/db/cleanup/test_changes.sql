SELECT *
FROM changes
WHERE old_value LIKE 'System Test%'
   OR new_value LIKE 'System Test%';