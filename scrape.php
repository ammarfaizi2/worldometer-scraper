<?php

echo "Downloading data...\n";

$ch = curl_init("https://www.worldometers.info/coronavirus/");
$opt = [
	CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:92.0) Gecko/20100101 Firefox/92.0",
	CURLOPT_RETURNTRANSFER => true
];
curl_setopt_array($ch, $opt);
$html = curl_exec($ch);
curl_close($ch);

// $html = file_get_contents("test.html");

if (!preg_match("/<table id=\"main_table_countries_today\"(.+?)<\/table>/s", $html, $m)) {
	echo "Can't find main table\n";
	exit;
}

$html = $m[1];

$patt = "/<\/tr>\n<tr style=\"\">\n<td style=\"font-size:12px;color: grey;text-align:center;vertical-align:middle;\">(.+?)<\/tr>/s";

if (!preg_match_all($patt, $html, $m)) {
	echo "Can't find country rows\n";
	exit;
}


echo "Country Name", "\t";
echo "CMT", "\t";
echo "FST", "\t";
echo "SDT", "\t";
echo "Active CMT", "\n";
foreach ($m[1] as $v) {
	$data = extract_country_data($v);

	if (!$data)
		continue;

	echo $data["country_name"], "\t";
	echo $data["cmt"], "\t";
	echo $data["fst"], "\t";
	echo $data["sdt"], "\t";
	echo $data["active_cmt"], "\n";
}


function clean_num(string $data): int
{
	$data = trim(str_replace(",", "", $data));
	if (is_numeric($data)) {
		return (int)$data;
	}

	return -1;
}


function clean_country_name(string $name): string
{
	return html_entity_decode($name, ENT_QUOTES, "UTF-8");
}


function extract_country_data(string $html): ?array
{
	if (!preg_match("/<a class=\"mt_a\" href=\"country\/.+?\/\">(.+?)<\/a>/s", $html, $m)) {
		return NULL;
	}
	$countryName = $m[1];

	if (!preg_match_all("/<td style=\".+?\">(.*?)<\/td>/s", $html, $m)) {
		return NULL;
	}

	$m = $m[1];

	$cmt = $m[1];
	$fst = $m[3];
	$sdt = $m[5];
	$activeCmt = $m[7];

	return [
		"country_name" => clean_country_name($countryName),
		"cmt" => clean_num($cmt),
		"fst" => clean_num($fst),
		"sdt" => clean_num($sdt),
		"active_cmt" => clean_num($activeCmt)
	];
}




// <table>
// ...
// </table>
