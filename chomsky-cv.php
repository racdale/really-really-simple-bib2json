
<div class=fullOn>

<div class=cvTitle>Curriculum Vitae</div>
<div class=name>Noam Chomsky</div>
<div class=affiliation>MIT</div>

</div> <!--top full on-->

<?php 


include('really-really-simple-bib2json-functions.php');

echo '<div class=sectionHeader>Selected Publications</div>';
# function spitBib($classterm,$files,$authorLastName,$students,$searchterm) {
spitBib('article pub',array("examples.bib","examples2.bib"),'Chomsky','','');

?>


