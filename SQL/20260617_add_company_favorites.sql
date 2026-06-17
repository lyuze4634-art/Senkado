USE job_hunt_manager;

ALTER TABLE companies
  ADD COLUMN IF NOT EXISTS is_favorite TINYINT(1) NOT NULL DEFAULT 0
  AFTER company_status;
