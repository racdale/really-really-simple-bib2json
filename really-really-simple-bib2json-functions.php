<?php 

function convertAccents($bibtex) { # latex > html conversion
	$bibtex = str_replace('{\"o}','&ouml',$bibtex); # example
	$bibtex = str_replace('\&','&amp;',$bibtex);
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

function printAPA($head,$json,$tail) { # some examples from Am. Psych. Assoc. format
	for ($i=0;$i<count(array_keys($json));$i++) { # get keys into lower case
		$json[strtolower(array_keys($json)[$i])]=$json[array_keys($json)[$i]];
	} 
	switch (array_keys($json)[0]) {

		#
		# very simple. just format using the json entries as you see fit... add willynilly
		#

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
		# BOOK 
		##################################################
		case 'book':
			$cite = renderAuthor($json['author']).' ('.$json['year'].'). <em>'.
				$json['title'].'</em>. ';
			if (array_key_exists('address', $json)) {
				$cite .= '. '.$json['address'].': ';
			}
			$cite .= $json['publisher'];
			break;
	}
	$cite = str_replace('Chomsky,','<b>Chomsky</b>,',$cite);

	return "$head$cite.$tail";
}

function renderAuthor($authString) { # can make simple assumptions about how you format your author names
	$arr = explode(' and ',$authString);
	$auth = '';
	if (count($arr)==1) {
		$auth = $arr[0];
	} else {
		for ($i=0;$i<count($arr);$i++) {
			if ($i==count($arr)-1 & count($arr)>1) {
				 # remove all 'ands' but convert last one to ampersand (APA format)
				$auth .= substr($auth,0,strlen($auth)-2).' &amp; '.$arr[$i];
			} else {
				$auth .= $arr[$i].', ';
			}
		}
	}
	return($auth);
}
function spitBib($classterm,$file) { # cycle through all entries of the file supplied; class term for css
	$fc = file_get_contents($file);
	$fc = bibtex2json($fc);
	$citeList = [];
	foreach (explode("}",$fc) as $json_txt) {
		if (strlen(trim($json_txt))>0) {
			$ref = get_object_vars(json_decode($json_txt.'}'));
			$citeList[] = printAPA("<div class=\"$classterm\">",$ref,'</div>');
		}
	}
	arsort($citeList);
	foreach ($citeList as $string) {
		echo $string;
	}
}

?>