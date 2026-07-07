-- Non-destructive backfill: copy legacy single-file paths into new multi-file tables.
-- Does NOT drop or alter assignments.attachment_path or assignment_submissions.file_path.

INSERT INTO assignment_attachments (assignment_id, title, file_path, original_filename, sort_order)
SELECT a.id,
       COALESCE(NULLIF(TRIM(SUBSTRING_INDEX(a.attachment_path, '/', -1)), ''), 'Attachment'),
       a.attachment_path,
       SUBSTRING_INDEX(a.attachment_path, '/', -1),
       0
FROM assignments a
WHERE a.attachment_path IS NOT NULL
  AND TRIM(a.attachment_path) <> ''
  AND NOT EXISTS (
    SELECT 1 FROM assignment_attachments aa
    WHERE aa.assignment_id = a.id AND aa.file_path = a.attachment_path
  );

INSERT INTO assignment_submission_files (submission_id, title, file_path, original_filename, sort_order)
SELECT s.id,
       COALESCE(NULLIF(TRIM(s.original_filename), ''), NULLIF(TRIM(SUBSTRING_INDEX(s.file_path, '/', -1)), ''), 'Uploaded file'),
       s.file_path,
       s.original_filename,
       0
FROM assignment_submissions s
WHERE s.file_path IS NOT NULL
  AND TRIM(s.file_path) <> ''
  AND NOT EXISTS (
    SELECT 1 FROM assignment_submission_files sf
    WHERE sf.submission_id = s.id AND sf.file_path = s.file_path
  );
