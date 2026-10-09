<?php
function pqgiftshop_getmoduleinfo(){
	$info = array(
		"name"=>"PQ Gift Shop",
		"version"=>"2.22",
		"author"=>"`#Lonny Luberts",
		"category"=>"PQcomp",
		"download"=>"http://www.pqcomp.com/modules/mydownloads/visit.php?cid=3&lid=14",
		"vertxtloc"=>"http://www.pqcomp.com/",
		"settings"=>array(
			"PQ Gift Shop Module Settings,title",
			"gsloc"=>"Where does the Gift Shop appear,location|".getsetting("villagename", LOCATION_FIELDS),
			"gsowner"=>"Gift Shop Clerk Name,text|Paula",
			"gsheshe"=>"Gift Shop Owner Sex,text|she",
			"special"=>"Shop Specialty,text|sweetie",
			"If you use the word -Card- the gift show will assume it is a greeting card,note",
			"gift1"=>"Gift1,text|Four Leaf Clover",
			"gift1price"=>"Gift1 Price,int|0",
			"gift2"=>"Gift2,text|Christmas Card",
			"gift2price"=>"Gift2 Price,int|10",
			"gift3"=>"Gift3,text|Thank You Card",
			"gift3price"=>"Gift3 Price,int|20",
			"gift4"=>"Gift4,text|Birthday Card",
			"gift4price"=>"Gift4 Price,int|40",
			"gift5"=>"Gift5,text|FruitCake",
			"gift5price"=>"Gift5 Price,int|60",
			"gift6"=>"Gift6,text|Bottle of Eggnog",
			"gift6price"=>"Gift6 Price,int|100",
			"gift7"=>"Gift7,text|Box of Chocolates",
			"gift7price"=>"Gift7 Price,int|200",
			"gift8"=>"Gift8,text|Teddy Bear",
			"gift8price"=>"Gift8 Price,int|500",
			"gift9"=>"Gift9,text|Bouquet of Roses",
			"gift9price"=>"Gift9 Price,int|1000",
			"gift10"=>"Gift10,text|Snow Globe",
			"gift10price"=>"Gift10 Price,int|1500",
			"gift11"=>"Gift11,text|Gold Band",
			"gift11price"=>"Gift11 Price,int|2000",
			"gift12"=>"Gift12,text|24K Diamond Ring",
			"gift12price"=>"Gift12 Price,int|3000",
		),
	);
	return $info;
}

function pqgiftshop_install(){
	if (!is_module_active('pqgiftshop')){
		output_notl("`4Installing Gift Shop Module.`n");
	}else{
		output_notl("`4Updating Gift Shop Module.`n");
	}
	module_addhook("village");
	return true;
}

function pqgiftshop_uninstall(){
	output_notl("`4Un-Installing Gift Shop Module.`n");
	return true;
}

function pqgiftshop_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "village":
			if ($session['user']['location'] == get_module_setting("gsloc")){
				tlschema($args['schemas']['marketnav']);
    			addnav($args['marketnav']);
    			tlschema();
				addnav(array("%s's Gift Shop",get_module_setting('gsowner')), "runmodule.php?module=pqgiftshop");
			}
		break;
	}
	return $args;
}

function pqgiftshop_run(){
	global $session;
	$op = httpget('op');
	$gift=rawurldecode(httpget('op2'));
	$price=httpget('price');
	$name=httpget('name');
        $whom = httppost('whom');
        $whom = stripslashes(rawurldecode($whom));
        $mailmessage="";
        $shope="pqgiftshop";
        $rawwhom = $whom;
        $patternParts = str_split($whom);
        if (! empty($patternParts)) {
                $whom = '%' . implode('%', $patternParts) . '%';
        } else {
                $whom = '%';
        }
        $mess=httppost('mess');
	page_header(array("%s's Ol' Gifte Shoppe",sanitize(get_module_setting('gsowner'))));
	output("`c`b`&Ye Ol' Gifte Shoppe`0`b`c`n`n");
	require("./modules/pqgiftshop/pqgiftshop.php");
}
?>
