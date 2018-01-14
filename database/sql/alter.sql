-- 修正SQL文
ALTER TABLE page ADD INDEX title(title);
ALTER TABLE activity ADD INDEX page_id (page_id);
ALTER TABLE activity ADD INDEX groupby_index (page_id, user_id);
