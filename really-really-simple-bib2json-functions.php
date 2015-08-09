<?php 

$authorLastName = 'Chomsky';

date_default_timezone_set('America/Los_Angeles');

# assumes $students defined as comma-delimited list of student last names
function underlineStudents($cite) {
	foreach (explode(',',$students) as $student) {
		$cite = str_replace("$student,","<u>$student</u>,",$cite);
	}
	return $cite;
}

function convertAccents($bibtex) {
	$bibtex = str_replace('{\"o}','&ouml',$bibtex);
	$bibtex = str_replace('{\k a}','&#261;',$bibtex);
	$bibtex = str_replace("{\'a}",'&aacute;',$bibtex);
	$bibtex = str_replace("{\'e}",'&eacute;',$bibtex);
	$bibtex = str_replace("{\o}",'&oslash;',$bibtex);
	$bibtex = str_replace("{\'o}",'o&#769;',$bibtex);
	$bibtex = str_replace("{\^e}",'e&#770;',$bibtex);
	$bibtex = str_replace("{\`a}",'a&#768;',$bibtex);
	$bibtex = str_replace("{\~a}",'a&#771;',$bibtex);
	$bibtex = str_replace("{\c c}",'',$bibtex);
	return $bibtex;
}
# based largely on the simple solution by MaxArt:
# http://stackoverflow.com/questions/10768747/parse-bibtex-files
function bibtex2json($bibtex) {
	$json_txt = convertAccents($bibtex);
	$json_txt = preg_replace("/\%.*\n/","",$json_txt); # use bibtex structure to create json format
	$json_txt = preg_replace("/\"/","\\\"",$json_txt);
	$json_txt = preg_replace("/(\S+)\s*=\s*\{/","\"\\1\": \"",$json_txt);
	$json_txt = preg_replace("/\}(?=\s*[,\}])/","\"",$json_txt);
	$json_txt = preg_replace("/@(\S+)\s*\{([^,]*)/","{\"\\1\": \"\\2\"",$json_txt);
	return $json_txt;
}

function printAPA($json) {
	for ($i=0;$i<count(array_keys($json));$i++) {
		$json[strtolower(array_keys($json)[$i])]=$json[array_keys($json)[$i]];
	}
	switch (array_keys($json)[0]) {
		##################################################
		# ARTICLE 
		##################################################
		case 'article':
			$cite = renderAuthor($json['author']).' ('.$json['year'].
				'). '.$json['title'].'. <em>'.$json['journal'].'</em>';
			if (array_key_exists('volume', $json)) {
				$cite .= ', <em>'.$json['volume'].'</em>';
			} 
			if (array_key_exists('pages',$json)) {
				$json['pages'] = preg_replace('/\s*--\s*/','-',$json['pages']);
				$cite .= ', '.$json['pages'];
			}
			break;

		##################################################
		# PROCEEDINGS 
		##################################################
		case 'inproceedings':
			$cite = renderAuthor($json['author']).' ('.$json['year'].'). '.
				$json['title'].' In ';
			if (array_key_exists('editor', $json)) {
				$ed = '(Ed.)';
				if (count(explode(' and ',$json['editor'])>1)) {
					$ed = '(Eds.)';
				}
				$cite .= renderAuthor($json['editor'])." $ed, ";
			}
			$cite .= '<em>'.$json['booktitle'].'</em>';
			if (array_key_exists('pages', $json)) {
				$json['pages'] = preg_replace('/\s*--\s*/','-',$json['pages']);
				$cite .= ' (pp. '.$json['pages'].')';
			}
			if (array_key_exists('address', $json)) {
				$cite .= '. '.$json['address'].': '.$json['publisher'];
			}
			break;

		##################################################
		# CHAPTERS 
		##################################################
		case 'incollection':
			$cite = renderAuthor($json['author']).' ('.$json['year'].'). '.
				$json['title'].'. In ';
			if (array_key_exists('editor', $json)) {
				if (count(explode(' and ',$json['editor']))>1) {
					$ed = '(Eds.)';
				} else {
					$ed = '(Ed.)';
				}
				$cite .= renderAuthor($json['editor'])." $ed, ";
			}
			$cite .= '<em>'.$json['booktitle'].'</em>';
			if (array_key_exists('pages', $json)) {
				$json['pages'] = preg_replace('/\s*--\s*/','-',$json['pages']);
				$cite .= ' (pp. '.$json['pages'].'). ';
			} else { $cite .= '. '; }
			if (array_key_exists('address', $json)) {
				$cite .= $json['address'].': ';
			}
			$cite .= $json['publisher'];
			break;

		##################################################
		# GRANTS 
		##################################################
		case 'grant':
			$cite = renderAuthor($json['author']).' ('.$json['yeardisplay'].'). '.
				$json['title'].'. '.$json['amount'].'. '.$json['organization'];
			break;

		##################################################
		# SYMPOSIA 
		##################################################
		case 'symposium':
			$cite = renderAuthor($json['author']).' ('.$json['year'].'). '.
				$json['title'].'. '.$json['organization'];
			break;

		##################################################
		# CONFERENCE 
		##################################################
		case 'conference':
			$cite = renderAuthor($json['author']).' ('.$json['year'].', '.$json['month'].'). '.
				$json['title'].'. '.$json['organization'].'. '.$json['address'];;
			$json['day'] = '1';
			break;

		##################################################
		# INVITED TALKS 
		##################################################
		case 'talk':
			$cite = $json['title'].' ('.$json['month'].' '.$json['day'].', '.$json['year'].'). '.$json['organization'];
			break;

		##################################################
		# PRESS
		##################################################
		case 'url':
			$cite = $json['title'].' ('.$json['month'].' '.$json['day'].', '.$json['year'].'). '.$json['booktitle'];
			#$cite .= ". <span class=pressDescription>\"".$json['effort-description']."\"</span>";
			break;

		##################################################
		# BOOKS / SPECIAL ISSUES / ETC. 
		##################################################
		case 'book':
			$cite = renderAuthor($json['author']).' ('.$json['year'].'). '.
				$json['title'].'. <em>'.$json['publisher'].'</em>';
			if (array_key_exists('volume', $json)) {
				$cite .= ', <em>'.$json['volume'].'</em>';
			} 
			if (array_key_exists('number', $json)) {
				$cite .= '('.$json['number'].')';
			} 


	}
	$cite = str_replace("$authorLastName,","<b>$authorLastName</b>,",$cite);
	$cite = underlineStudents($cite);
	if (!is_numeric($json['year'])) {
		$json['year'] = '9999'; # to order...
	}
	$sortCode = strtotime($json['month'].' '.$json['day'].' '.$json['year']);
	$sortCode = substr('0'.$sortCode,-10);
	return '<!--'.$sortCode.$json['author']."-->$head$cite.$tail";
}
function renderAuthor($authString) { # can make simple assumptions about how you format your author names
	$arr = explode(' and ',$authString);
	$auth = '';
	if (count($arr)==1) {
		$auth = $arr[0];
	} else {
		for ($i=0;$i<count($arr);$i++) {
			if ($i==count($arr)-1) {
				 # remove all 'ands' but convert last one to ampersand (APA format)
				$auth = substr($auth,0,strlen($auth)-2).' &amp; '.$arr[$i];
			} else {
				$auth .= $arr[$i].', ';
			}
		}
	}
	return($auth);
}

function spitBib($classterm,$files) { # cycle through all entries of the file supplied; class term for css
	$citeList = [];
	foreach ($files as $file) {
		$fc = file_get_contents($file);
		$fc = bibtex2json($fc);
		foreach (explode("}",$fc) as $json_txt) {
			if (strlen(trim($json_txt))>0) {
				$ref = get_object_vars(json_decode($json_txt.'}'));
				$citeList[] = printAPA($ref);
			}
		}
	}
	rsort($citeList);
	for ($i=0;$i<count($citeList);$i++) {
		$citeList[$i] = "<div class=\"$classterm\">".$citeList[$i].'</div>';
	}
	foreach ($citeList as $string) {
		echo $string;
	}
}

?>






