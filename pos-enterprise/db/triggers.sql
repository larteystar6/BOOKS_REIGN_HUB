USE pos_enterprise;
DELIMITER $$
CREATE TRIGGER product_images_after_insert
AFTER INSERT ON product_images
FOR EACH ROW
BEGIN
  INSERT INTO audit_logs (uuid, entity, entity_id, action, user_uuid, new_value)
  VALUES (UUID(), 'product_images', NEW.uuid, 'create', NEW.created_by, JSON_OBJECT('filename', NEW.filename, 'product_uuid', NEW.product_uuid));
END$$

CREATE TRIGGER product_images_after_update
AFTER UPDATE ON product_images
FOR EACH ROW
BEGIN
  INSERT INTO audit_logs (uuid, entity, entity_id, action, user_uuid, old_value, new_value)
  VALUES (UUID(), 'product_images', NEW.uuid, 'update', NEW.created_by, JSON_OBJECT('filename', OLD.filename), JSON_OBJECT('filename', NEW.filename));
END$$

DELIMITER ;
