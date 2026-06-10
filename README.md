Use sys_get_temp_dir() to safely generate a temp file path
Separate concerns: one method gets the client, one gets content, one gets a presigned URL (they were blended)
Throw or return consistently rather than mixing echo + silent null returns
Add proper return types and docblocks
Cleaner credential reading
