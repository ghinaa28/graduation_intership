-- Populate Equipment Categories
INSERT IGNORE INTO equipment_categories (category_name, icon_class, status) VALUES
('Laboratory Equipment', 'fas fa-flask', 'active'),
('Scientific Instruments', 'fas fa-microscope', 'active'),
('Safety Equipment', 'fas fa-hard-hat', 'active'),
('Measurement Tools', 'fas fa-ruler', 'active'),
('Training Equipment', 'fas fa-chalkboard-teacher', 'active'),
('Presentation Tools', 'fas fa-projector', 'active'),
('Audio/Video Equipment', 'fas fa-video', 'active'),
('Furniture', 'fas fa-chair', 'active'),
('Networking Equipment', 'fas fa-network-wired', 'active'),
('Workshop Tools', 'fas fa-tools', 'active'),
('Power Tools', 'fas fa-bolt', 'active'),
('Hand Tools', 'fas fa-wrench', 'active'),
('Lighting', 'fas fa-lightbulb', 'active'),
('Event Equipment', 'fas fa-calendar-alt', 'active'),
('Office Equipment', 'fas fa-print', 'active'),
('Printing Equipment', 'fas fa-print', 'active');

-- Populate Equipment Items
INSERT IGNORE INTO equipment_items (category_id, item_name, description, standard_price, unit_type, status) VALUES
-- Laboratory Equipment
((SELECT category_id FROM equipment_categories WHERE category_name = 'Laboratory Equipment'), 'Microscope', 'High-quality optical microscope for research', 15.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Laboratory Equipment'), 'Centrifuge', 'Laboratory centrifuge for sample separation', 25.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Laboratory Equipment'), 'Autoclave', 'Sterilization equipment', 20.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Laboratory Equipment'), 'Fume Hood', 'Safety fume hood for chemical work', 30.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Laboratory Equipment'), 'Lab Bench', 'Sturdy laboratory workbench', 10.00, 'per_hour', 'active'),

-- Scientific Instruments
((SELECT category_id FROM equipment_categories WHERE category_name = 'Scientific Instruments'), 'Spectrophotometer', 'UV-Vis spectrophotometer', 40.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Scientific Instruments'), 'pH Meter', 'Digital pH measurement device', 8.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Scientific Instruments'), 'Balance Scale', 'Precision analytical balance', 12.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Scientific Instruments'), 'Thermocycler', 'PCR thermal cycler', 35.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Scientific Instruments'), 'Incubator', 'Laboratory incubator', 18.00, 'per_hour', 'active'),

-- Safety Equipment
((SELECT category_id FROM equipment_categories WHERE category_name = 'Safety Equipment'), 'Safety Goggles', 'Protective eyewear set', 2.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Safety Equipment'), 'Lab Coat', 'Protective laboratory coat', 3.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Safety Equipment'), 'Safety Gloves', 'Disposable safety gloves pack', 1.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Safety Equipment'), 'Fire Extinguisher', 'Laboratory fire safety equipment', 5.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Safety Equipment'), 'First Aid Kit', 'Complete first aid kit', 4.00, 'per_hour', 'active'),

-- Measurement Tools
((SELECT category_id FROM equipment_categories WHERE category_name = 'Measurement Tools'), 'Digital Caliper', 'Precision measuring tool', 5.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Measurement Tools'), 'Multimeter', 'Electrical measurement device', 8.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Measurement Tools'), 'Oscilloscope', 'Electronic test instrument', 30.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Measurement Tools'), 'Thermometer', 'Digital thermometer', 2.00, 'per_hour', 'active'),

-- Training Equipment
((SELECT category_id FROM equipment_categories WHERE category_name = 'Training Equipment'), 'Whiteboard', 'Large whiteboard for training', 8.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Training Equipment'), 'Flip Chart', 'Portable flip chart stand', 5.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Training Equipment'), 'Training Materials', 'Printed training materials set', 10.00, 'per_day', 'active'),

-- Presentation Tools
((SELECT category_id FROM equipment_categories WHERE category_name = 'Presentation Tools'), 'Projector', 'HD projector for presentations', 25.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Presentation Tools'), 'Projection Screen', 'Retractable projection screen', 8.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Presentation Tools'), 'Laser Pointer', 'Presentation laser pointer', 2.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Presentation Tools'), 'Wireless Presenter', 'Remote presentation control', 5.00, 'per_hour', 'active'),

-- Audio/Video Equipment
((SELECT category_id FROM equipment_categories WHERE category_name = 'Audio/Video Equipment'), 'Sound System', 'Complete PA sound system', 40.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Audio/Video Equipment'), 'Microphone Set', 'Wireless microphone system', 15.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Audio/Video Equipment'), 'Video Camera', 'HD video recording camera', 30.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Audio/Video Equipment'), 'Speakers', 'Professional speakers', 20.00, 'per_hour', 'active'),

-- Furniture
((SELECT category_id FROM equipment_categories WHERE category_name = 'Furniture'), 'Conference Table', 'Large conference table', 15.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Furniture'), 'Office Chairs', 'Ergonomic office chairs (set of 10)', 25.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Furniture'), 'Training Desks', 'Portable training desks (set of 20)', 30.00, 'per_hour', 'active'),

-- Networking Equipment
((SELECT category_id FROM equipment_categories WHERE category_name = 'Networking Equipment'), 'WiFi Router', 'High-speed WiFi router', 10.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Networking Equipment'), 'Network Switch', 'Ethernet network switch', 12.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Networking Equipment'), 'Cables & Connectors', 'Network cables and connectors set', 5.00, 'per_hour', 'active'),

-- Workshop Tools
((SELECT category_id FROM equipment_categories WHERE category_name = 'Workshop Tools'), 'Drill Press', 'Bench drill press', 20.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Workshop Tools'), 'Table Saw', 'Professional table saw', 25.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Workshop Tools'), 'Workbench', 'Heavy-duty workbench', 12.00, 'per_hour', 'active'),

-- Power Tools
((SELECT category_id FROM equipment_categories WHERE category_name = 'Power Tools'), 'Circular Saw', 'Power circular saw', 15.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Power Tools'), 'Angle Grinder', 'Power angle grinder', 12.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Power Tools'), 'Power Drill', 'Cordless power drill', 10.00, 'per_hour', 'active'),

-- Hand Tools
((SELECT category_id FROM equipment_categories WHERE category_name = 'Hand Tools'), 'Tool Set', 'Complete hand tool set', 8.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Hand Tools'), 'Wrench Set', 'Professional wrench set', 5.00, 'per_hour', 'active'),

-- Lighting
((SELECT category_id FROM equipment_categories WHERE category_name = 'Lighting'), 'LED Lights', 'Professional LED lighting setup', 15.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Lighting'), 'Stage Lights', 'Stage lighting system', 30.00, 'per_hour', 'active'),

-- Event Equipment
((SELECT category_id FROM equipment_categories WHERE category_name = 'Event Equipment'), 'Stage Setup', 'Complete stage setup', 50.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Event Equipment'), 'Tables & Chairs', 'Event tables and chairs (set of 50)', 60.00, 'per_hour', 'active'),

-- Office Equipment
((SELECT category_id FROM equipment_categories WHERE category_name = 'Office Equipment'), 'Printer', 'Multifunction printer', 8.00, 'per_hour', 'active'),
((SELECT category_id FROM equipment_categories WHERE category_name = 'Office Equipment'), 'Scanner', 'Document scanner', 5.00, 'per_hour', 'active');

-- Create Equipment Packages for different place types
-- Laboratory Package
INSERT IGNORE INTO equipment_packages (package_name, package_description, package_type, status) VALUES
('Laboratory Package', 'Complete laboratory equipment package for research and experiments', 'predefined', 'active');

SET @lab_package_id = (SELECT package_id FROM equipment_packages WHERE package_name = 'Laboratory Package' LIMIT 1);

INSERT IGNORE INTO package_items (package_id, item_id, quantity) VALUES
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Microscope'), 2),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Centrifuge'), 1),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Fume Hood'), 1),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Lab Bench'), 4),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'pH Meter'), 2),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Balance Scale'), 1),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Safety Goggles'), 10),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Lab Coat'), 10),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Safety Gloves'), 20),
(@lab_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'First Aid Kit'), 1);

-- Training Room Package
INSERT IGNORE INTO equipment_packages (package_name, package_description, package_type, status) VALUES
('Training Room Package', 'Complete training room equipment package', 'predefined', 'active');

SET @training_package_id = (SELECT package_id FROM equipment_packages WHERE package_name = 'Training Room Package' LIMIT 1);

INSERT IGNORE INTO package_items (package_id, item_id, quantity) VALUES
(@training_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Projector'), 1),
(@training_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Projection Screen'), 1),
(@training_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Whiteboard'), 2),
(@training_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Flip Chart'), 1),
(@training_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Training Desks'), 1),
(@training_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Office Chairs'), 1),
(@training_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Microphone Set'), 1),
(@training_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'WiFi Router'), 1);

-- Conference Room Package
INSERT IGNORE INTO equipment_packages (package_name, package_description, package_type, status) VALUES
('Conference Room Package', 'Complete conference room equipment package', 'predefined', 'active');

SET @conference_package_id = (SELECT package_id FROM equipment_packages WHERE package_name = 'Conference Room Package' LIMIT 1);

INSERT IGNORE INTO package_items (package_id, item_id, quantity) VALUES
(@conference_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Projector'), 1),
(@conference_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Projection Screen'), 1),
(@conference_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Conference Table'), 1),
(@conference_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Office Chairs'), 1),
(@conference_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Microphone Set'), 1),
(@conference_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'WiFi Router'), 1),
(@conference_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Whiteboard'), 1);

-- Workshop Space Package
INSERT IGNORE INTO equipment_packages (package_name, package_description, package_type, status) VALUES
('Workshop Space Package', 'Complete workshop equipment package', 'predefined', 'active');

SET @workshop_package_id = (SELECT package_id FROM equipment_packages WHERE package_name = 'Workshop Space Package' LIMIT 1);

INSERT IGNORE INTO package_items (package_id, item_id, quantity) VALUES
(@workshop_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Workbench'), 2),
(@workshop_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Table Saw'), 1),
(@workshop_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Power Drill'), 2),
(@workshop_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Tool Set'), 2),
(@workshop_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Safety Goggles'), 5),
(@workshop_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'First Aid Kit'), 1);

-- Event Hall Package
INSERT IGNORE INTO equipment_packages (package_name, package_description, package_type, status) VALUES
('Event Hall Package', 'Complete event hall equipment package', 'predefined', 'active');

SET @event_package_id = (SELECT package_id FROM equipment_packages WHERE package_name = 'Event Hall Package' LIMIT 1);

INSERT IGNORE INTO package_items (package_id, item_id, quantity) VALUES
(@event_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Sound System'), 1),
(@event_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Microphone Set'), 1),
(@event_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Stage Lights'), 1),
(@event_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Stage Setup'), 1),
(@event_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Tables & Chairs'), 1),
(@event_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Projector'), 1),
(@event_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Projection Screen'), 1);

-- Office Space Package
INSERT IGNORE INTO equipment_packages (package_name, package_description, package_type, status) VALUES
('Office Space Package', 'Complete office space equipment package', 'predefined', 'active');

SET @office_package_id = (SELECT package_id FROM equipment_packages WHERE package_name = 'Office Space Package' LIMIT 1);

INSERT IGNORE INTO package_items (package_id, item_id, quantity) VALUES
(@office_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Conference Table'), 1),
(@office_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Office Chairs'), 1),
(@office_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Printer'), 1),
(@office_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Scanner'), 1),
(@office_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'WiFi Router'), 1),
(@office_package_id, (SELECT item_id FROM equipment_items WHERE item_name = 'Whiteboard'), 1);
