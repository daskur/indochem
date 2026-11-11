<?php
$safetyTitle='Laboratory Hazards';
$safetyNavigation='<a href="#chemical">Chemical</a><a href="#physical">Physical</a><a href="#biological">Biological</a><a href="#electrical">Electrical</a><a href="#risk-control">Risk Control</a>';
$safetyContent='
<section id="chemical"><h2>Chemical Hazards</h2><p>Hazards include corrosives, toxic substances, oxidizers, flammable liquids, gases and reactive materials. Read the Safety Data Sheet and label before handling. Never mix chemicals unless a verified procedure authorizes it.</p></section>
<section id="physical"><h2>Physical Hazards</h2><p>Hot surfaces, pressure vessels, moving equipment, sharp objects, noise and high temperatures require engineering controls, guarding and task-specific PPE. Allow systems to cool and isolate stored energy before maintenance.</p></section>
<section id="biological"><h2>Biological Hazards</h2><p>Prevent exposure through approved containment, sanitation, sharps control and waste management. Treat all biological samples according to institutional procedures.</p></section>
<section id="electrical"><h2>Electrical Hazards</h2><p>Keep liquids away from energized equipment, inspect leads and plugs, and do not bypass protective devices. Qualified personnel should perform repairs after lockout and isolation.</p></section>
<section id="risk-control"><h2>Risk Control Hierarchy</h2><ol><li>Eliminate the hazard.</li><li>Substitute a less hazardous method or material.</li><li>Use engineering controls.</li><li>Apply administrative controls and procedures.</li><li>Use PPE as the final protective layer.</li></ol></section>';
require __DIR__.'/safety_page.php';
