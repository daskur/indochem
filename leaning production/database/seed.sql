USE ipla;

INSERT INTO news (title,content,image,category,author_id,status,published_at)
SELECT
'IPLA Learning Platform Update',
'New certificates are now generated automatically when users complete every step of an industrial process.',
NULL,
'Platform',
u.id,
'published',
NOW()
FROM users u WHERE u.email='admin@ipla.local'
AND NOT EXISTS (SELECT 1 FROM news WHERE title='IPLA Learning Platform Update');

INSERT INTO news (title,content,image,category,author_id,status,published_at)
SELECT
'Industrial Safety Reminder',
'Always review PPE requirements, equipment isolation procedures and process-specific hazards before any practical industrial activity.',
NULL,
'Safety',
u.id,
'published',
NOW()
FROM users u WHERE u.email='admin@ipla.local'
AND NOT EXISTS (SELECT 1 FROM news WHERE title='Industrial Safety Reminder');

INSERT INTO users (full_name,email,password,role)
VALUES ('IPLA Administrator','admin@ipla.local','$2y$10$jK.nqbiUOauPRbiITsTaFOoFm.gk5iEGIbyYTDMmIFzmTLVQ.oudK','admin')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), role='admin';

INSERT INTO processes (title,slug,category,short_description,description,status,created_by)
SELECT
    seed.title,
    seed.slug,
    seed.category,
    seed.short_description,
    seed.description,
    'published',
    (SELECT id FROM users WHERE email='admin@ipla.local' LIMIT 1)
FROM (
    SELECT 'Cement Production' AS title,'cement-production' AS slug,'Construction Materials' AS category,'Learn how limestone and clay are transformed into Portland cement.' AS short_description,'Cement production converts raw materials into clinker and finishes it as cement through crushing, grinding, heating, cooling and packaging.' AS description
    UNION ALL SELECT 'Bottled Water Production','bottled-water-production','Food and Beverage','Learn the treatment and bottling process for safe drinking water.','Water is screened, treated, filtered, disinfected, inspected, bottled and packaged under controlled quality and hygiene conditions.'
    UNION ALL SELECT 'Glass Production','glass-production','Materials','Learn how silica and mineral additives are melted into glass.','Glass production combines raw materials, melting, forming, annealing and inspection.'
    UNION ALL SELECT 'Synthetic Leather Production','synthetic-leather-production','Textiles','Learn the coating and finishing steps used to manufacture synthetic leather.','Synthetic leather is produced through fabric preparation, polymer coating, texture formation, curing and finishing.'
    UNION ALL SELECT 'Sugar Production','sugar-production','Food and Beverage','Follow cane or beet processing from extraction through crystallization.','Sugar production covers preparation, juice extraction, clarification, evaporation, crystallization and drying.'
    UNION ALL SELECT 'Plastic Production','plastic-production','Polymers','Explore polymer processing and common forming operations.','Plastic production involves polymerization, compounding, shaping, cooling and finishing.'
    UNION ALL SELECT 'Fertilizer Production','fertilizer-production','Agricultural Chemicals','Learn the industrial manufacture of plant nutrient products.','Fertilizer production combines nutrient sources, controlled reactions and granulation, drying, screening and packaging.'
    UNION ALL SELECT 'Paper Production','paper-production','Pulp and Paper','Trace wood fiber processing from pulping to finished paper.','Paper production includes pulping, screening, refining, sheet formation, pressing, drying and finishing.'
    UNION ALL SELECT 'Soap Production','soap-production','Personal Care','Learn how oils and alkali are transformed into soap.','Soap manufacturing includes reaction, mixing, forming, drying, finishing and packaging.'
    UNION ALL SELECT 'Paint Production','paint-production','Coatings','Learn pigment dispersion and paint formulation.','Paint production combines resin, pigments, solvents or water and additives through controlled mixing, dispersion and testing.'
    UNION ALL SELECT 'Ethanol Production','ethanol-production','Biofuels and Chemicals','Learn the fermentation and distillation route to ethanol.','Ethanol production includes feedstock preparation, hydrolysis or conversion, fermentation, distillation, dehydration and storage.'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM processes WHERE processes.slug = seed.slug);

INSERT INTO equipment (name,category,description,function,maintenance,safety_precautions)
VALUES
('Rotary Kiln','Thermal Processing','A long rotating refractory-lined furnace used for high-temperature processing.','Calcines raw meal and forms cement clinker.','Inspect refractory lining, seals, bearings and drive components routinely.','Use heat-protection PPE and isolate energy sources before maintenance.')
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO equipment (name,category,description,function,maintenance,safety_precautions)
VALUES
('Ball Mill','Size Reduction','A cylindrical mill that reduces material size using grinding media.','Grinds raw materials and cement clinker.','Inspect liners, bearings and lubrication systems regularly.','Lock out rotating drives and prevent access to moving parts.')
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO chemicals (name,chemical_formula,cas_number,physical_state,properties,uses,hazards,storage_methods,safety_information)
VALUES ('Calcium Oxide','CaO','1305-78-8','Solid','Highly reactive alkaline oxide, also called quicklime.','Used in cement, steelmaking and water treatment.','Causes severe irritation and heat on contact with moisture.','Store dry in sealed containers away from acids and moisture.','Wear eye protection, gloves and respiratory protection where dust exposure is possible.')
ON DUPLICATE KEY UPDATE properties=VALUES(properties);

INSERT INTO process_materials (process_id,material_name,description,purpose,quantity,unit)
SELECT p.id,'Limestone','Primary calcium-carbonate raw material.','Provides calcium oxide after calcination.',NULL,'metric tonne'
FROM processes p WHERE p.slug='cement-production'
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO process_steps (process_id,step_number,title,description,temperature,pressure,duration,input_material,output_material,safety_note,quality_note)
SELECT p.id,1,'Raw material extraction','Limestone, clay and corrective materials are quarried and transported to the plant.','Ambient','Ambient','Variable','Quarried minerals','Prepared feed materials','Use controlled excavation and transport procedures.','Check chemical composition and reject unsuitable feed.'
FROM processes p WHERE p.slug='cement-production'
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO process_steps (process_id,step_number,title,description,temperature,pressure,duration,input_material,output_material,equipment_used,safety_note,quality_note)
SELECT p.id,2,'Crushing and Grinding','Reduce raw materials to a uniform size suitable for blending and kiln processing.','Ambient','Ambient','Continuous','Crushed limestone and clay','Raw meal feed','Crusher and ball mill','Guard moving equipment and control dust exposure.','Verify particle size distribution and moisture.'
FROM processes p WHERE p.slug='cement-production'
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO process_steps (process_id,step_number,title,description,temperature,pressure,duration,input_material,output_material,equipment_used,safety_note,quality_note)
SELECT p.id,3,'Preheating and Calcination','Heat and partially decompose raw meal before clinker formation.','900-1450 °C','Near ambient','Continuous','Raw meal','Calcined material and clinker','Preheater and rotary kiln','Use thermal and gas-hazard controls.','Monitor free lime and kiln operating conditions.'
FROM processes p WHERE p.slug='cement-production'
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO process_steps (process_id,step_number,title,description,input_material,output_material,equipment_used,safety_note,quality_note)
SELECT p.id,4,'Clinker Grinding and Packaging','Cool clinker, blend gypsum, grind to cement fineness and package or dispatch in bulk.','Clinker and gypsum','Finished cement','Clinker cooler, ball mill and packer','Control hot surfaces and dust; isolate packer machinery.','Test fineness, setting time and compressive strength.'
FROM processes p WHERE p.slug='cement-production'
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO process_equipment (process_id,equipment_id,purpose)
SELECT p.id,e.id,'Processes and mills the cement raw materials and clinker.'
FROM processes p
JOIN equipment e ON e.name = 'Ball Mill'
WHERE p.slug='cement-production'
ON DUPLICATE KEY UPDATE purpose=VALUES(purpose);

INSERT INTO process_equipment (process_id,equipment_id,purpose)
SELECT p.id,e.id,'Performs high-temperature calcination and clinker formation.'
FROM processes p
JOIN equipment e ON e.name = 'Rotary Kiln'
WHERE p.slug='cement-production'
ON DUPLICATE KEY UPDATE purpose=VALUES(purpose);

INSERT INTO process_reactions (process_id,step_id,reaction_title,reactants,products,equation,conditions,description)
SELECT p.id,s.id,'Limestone calcination','Calcium carbonate','Calcium oxide and carbon dioxide','CaCO3 -> CaO + CO2','Approximately 900 °C and above','Calcination releases carbon dioxide and provides calcium oxide for clinker mineral formation.'
FROM processes p JOIN process_steps s ON s.process_id=p.id
WHERE p.slug='cement-production' AND s.step_number=3
ON DUPLICATE KEY UPDATE equation=VALUES(equation);

INSERT INTO glossary (term,definition,category)
VALUES
('Clinker','Partially fused nodules formed by heating cement raw meal in a kiln.','Cement')
ON DUPLICATE KEY UPDATE definition=VALUES(definition),
category=VALUES(category);

INSERT INTO glossary (term,definition,category) VALUES
('Calcination','Thermal decomposition of a material, such as limestone, to release volatile components and form an oxide.','Thermal Processing'),
('Raw Meal','Finely ground and blended raw materials prepared for feeding into a cement kiln or preheater.','Cement'),
('Molarity','The number of moles of solute dissolved per litre of solution.','Chemistry'),
('Normality','A concentration measure expressed as gram-equivalent weight of solute per litre of solution.','Chemistry'),
('Yield','The amount of product obtained compared with the theoretical or stoichiometric quantity.','Production'),
('Distillation','A separation process that uses differences in component volatility to separate or purify liquids.','Separation'),
('Fermentation','A biological conversion in which microorganisms transform sugars into products such as ethanol or organic acids.','Bioprocess'),
('Filtration','A separation operation that removes suspended solids from a fluid by passing it through a porous medium.','Separation'),
('Crystallization','Formation of solid crystals from a supersaturated solution, melt, vapor or other phase.','Production'),
('Refractory','A heat-resistant material used to line furnaces, kilns and other high-temperature equipment.','Materials'),
('PPE','Personal protective equipment worn to reduce exposure to workplace hazards.','Safety'),
('Lockout-Tagout','A safety procedure that isolates hazardous energy before maintenance or servicing begins.','Safety')
ON DUPLICATE KEY UPDATE definition=VALUES(definition), category=VALUES(category);

INSERT INTO process_flow_diagrams (process_id,title,diagram_image,description)
SELECT id,'Cement Production Process Flow','assets/images/cement-process-flow.svg','A visual overview of cement production from raw-material preparation through crushing, preheating, clinker formation, grinding and packaging.'
FROM processes
WHERE slug='cement-production'
AND NOT EXISTS (
    SELECT 1 FROM process_flow_diagrams
    WHERE process_flow_diagrams.process_id=processes.id
      AND process_flow_diagrams.title='Cement Production Process Flow'
);

INSERT INTO quizzes (process_id,title,description,passing_score,status)
SELECT id,'Cement Production Practice Quiz','Test your understanding of cement raw materials, kiln operations, grinding and quality control.',70,'published'
FROM processes
WHERE slug='cement-production'
AND NOT EXISTS (
    SELECT 1 FROM quizzes
    WHERE quizzes.process_id=processes.id
      AND quizzes.title='Cement Production Practice Quiz'
);

INSERT INTO quiz_questions (quiz_id,question,question_type,marks,explanation)
SELECT q.id,'What is the main calcium source in cement raw meal?','multiple_choice',1,'Limestone provides calcium carbonate, which is calcined to form calcium oxide.'
FROM quizzes q
WHERE q.title='Cement Production Practice Quiz'
AND NOT EXISTS (SELECT 1 FROM quiz_questions qq WHERE qq.quiz_id=q.id AND qq.question='What is the main calcium source in cement raw meal?');

INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Limestone',1 FROM quiz_questions qq
WHERE qq.question='What is the main calcium source in cement raw meal?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Limestone');
INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Gypsum',0 FROM quiz_questions qq
WHERE qq.question='What is the main calcium source in cement raw meal?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Gypsum');
INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Clay only',0 FROM quiz_questions qq
WHERE qq.question='What is the main calcium source in cement raw meal?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Clay only');

INSERT INTO quiz_questions (quiz_id,question,question_type,marks,explanation)
SELECT q.id,'Which equipment performs high-temperature clinker formation?','multiple_choice',1,'The rotary kiln provides the sustained high temperatures required for clinker reactions.'
FROM quizzes q
WHERE q.title='Cement Production Practice Quiz'
AND NOT EXISTS (SELECT 1 FROM quiz_questions qq WHERE qq.quiz_id=q.id AND qq.question='Which equipment performs high-temperature clinker formation?');

INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Rotary kiln',1 FROM quiz_questions qq
WHERE qq.question='Which equipment performs high-temperature clinker formation?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Rotary kiln');
INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Ball mill',0 FROM quiz_questions qq
WHERE qq.question='Which equipment performs high-temperature clinker formation?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Ball mill');
INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Dust collector',0 FROM quiz_questions qq
WHERE qq.question='Which equipment performs high-temperature clinker formation?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Dust collector');

INSERT INTO quiz_questions (quiz_id,question,question_type,marks,explanation)
SELECT q.id,'Calcination releases which gas from calcium carbonate?','multiple_choice',1,'Calcination converts CaCO3 into CaO and CO2.'
FROM quizzes q
WHERE q.title='Cement Production Practice Quiz'
AND NOT EXISTS (SELECT 1 FROM quiz_questions qq WHERE qq.quiz_id=q.id AND qq.question='Calcination releases which gas from calcium carbonate?');

INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Carbon dioxide',1 FROM quiz_questions qq
WHERE qq.question='Calcination releases which gas from calcium carbonate?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Carbon dioxide');
INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Oxygen',0 FROM quiz_questions qq
WHERE qq.question='Calcination releases which gas from calcium carbonate?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Oxygen');
INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Hydrogen',0 FROM quiz_questions qq
WHERE qq.question='Calcination releases which gas from calcium carbonate?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Hydrogen');

INSERT INTO quiz_questions (quiz_id,question,question_type,marks,explanation)
SELECT q.id,'Which product is tested for fineness, setting time and compressive strength?','multiple_choice',1,'These tests describe key finished-cement quality characteristics.'
FROM quizzes q
WHERE q.title='Cement Production Practice Quiz'
AND NOT EXISTS (SELECT 1 FROM quiz_questions qq WHERE qq.quiz_id=q.id AND qq.question='Which product is tested for fineness, setting time and compressive strength?');

INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Finished cement',1 FROM quiz_questions qq
WHERE qq.question='Which product is tested for fineness, setting time and compressive strength?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Finished cement');
INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Raw meal',0 FROM quiz_questions qq
WHERE qq.question='Which product is tested for fineness, setting time and compressive strength?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Raw meal');
INSERT INTO quiz_answers (question_id,answer,is_correct)
SELECT qq.id,'Quarry rock',0 FROM quiz_questions qq
WHERE qq.question='Which product is tested for fineness, setting time and compressive strength?'
AND NOT EXISTS (SELECT 1 FROM quiz_answers qa WHERE qa.question_id=qq.id AND qa.answer='Quarry rock');
