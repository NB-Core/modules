<?php

/*interface to make it compatible for old modules*/
function marriage_getmoduleinfo() {
	$class=new marriage;
	return $class->getmoduleinfo();
	unset($class);
	return;
}

function marriage_install() {
	$class=new marriage;
	$class->install();
	unset($class);
	return;
}

function marriage_uninstall() {
	$class=new marriage;
	$class->uninstall();
	unset($class);
	return;
}

function marriage_dohook($hookname,$args) {
	$class=new marriage;
	return $class->do_hook($hookname,$args);
	unset($class);
	return;
}

function marriage_run() {
	$class=new marriage;
	$class->run();
	unset($class);
	return;
}

/*end of legacy support*/
if (!interface_exists("module_base")) {
	interface module_base {
		public function getmoduleinfo();
		public function install ();
		public function uninstall();
		public function do_hook($hookname,$args);
		public function run();
	}
}

class marriage implements module_base {

	private $ringgiver,$chapelwatcher,$buffrounds,$readingguy;

	function __construct() {
			require("modules/marriage/constants.php");
			$this->ringgiver=$ringgiver;
			$this->chapelwatcher=$chapelwatcher;
			$this->buffrounds=$buffrounds;
			$this->readingguy=$readingguy;	
	}
	
	public function getmoduleinfo() {
		$info= array(
			"name"=>"Marriage",
			"version"=>"1.1",
			"author"=>"`2Oliver Brendel",
			"category"=>"Marriage",
			"download"=>"",
			"settings"=>array(
				"Flirt Settings, title",
				"maxflirts"=>"Maximum number of flirts per day,range,0,50,1|0",
				"Marriage Settings,title",
				"m_pointsneeded"=>"Average points needed to get engaged?,range,0,11|8",
				"m_minflirts"=>"Minimum flirts needed to get engaged?,range,1,12|5",
				"(range 0-12 where 12 is 'only supergood flirts' and hence unlikely to achieve. Be reasonable),note",
				"m_wordcount"=>"Word count to enable engagement,range,0,10000,100|2000",
				"mc_wordcount"=>"Word count to enable marriage,range,0,30000,100|8000",
				),
			"prefs"=>array(
				"Marriage,title",
				"user_spousesecret"=>"`4Show spouse in Bio?,bool|1",
				"fiancee"=>"Engaged with whom?",
				"flirts_today"=>"How many times flirted today?,range,0,50,1|0",
				"ignored"=>"Who has this user ignored,viewonly",
				"marry"=>"Marriage Status (0=nothing 1=ready for marriage 2=accepted),range,0,2,1|0",
				),
			);
		return $info;	
	}
	public function install() {
	
	/*hooks*/
		module_addhook("moderate");
	//	module_addhook("drinks-text");
	//	module_addhook("drinks-check");
		module_addhook("newday");
		module_addhook_priority("footer-inn",1);
		module_addhook("gardens");
		module_addhook_priority("delete_character",1);
		module_addhook("charstats");
		module_addhook("biostat");
	
	/* Table generations*/
		$optout=array(
			'acctid'=>array('name'=>'acctid', 'type'=>'int(11) unsigned'),
			'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'acctid'),
		);
		$flirts=array(
			'from'=>array('name'=>'initiator', 'type'=>'int(11) unsigned'),
			'to'=>array('name'=>'receiver', 'type'=>'int(11) unsigned'),
			'flirtpoints'=>array('name'=>'flirtpoints', 'type'=>'int(11) unsigned'),
			'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'initiator'),
			'key-one'=> array('name'=>'receiver', 'type'=>'key', 'unique'=>'0', 'columns'=>'receiver'),
		);
		$proposals=array(
			'from'=>array('name'=>'initiator', 'type'=>'int(11) unsigned'),
			'to'=>array('name'=>'proposed_to', 'type'=>'int(11) unsigned'),
			'ring'=>array('name'=>'ring', 'type'=>'int(4) unsigned', 'default'=>'0'),
			'propose'=>array('name'=>'propose', 'type'=>'text', 'default'=>''),
			'response'=>array('name'=>'response', 'type'=>'text', 'default'=>''),
			'accepted'=>array('name'=>'accepted', 'type'=>'int(2) unsigned', 'default'=>0),
			'date'=>array('name'=>'date', 'type'=>'datetime','default'=>'1970-01-01 00:00:00'),
			'responsedate'=>array('name'=>'responsedate', 'type'=>'datetime','default'=>'1970-01-01 00:00:00'),
			'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'initiator,proposed_to,date'),
			'key-one'=> array('name'=>'receiver', 'type'=>'key', 'unique'=>'0', 'columns'=>'date'),
		);
		$ignored=array(
			'player'=>array('name'=>'player', 'type'=>'int(11) unsigned'),
			'target'=>array('name'=>'target', 'type'=>'int(11) unsigned'),
			'reason'=>array('name'=>'reason', 'type'=>'varchar(1000)', 'default'=>''),
			'date'=>array('name'=>'date', 'type'=>'datetime','default'=>'1970-01-01 00:00:00'),
			'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'player,target'),
			
		);
		$tries=array(
			'id'=>array('name'=>'id', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
			'from'=>array('name'=>'initiator', 'type'=>'int(11) unsigned'),
			'to'=>array('name'=>'receiver', 'type'=>'int(11) unsigned'),
			'message'=>array('name'=>'message', 'type'=>'varchar(4000)', 'default'=>''), //php5 only up to 65k
			'response' =>array('name'=>'response', 'type'=>'varchar(4000)', 'default'=>''),
			'successful'=>array('name'=>'successful', 'type'=>'tinyint(4)','default'=>0),
			'date'=>array('name'=>'date', 'type'=>'datetime','default'=>'1970-01-01 00:00:00'),
			'responsedate'=>array('name'=>'responsedate', 'type'=>'datetime','default'=>'1970-01-01 00:00:00'),
			'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'id'),
			'key-one'=> array('name'=>'initiator', 'type'=>'key', 'unique'=>'0', 'columns'=>'initiator'),
			'key-two'=> array('name'=>'receiver', 'type'=>'key', 'unique'=>'0', 'columns'=>'receiver'),
			'key-three'=> array('name'=>'successful', 'type'=>'key', 'unique'=>'0', 'columns'=>'successful'),
		);
		require_once("lib/tabledescriptor.php");
		synctable(db_prefix("marriage_flirtpoints"), $flirts, true);
		synctable(db_prefix("marriage_proposals"), $proposals, true);
		synctable(db_prefix("marriage_actions"), $tries, true);
		synctable(db_prefix("marriage_optout"), $optout, true);
		synctable(db_prefix("marriage_ignorelist"), $ignored, true);
		return true;
	}
	
	public function uninstall() {
	}
	
	public function do_hook($hookname,$args) {
		$hookname=str_replace("-","_",$hookname); //need to do this as - breaks in function names
		$method="hook_".$hookname;
		if (method_exists($this,$method)) $args=$this->$method($args);
		return $args;
	}
	
	private function hook_gardens($args) {
		addnav("Love & Lust");
		addnav("`)S`%hadowy `xMeadows","runmodule.php?module=marriage&op=meadows");
		return $args;
	}
	
	private function hook_biostat($args) {
		global $session;
		$sql="SELECT marriedto FROM ".db_prefix('accounts')." WHERE acctid=".$args['acctid'].";";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		$fiancee=(int)get_module_pref('fiancee','marriage',$args['acctid']);
		if (((int)$row['marriedto'])!=0) {
			if (!get_module_pref('user_spousesecret','marriage',$args['acctid'])) {
				$partner="`iSecret`i";
			} else {
				$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=".$row['marriedto'].";";
				$result=db_query_cached($sql,"spouse-".$row['marriedto']);
				$row2=db_fetch_assoc($result);
				$partner=$row2['name'];
				if ($row['marriedto']==INT_MAX) {
					require_once("lib/partner.php");
					$partner=get_partner(false);
					$partner="`iSecret`i";
				}
			}
			output("`^Spouse: `2%s`n",$partner);
		} elseif ($fiancee!=0) {
			if (!get_module_pref('user_spousesecret','marriage',$args['acctid'])) {
				$partner="`iSecret`i";
			} else {
				$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=".get_module_pref('fiancee','marriage',$args['acctid']).";";
				$result=db_query($sql);
				$fiancee=db_fetch_assoc($result);
				output("`^Fiancée: `2%s`n",$fiancee['name']);
			}
			
		}
		return $args;
	}
	
	private function hook_moderate($args) {
		$args['marriage'] = 'The Chapel';
		for ($i=1;$i<4;$i++) {
			$args["meadows-$i"] = "The Shadowy Meadows $i";
		}
		$args['mead-gamecorner'] = 'The Shadowy Meadows - Gamecorner';
		return $args;
	}
	
	private function hook_footer_inn($args) {
		if (httpget('op')=='' && ((int)$session['user']['marriedto'])!=0 && ((int)$session['user']['marriedto'])!=INT_MAX && is_module_active('lovers')) {
			addnav("Things to do");
			blocknav("runmodule.php?module=lovers&op=flirt",true);
			require_once('lib/partner.php');
			$partner=get_partner();
			//addnav(array("F?Flirt with %s`0",$partner),"runmodule.php?module=marriage&op=innflirt");
		}
		return $args;
	}
	
	private function hook_charstats($args) {
		global $session;
		$fiancee=get_module_pref('fiancee','marriage');
		if (((int)$session['user']['marriedto'])!=0) {
			require_once("lib/partner.php");
			$partner=get_partner(true);
			setcharstat("Personal Info","Spouse","`^".$partner);
		} elseif ($fiancee>0) {
			$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=$fiancee;";
			$result=db_query_cached($sql,"fiancee-".$session['user']['acctid']);
			$row=db_fetch_assoc($result);
			setcharstat("Personal Info","Fiancée","`^".$row['name']);
		}
		return $args;
	}
	
	private function hook_delete_character($args) {
		global $session;
		
		$acctid=$args['acctid'];
		
		$sql="SELECT * FROM ".db_prefix('accounts')." WHERE acctid=$acctid LIMIT 1;";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		$min=0;
		$sql="SELECT * FROM ".db_prefix('marriage_flirtpoints')." WHERE flirtpoints>$min AND receiver=$acctid OR initiator=$acctid";
		//select all above a certain level to notify of the tragic death of the player
		$result=db_query($sql);
		$notify=array();
		//add spouse
		if ($row['marriedto']!=0 || $row['marriedto']!=INT_MAX) $notify[]=$row['marriedto'];
		//add others
		$fiancee=(int)get_module_pref('fiancee','marriage',$acctid);
		if ($fiancee!=0) $notify[]=$fiancee;
		while ($rowtwo=db_fetch_assoc($result)) {
			if ($rowtwo['initiator']==$acctid) {
				$notify[]=$rowtwo['receiver'];
			} else $notify[]=$rowtwo['initiator'];
		}
		$subject=translate_inline("`\$Tragic Events!");
		$message=array(translate_inline("`4I am very sorry to notify you about tragic events that have occurred! %s`4 has last been seen entering the woods and has not been seen since! We have not given up hope, but we fear for the worst."),$row['name']);
		require_once('lib/systemmail.php');
		foreach ($notify as $to) {
			systemmail($to,$subject,$message);
		}
		
		/*set the user and spouse to "unmarried", he has to marry again, else we have problems... one married, the other not*/
		$sql="UPDATE ".db_prefix('accounts')." SET marriedto=0 WHERE acctid={$row['marriedto']} OR acctid={$row['acctid']} LIMIT 1;";
		db_query($sql);
		
		/*kill possible fiancee*/
		if ($fiancee!=0) {
			set_module_pref('fiancee',0,'marriage',$row['acctid']);
			set_module_pref('fiancee',0,'marriage',$fiancee);
		}
		
		/*restore info*/
		$sql="SELECT * FROM ".db_prefix('marriage_actions')." WHERE initiator=$acctid OR receiver=$acctid";
		$result=db_query($sql);
		$save='';
		$fields='';
		while ($row=db_fetch_assoc($result)) {
			if ($fields=='') $fields=array_keys($row);
			$save.="INSERT INTO ".db_prefix('marriage_actions')." (".implode(",",$fields)." VALUES ('".addslashes(implode("','",$row))."');";
		}
		set_module_pref('restoresave',$save,'marriage');
		/*save all flirt info except ignores*/

		$sql="DELETE FROM ".db_prefix('marriage_optout')." WHERE acctid=$acctid";
		db_query($sql);
		$sql="DELETE FROM ".db_prefix('marriage_flirtpoints')." WHERE initiator=$acctid OR receiver=$acctid";
		db_query($sql);
		$sql="DELETE FROM ".db_prefix('marriage_actions')." WHERE initiator=$acctid OR receiver=$acctid";
		db_query($sql);
		$sql="DELETE FROM ".db_prefix('marriage_ignorelist')." WHERE target=$acctid OR player=$acctid";
		db_query($sql);		
		
		
		
		return $args;
	}
	
	private function hook_newday($args) {
		global $session;
		set_module_pref('flirts_today',0);
		$fiancee=get_module_pref('fiancee','marriage');
		if (((int)$session['user']['marriedto'])!=0) {
			require_once("lib/partner.php");
			$partner=get_partner(true);
			//buff marriage
			$this->applybuff(0,$partner);
		} elseif ($fiancee>0) {
			$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=$fiancee;";
			$result=db_query_cached($sql,"fiancee-".$fiancee."-".$session['user']['acctid']);
			$row=db_fetch_assoc($result);
			//buff fiancee
			$this->applybuff(1,$row['name']);
		}		
		return $args;
	}
	
	private function applybuff($type,$name) {
		if (is_module_active('alignment')) {
			require_once("modules/alignment/func.php");
			if (is_evil()) {
				apply_buff('marriage_buff',
						array(
							"name"=>array("`gVigorous feelings for %s",$name),
							"rounds"=>$this->buffrounds,
							"wearoff"=>"The need for your partner wears off for today.",
							"atkmod"=>1.05,
							"defmod"=>1.1,
							"roundmsg"=>array("`RYou think about %s`R!",$name),
							"schema"=>"module-marriage"
						));
			} elseif (is_good()) {
				apply_buff('marriage_buff',
						array(
							"name"=>array("`gHonest feelings for %s",$name),
							"rounds"=>$this->buffrounds,
							"wearoff"=>"The enthusiasm about your partner wears off for today.",
							"defmod"=>1.25,
							"roundmsg"=>array("`RYou care about %s`R!",$name),
							"schema"=>"module-marriage"
						));
			} else {
				apply_buff('marriage_buff',
						array(
							"name"=>array("`gFeelings for %s",$name),
							"rounds"=>$this->buffrounds,
							"wearoff"=>"The enthusiasm about your partner wears off for today.",
							"defmod"=>1.1,
							"atkmod"=>1.1,
							"roundmsg"=>array("`RYou care about %s`R!",$name),
							"schema"=>"module-marriage"
						));
			}
		} else {
			apply_buff('marriage_buff',
					array(
						"name"=>array("`gFeelings for %s",$name),
						"rounds"=>$this->buffrounds,
						"wearoff"=>"The enthusiasm about your partner wears off for today.",
						"defmod"=>1.2,
						"roundmsg"=>array("`RYou think about %s`R!",$name),
						"schema"=>"module-marriage"
					));
		}
	
	
	}
	
	public function run() {
		global $session;
		$op=httpget('op');
		switch ($op) {
			case "meadows":
				$this->meadows();
				break;
			case "flirt":
				$this->flirt();
				break;
			case "max":
				$this->max();
				break;
			default:
				$this->chapel();
		}
		return;	
	}
	
	private function ismarried($user=false) {
		global $session;
		if ($user===false) {
			$married=(int)$session['user']['marriedto'];
		} else {
			$sql="SELECT marriedto FROM ".db_prefix('accounts')." WHERE acctid=$user;";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			$married=(int)$row['marriedto'];
		}
		if ($married!=0) return $married;
		return false;
	}

	private function isengaged($user=false) {
		global $session;
		if ($user===false) {
			$engaged=(int)get_module_pref('fiancee','marriage');
		} else {
			$engaged=(int)get_module_pref('fiancee','marriage',$user);
		}
		if ($engaged!=0) return $engaged;
		return false;
	}	
	
	private function displaypicture($name) {
		if (is_module_active('addimages')) {
			if (get_module_pref('user_addimages','addimages')==0) return;
		}
		$nameshort=explode(".",$name);
		$nameshort=$nameshort[0];
		rawoutput("<br><center><img alt='$nameshort' src='$name'></center><br>");
		return;
	}
	
	private function flirtbar($value,$min=-1,$max=12,$height=40,$width=150) {
		$bg="#000099"; //blue
		$red="#FF0000"; //red
		$yellow="#FDF700"; //yellowish
		$orange="#FF8000"; //orange
		$grey="#827B84"; //greyish
		$hot=0.67;
		$med=0.47;
		$cool=0.3;
		$totalwidth=$max-$min;
		$scale=$width/$totalwidth;
		$length=round($scale*($value-$min));
/*		debug(($value-$min));
		debug($med*$totalwidth);
		debug($cool*$totalwidth);*/
		if (($value-$min)>($hot*$totalwidth)) {
			$fg=$red;
		} elseif (($value-$min)>($med*$totalwidth)) {
			$fg=$orange;
		} elseif (($value-$min)>($cool*$totalwidth)) { 
			$fg=$yellow;
		} else $fg=$grey;
		$table="<table BORDER='2' CELLPADDING='0' CELLSPACING='0' height='15' width='$width'>
					<TR><TD bgcolor='$bg' align='center'>
						<table BORDER='0' CELLPADDING='0' height='100%' width='$length'>
							<TR><TD bgcolor='$fg'></TD></TR>
						</TABLE>
					</TR>
					</TD></TR>
				</TABLE>";
		return $table;
	}
	
	private function flirtvalue($user) {
		//list returns array with acctid, charname, points
		$sql="SELECT a.name AS name,a.acctid AS acctid,avg(b.successful) as med FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.receiver=a.acctid WHERE b.initiator='$user' AND responsedate!='1970-01-01 00:00:00' GROUP BY b.receiver
			UNION 
			SELECT a.name AS name,a.acctid AS acctid,avg(b.successful) as med FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE b.receiver='$user' AND responsedate!='1970-01-01 00:00:00' GROUP BY b.receiver ORDER BY name ASC";
		$result=db_query($sql);
		$out=array();
		while ($row=db_fetch_assoc($result)) {
			$out[$row['acctid']]=array("name"=>$row['name'],"points"=>$row['med']);
		}
		return $out;
	}
	
	/*Flirt part*/
	
	private function flirt_reaction($number,$sex,$full=false) {
		//$reactions=array("Disgusting","Worse...","Really bad","I've seen worse","A failed try...","Not all bad","Slightly better than usual","A good call","I was positively surprised","I liked it!","Great!","I loved it!","One of my best flirts!");
		static $reactions_female=array("Leave me alone",
			"Annoying...", 
			"Am not interested", 
			"That was awkward", 
			"That felt insincere", 
			"I'm listening...", 
			"Go on...", 
			"That was sweet", 
			"I'm blushing", 
			"Please don't stop", 
			"My heart is beating so fast!", 
			"I love it so much!", 
			"You're the only one!",
			);
		static $reactions_male=array("Leave me alone",
			"Annoying...", 
			"am not interested", 
			"That was awkward", 
			"That felt insincere", 
			"I'm listening...", 
			"Go on...", 
			"That was sweet", 
			"I'm blushing", 
			"Don't stop", 
			"You really get through to me!", 
			"I love it so much!", 
			"You're the only one!",
			);		
		$reactions_female=translate_inline($reactions_female);		
		$reactions_male=translate_inline($reactions_male);
		if ($sex==SEX_MALE) {
			if ($full===true) return $reactions_male;
			return $reactions_male[$number];
		} else {
			if ($full===true) return $reactions_female;
			return $reactions_female[$number];
		}
	}

	private function mystrip($text) {
		$text=str_replace(chr(13),"`n",$text);
		$text=str_replace('`c','',$text);
		$text=str_replace('`i','',$text);
		$text=str_replace('`b','',$text);
		return $text;	
	}
	
	private function flirt() {
		global $session;
		$limit=50;
		require("modules/marriage/run/flirt.php");
		
	}
	
	
	/* general*/
	
	private function meadows() {
		global $session;
		$subop=httpget('subop');
		$inoplace=(((int)date("d"))%3)+1;
		page_header('Shadowy Meadows');
		output("`c`b`)S`%hadowy `xMeadows`b`c`n`n");
		addnav("Navigation");
		addnav("Back to the Gardens","gardens.php");
		if ($subop!='') addnav("Back to the Meadows","runmodule.php?module=marriage&op=meadows");
		switch ($subop) {
			
			case "gamecorner":
				output("`RYou approach the game corner and sit down for a game of Shogi.`n`nOn a distant table, you see `)S`%hikamaru`R playing and yawning...`n`n`n`n");
				output("`%You remind yourself that this a place of peace, quite, and budding romance. This is not a place to harbor fights, rude language, or anything more then flirting.`n`n");
				require_once("lib/commentary.php");
				addcommentary();					
				commentdisplay("`n`n`%C`Ronverse:`n","mead-".$subop,"",50,"converses");
				break;
			case "meadow-1":case "meadow-2":case "meadow-3":
				output("`RYou enter a part of the big meadow in which people you are interested in might converse.`n`n");
				output("`%You remind yourself that this is a place of quiet peace and romance. Neither it is for fighting nor rude language nor more than just flirting.`n`n");
				modulehook($subop,array());
				$this->displaypicture("modules/marriage/pics/$subop.gif");
				if ($subop=="meadow-$inoplace") {
					//ino here
					addnav("Persons");
					addnav_notl($this->ringgiver,"runmodule.php?module=marriage&op=max");
					output("`4Also you see %s`4 here, standing in the shadow of a tree.`n`n",$this->ringgiver);
				}
				require_once("lib/commentary.php");
				addcommentary();	
				commentdisplay("`n`n`%C`Ronverse:`n",$subop,"",50,"converses");
				break;
			case "spouse":
				output("`RYou take some time off with your spouse... you really needed that. You feel relaxed and refreshed.");
				break;
			case "fiancee":
				output("`RYou take some time to wait for your fiancee to show up... you really needed that time off, relaxing.");
				break;
			default:
			if (is_module_active('punishers')) $guard=get_module_setting('chiefname','punishers');
				else $guard=translate_inline($this->readingguy);
			output("`RYou enter a small hidden place in the gardens called the `)S`%hadowy `xMeadows`R.`n`n");
			output("You see many other all over the place, engaged in talks - especially romantic ones - but also a game corner where discussions are going on during games of the mind.`n`nIn a more distant part, there is also some sort of picnic taking place.`n`n");
			output("With the back to a tree, you see `q%s`R reading a certain indecent book.`n`n",$guard);
			addnav("The Meadow");
			for ($i=1;$i<4;$i++) {
				addnav(array("Meadow #%s",$i),"runmodule.php?module=marriage&op=meadows&subop=meadow-$i");
			}
			addnav("Game Corner","runmodule.php?module=marriage&op=meadows&subop=gamecorner");
			addnav("Flirting...");
			addnav("Flirt Actions","runmodule.php?module=marriage&op=flirt");
			if ($this->ismarried()) {
				addnav("Find a nice place for you and your spouse","runmodule.php?module=marriage&op=meadows&subop=spouse");
			} elseif ($this->isengaged()) {
				addnav("Find a nice place for you and your fiancee","runmodule.php?module=marriage&op=meadows&subop=fiancee");
			}
			addnav("Chapel");
			addnav("Visit the Chapel","runmodule.php?module=marriage&op=chapel");
			break;
		
		}
	
		page_footer();
		
	}
	
	/*Marriage Part*/
	
	private function max() {
		global $session;
		$limit=50;
		$rings=array(
			"`gSteel `#Ring",
			"`qMercury `#Ring",
			"`)Silver `#Ring",
			"`^Gold `#Ring",
			"`yTribal `#Ring",
			"`)Silver `#Ring `4with `^Golden Inlays",
			"`mDiamond `#Ring",
			"`~Adamantite Black `#Ring",
			"`mDiamond `&White `#Ring",
			);
		$ringdesc=array(
			"A simple and plain ring of steel. Elegant in design.",
			"Made of the finest mercury, it ages like a relationship, but if cleaned from time to time, it stays the same as now.",
			"Silver is a good choice ... it is durable and shining.",
			"Gold is the choice of luxury and wealth.",
			"The Tribal Ring is made of silver along with tribal cravings laid around the ring like a wire.",
			"This ring made of silver has been patched together with gold in the process, giving it a strange 'sun-moon' effect of gold and silver.",
			"Diamonds are girl's best friend. Expensive, but nevertheless...",
			"This hard material has a weird black aura...vile and oppressing.",
			"The Diamond has been patched into something looking like white snow... you can't explain what material it might be, but it shines like innocence.",
			);
		$ringcosts=array( //gems|gold
			"5|500",
			"10|5000",
			"20|5000",
			"40|5000",
			"50|5000",
			"60|5000",
			"100|5000",
			"200|5000",
			"500|50000",
			"500|50000",
			);
		// enhancements are calculated on this
		
		$rings=translate_inline($rings);
		$ringdesc=translate_inline($ringdesc);
		
		$user=$session['user']['acctid'];

		$subop=httpget('subop');
		page_header('Shadowy Meadows');
		output("`c`b%s`b`c`n",$this->ringgiver);
		output("`cFlowers And More`c`n`n");
		addnav("Navigation");
		addnav("Back to the Meadows","runmodule.php?module=marriage&op=meadows");
		addnav("FAQ");
		addnav("About rings","runmodule.php?module=marriage&op=max&subop=faq&topic=rings");
		addnav("About marriage","runmodule.php?module=marriage&op=max&subop=faq&topic=marriage");
		$this->displaypicture("modules/marriage/pics/ringgiver.jpg");	
		switch($subop) {
			case "buyring":
				
				if (httppost('send')) {
					$ring=(int)httppost('ring');
					$target=(int)httppost('target');
					list($gems,$gold)=explode("|",$ringcosts[$ring]);
					output("`xYou have bought the %s`x...and your proposal has been sent!`n`n",$rings[$ring]);
					$session['user']['gold']-=$gold;
					$session['user']['gems']-=$gems;
					$proposal=$this->mystrip(httppost('proposal'));
					$sql="INSERT INTO ".db_prefix('marriage_proposals')." (initiator,proposed_to,ring,propose,date,response) VALUES ('$user','$target','$ring','".addslashes($proposal)."','".date("Y-m-d H:i:s")."','');";
					db_query($sql);
					require_once("lib/systemmail.php");
					systemmail($target,array("`R`bProposal!`b"),array("
						%s`x has proposed to you! For details and your response visit the Meadows!",
							$session['user']['name'],
						));
					break;
				}
			
				$text='';
				
				if (httppost('preview')) {
					$text=httppost('proposal');
					$message=stripslashes($text);
					output("Preview:`n`n");
					output_notl("`c".$message."`c`n`n");
				}
			
				$ring=(int)httpget('ring');
				if ($ring===0) $ring=(int) httppost('ring');
				$target=(int)httpget('target');
				if ($target===0) $target=(int) httppost('target');
				if ($target===0) break;
				output("`4\"`yNow it is your turn ... how do you intend to propose? Give a detailed description on what you do. Your beloved will give you a response, hopefully.`4\"`x`n`n");
				rawoutput("<form action='runmodule.php?module=marriage&op=max&subop=buyring' method='POST'><center>");
				addnav("","runmodule.php?module=marriage&op=max&subop=buyring");
				require_once("modules/marriage/forms.php");
				marriage_charsleft("proposal");
				$l=20000;
				$box='proposal';
				rawoutput("<br><table><tr><td colspan='2'><textarea name='$box' id='input$box' onKeyUp='previewtext$box(document.getElementById(\"input$box\").value,$l);' cols='50' rows='20' size='3000' wrap='soft'>$message</textarea></td></tr>");
				rawoutput("<input type='hidden' name='target' value='$target'>");
				rawoutput("<input type='hidden' name='ring' value='$ring'>");
				$submit=translate_inline("Propose!");
				$preview=translate_inline("Preview!");
				rawoutput("<tr><td><input type='submit' class='button' name='preview' value='$preview'></td><td align='right'><input type='submit' class='button' name='send' value='$submit'></td></tr></table>");
				rawoutput("</center></form>");
				output("`i`\$Note: No center tags, no bold tags, no italic tags, those will be removed.`n`xAlso mind the timeout!`i");
				break;
			case "examine":
				$ring=(int)httpget('ring');
				$target=(int)httpget('target');
				output("`xYou step closer and examine the %s`x...`n`n",$rings[$ring]);
				output_notl($ringdesc[$ring]."`n`n");
				list($gems,$gold)=explode("|",$ringcosts[$ring]);
				output("`4This ring costs `^%s gold`4 and `%%s gems`4.`n`n`\$",$gold,$gems);
				$efford=true;
				if ($gems>$session['user']['gems']) {
					output("You do not have enough gems for it.`n");
					$efford=false;
				} 
				if ($gold>$session['user']['gold']) {
					output("You do not have enough gold for it.`n");
					$efford=false;
				}
				addnav("Ops");
				$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=$target;";
				$result=db_query($sql);
				$row=db_fetch_assoc($result);
				if ($efford) {
					addnav(array("Buy the ring for %s",$row['name']),"runmodule.php?module=marriage&op=max&subop=buyring&ring=$ring&target=$target");
				}
				addnav("Examine another ring","runmodule.php?module=marriage&op=max&subop=ring&target=$target");
				break;
			case "ring":
				$target=(int)httpget('target');
				$sql="SELECT * FROM ".db_prefix('marriage_proposals')." WHERE ((initiator=$user AND proposed_to=$target) OR (initiator=$target AND proposed_to=$user)) AND responsedate='1970-01-01 00:00:00';";
				$result=db_query($sql);
				if (db_num_rows($result)>0) {
					output("`4\"`yA ring? Yes, a ring and proposal between you two has been already done by me... please visit the meadows for more information.`4\"");
					break;
				}
				output("`4\"`ySo you have been sent here to get a ring for someone very dear to you. Dear enough to make your relationship public... and form a holy bond between each other...`4\"`4`n`nShe pulls out a couple of small boxes, hidden in her clothes and continues...`n`n`4\"`yThere are different rings for different people. Choose one you want to send, you will get your ring fitting to the one you chose.`4\"`n`nWhat do you do?");
				addnav("Rings");
				foreach ($rings as $key=>$ring) {
					addnav_notl($ring,"runmodule.php?module=marriage&op=max&subop=examine&ring=$key&target=$target");			
				}
				break;
			case "faq":
				output("%s`x tells you:`n`n`%",$this->ringgiver);
				$topic=httpget('topic');
				switch ($topic) {
					case "rings":
						output("`4\"`yAah, rings. Well, you need one in order to propose to somebody, and before that, you need `bquite`b some flirting to be done... and last but not least, the feelings have to be right.`n`n");
						output("`xThere are different rings. Each ring emphasizes a different aspect. Your true love should get the appropriate ring that fits to your relationship. Else, the chance of refusal is quite high, so pay attention to your spouse-to-be and the future of you both.`4\"`n`n");
						break;
					case "marriage":
						output("`4\"`xSo you want to get married? I can only hope out of true love and not out of capitalism... well, I'll tell you how it works in these very meadows.`n`n`yFirst, flirting is everything. If you have already one in mind, just approach that person. If you are idling around, try to get involved in conversations and see if there is someone you might like. Once you have made up your mind, begin to initiate a flirt.`n`n`gSecondly, start the first flirt calmly, and wait for the response. If you get a promising answer, you might continue... or even were right flirted back at, who knows ;)`nIf the flirts were answered too badly, you will never have a chance for proposal. And one bad flirt cannot outweigh one good usually... it takes time and patience.`n`n`RSame as for love.`n`nThirdly, if you two are pretty much familiar with each other, have flirted a lot positively ... you can check your marriage chances in the flirtpartner list. If this works out, you can visit me directly from there. I'll explain further when this time comes...`n`n");					
						break;
					default:
						output("`4\"`yWell... my time is limited, so just get flirting...`4\"");
						break;
				
				}
				break;
			default:
			output("`xYou wander around, towards %s`x who is standing at a tree with some flowers ready to be sold to young couples.`n`nYou remember her family owns a flower store and it's only natural for her to sell them right here where the demand is.`n`n`4As you close in, you see that she has a few more filled pockets than usually.",$this->ringgiver);
		
		}
		page_footer();
	}
	
	private function chapel() {
		global $session;
		$subop=httpget('subop');
		$inoplace=1;
		$user=$session['user']['acctid'];
		page_header('Shadowy Meadows Chapel');
		output("`c`b`)S`%hadowy `xMeadows `gChapel`b`c`n`n");
		addnav("Navigation");
		addnav("Back to the Gardens","gardens.php");
		addnav("Back to the Meadows","runmodule.php?module=marriage&op=meadows");
		$love="愛";
		switch ($subop) {
		
			case "marry_no":
				require_once("lib/systemmail.php");
				$target=(int)$target;
				$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=$target;";
				$result=db_query_cached($sql,"fiancee-".$session['user']['acctid']);
				$row=db_fetch_assoc($result);
				$namefiancee=$row['name'];
				
				output("%s`xsays, \"`qAlright - I will send out a notice to %s that you think it is too early.`x\"`n`n\"`xIf you want to cancel the engagement, you can so do so separately`q.`x\"",$this->chapelwatcher,$namefiancee);
				
				set_module_pref('marry',0,'marriage');
				set_module_pref('marry',0,'marriage',$target);
				
				systemmail($fiancee,array("`)Second Thoughts..."),array("`RYour wedding offer to %s`R was rejected - for now? Please make sure you both are ready for this.`n`nRegards, %s",$session['user']['name'],$this->chapelwatcher));
				
				break;
		
			case "marry_yes":
				require_once("lib/systemmail.php");
				$target=(int)$this->isengaged();
				$sql="SELECT name,sex FROM ".db_prefix('accounts')." WHERE acctid=$target;";
				$result=db_query($sql);
				$row=db_fetch_assoc($result);
				$fianceename=$row['name'];
				$fianceesex=$row['sex'];

				if ($row['name']==$session['user']['name']) {
					output("`x\"`q You cannot marry yourself... DUH!");
					break;
				}
			
				if (get_module_pref('marry','marriage',$target)!=2) {
					set_module_pref('marry',2,'marriage');
					output("`x\"`qOkay - now the only thing missing is your spouse-to-be...`x\"...");
					break;
				}
			
				//reset fiancee status
				set_module_pref('fiancee',0,'marriage');
				set_module_pref('fiancee',0,'marriage',$target);
				//reset possible marriages
				set_module_pref('marry',0,'marriage');
				set_module_pref('marry',0,'marriage',$target);	
			
				$session['user']['marriedto']=$target;
				$sql="UPDATE ".db_prefix('accounts')." SET marriedto=".$session['user']['acctid']." WHERE acctid=$target";
				db_query($sql);
				
				$fianceegender=translate_inline($fianceesex==SEX_FEMALE?"wife":"husband");
				$gender=translate_inline($session['user']['sex']==SEX_FEMALE?"wife":"husband");
				
				output("`xAfter you have entered the chapel, all gets a bit blurry - and you have the wedding you always wanted (you know how this looks like, so TELL others how it was ^^) ... `n`n%s`x clears her throat and says, \"`qI hereby pronounce you %s and %s!`x\"",$this->chapelwatcher,$fianceegender,$gender);
				
				systemmail($fiancee,array("`)Yes!"),array("`R%s`R has accepted the wedding offer and given you the consent for matrimony! You should think of a proper honeymoon ^^`n`nRegards, %s",$session['user']['name'],$this->chapelwatcher));
				
				addnews("`R%s`\$ and `R%s`\$ have been joined in matrimony today!",$session['user']['name'],$fianceename);
				
				break;
			
			
			case "marryconfirm":
				require_once("lib/systemmail.php");
				$target=(int)$target;
				$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=$target;";
				$result=db_query_cached($sql,"fiancee-".$session['user']['acctid']);
				$row=db_fetch_assoc($result);
				$namefiancee=$row['name'];
				
				output("%s`xsays, \"`qAlright - I will send out a notice right away and you will get notice from %s`q.`x\"`n`n\"Note that you `btoo`b need to visit the chapel and says `\$Yes`q...`x\"",$this->chapelwatcher,$namefiancee);
				
				set_module_pref('marry',1,'marriage');
				set_module_pref('marry',1,'marriage',$target);
				
				systemmail($target,array("`)A Wedding Proposal!"),array("`RYour engagement with %s`R has finally led to some results... `n`nYou have been asked by %s`R to visit the chapel for your ceremony... to say `\$Yes `Ror `\$No`R.`n`nRegards, %s",$session['user']['name'],$session['user']['name'],$this->chapelwatcher));
				break;
				
			case "chickenout":
				output("%s`x smiles and lets you leave in peace.",$this->chapelwatcher);
				break;
		
			case "marry":
				$target=(int)httpget('target');
				$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=$target;";
				$result=db_query_cached($sql,"fiancee-".$session['user']['acctid']);
				$row=db_fetch_assoc($result);
				$namefiancee=$row['name'];
				
				if (get_module_pref('marry','marriage')==1) {
					output("`x\"`qWell, DUH, you already have that going on?`x\", says %s`x.",$this->chapelwatcher);
					break;
				}
				
				output("You see %s`x doing a partial reconstruction of the wedding area including the inside of the chapel, \"`qOh, sorry, we are currently rebuilding the entire thing - so I heard you want to get married? `\$Great!\"`x`n`n\"`qSadly I cannot offer you much of a ceremony - and your spouse-to-be has to accept the marriage, too. Else there will be ... problems... you know.`x\"`n`n\"`qDo you want to get serious about this?",$this->chapelwatcher);
				addnav("Marriage...");
				addnav(array("`\$Yes!`y Marry %s",$namefiancee),"runmodule.php?module=marriage&op=chapel&subop=marryconfirm&target=$target");
				addnav(array("`\$No!`x I need more time with %s",$namefiancee),"runmodule.php?module=marriage&op=chapel&subop=chickenout");
				break;
			case "confirmquitengagement":
				$fiancee=get_module_pref('fiancee','marriage');
				//reset fiancee status
				set_module_pref('fiancee',0,'marriage');
				set_module_pref('fiancee',0,'marriage',$fiancee);
				//reset possible marriages
				set_module_pref('marry',0,'marriage');
				set_module_pref('marry',0,'marriage',$fiancee);	
				require_once("lib/systemmail.php");
				systemmail($fiancee,array("`)Engagement cancelled!"),array("`RYour engagement with %s`R has been cancelled by your fiancée... the ring has been taken and will be hidden from the plain eyesight as the relationsship ended.`n`nRegards, %s",$session['user']['name'],$this->chapelwatcher));
				output("%s`x says, \"`qAs you wish. Your former fiancée will be notified... I recommend to write a personal letter to her by yourself if you did not already  so.`x\"",$this->chapelwatcher);
				break;
			case "endengagement":
				$fiancee=get_module_pref('fiancee');
				$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=$fiancee;";
				$result=db_query_cached($sql,"fiancee-".$session['user']['acctid']);
				$row=db_fetch_assoc($result);
				$namefiancee=$row['name'];
				$this->displaypicture('modules/marriage/pics/lonely.jpg');
				output("%s`x asks you, \"`qYou really do want your engagement with %s to be cancelled? Well, I'll arrange the necessary things, send the ring back and so on... but be really sure about it, you won't be able to turn back.`x\"",$this->chapelwatcher,$namefiancee);
				addnav("Confirm");
				addnav("`\$Really `0Cancel Engagement","runmodule.php?module=marriage&op=chapel&subop=confirmquitengagement");
				break;
			case "divorcelover":
				require_once("lib/partner.php");
				$partner=get_partner(true);
				output("%s`x asks you, \"`qYou really do want your marriage with %s to be ended? Well, I'll arrange the necessary things, send the ring back and so on... but be really sure about it, you won't be able to turn back.`x\"",$this->chapelwatcher,$partner);
				addnav("Confirm");
				addnav("`\$Really `0get divorced?","runmodule.php?module=marriage&op=chapel&subop=confirmdivorcelover");				
				break;
			case "confirmdivorcelover":
				require_once("lib/partner.php");
				$partner=get_partner(true);
				output("%s`x says, \"`qOkay, the notice of divorce will be delivered to %s. As *cough* with this specific person *cough* it happens often *cough* I doubt you will face any greater negative consequences.`x\"",$this->chapelwatcher,$partner);
				$session['user']['marriedto']=0;
				debuglog("Divorce from the village bicycle");
				apply_buff('divorce-seth',
					 array(
						"name"=>"`\$Divorce Guilt",
						"rounds"=>$this->buffrounds,
						"wearoff"=>"You feel hopeful again.",
						"atkmod"=>0.9,
						"defmod"=>0.9,
						"minioncount"=>1,
						"survivenewday"=>1,
						"roundmsg"=>array("`)You feel very sad about `4%s`)!",$partner),
						"schema"=>"module-marriage"
						));
				break;

			case "divorceplayer":
				require_once("lib/partner.php");
				$partner=get_partner(true);
				output("%s`x asks you, \"`qYou really do want your marriage with %s to be ended? Well, I'll arrange the necessary things, send the ring back and so on... but be really sure about it, you won't be able to turn back.`x\"",$this->chapelwatcher,$partner);
				$takegems=(int)($session['user']['gems']*0.2);
				$gold=(int)($session['user']['gold']+$session['user']['goldinbank']);
				output("`n`n`\$You will lose 20%% of your gems (which means %s gems) and all your gold in bank and at hand (total of %s), which will be donated to the `)\"Lonely Lovers\"`\$ fund.",$takegems,$gold);
				
				addnav("Confirm");
				addnav("`\$Really `0get divorced?","runmodule.php?module=marriage&op=chapel&subop=confirmdivorceplayer");				
				break;
			case "confirmdivorceplayer":
				$sql= "SELECT acctid,name FROM ".db_prefix('accounts')." WHERE acctid=".$session['user']['marriedto'].";";
				$result=db_query($sql);
				if (db_num_rows($result)==0) {
					output("Sorry, there was an error, please report to your admin.");
					break;
				}
				$row=db_fetch_assoc($result);
				$takegems=(int)($session['user']['gems']*0.2);
				$gold=(int)($session['user']['gold']+$session['user']['goldinbank']);
				output("%s`x says, \"`qOkay, the notice of divorce will be delivered to %s`q. `n`nThe %s gems and %s gold have been taken into the fund, sorry, but an ending love it only a matter of loss of feelings, but also a loss of ... assets... mostly.`x\"",$this->chapelwatcher,$row['name'],$takegems,$gold);
				//news
				addnews("`2Bad news: `4%s`4 asked for a divorce from %s`4 today...",$session['user']['name'],$row['name']);
				//gems
				$session['user']['gems']-=$takegems;
				$session['user']['gold']=0;
				$session['user']['goldinbank']=0;
				debuglog("Lost $takegems and all gold ($gold) due to a divorce from player ".$row['login'],$row['acctid'],$session['user']['acctid']);
				
				//set
				$sql= "UPDATE ".db_prefix('accounts')." SET marriedto=0 WHERE acctid=".$session['user']['marriedto'].";";
				$result=db_query($sql);
				$session['user']['marriedto']=0;
				//set
				
				//notify
				require_once("lib/systemmail.php");
				systemmail($row['acctid'],array("`\$Divorce!"),array("`RYour marriage with %s`R has been divorced by your spouse... the ring has been taken and will be hidden from the plain eyesight as the relationsship ended. At least you might feel better that %s gems and %s gold have been taken from your spouse to support the \"Lonely Lovers\" fund... `n`nRegards, %s",$session['user']['name'],$takegems,$gold,$this->chapelwatcher));
				output("%s`x says, \"`qAs you wish. Your former spouse will be notified... I recommend to write a personal letter to her by yourself if you did not already so.`x\"",$this->chapelwatcher);				
				
				
				apply_buff('divorce-marriage',
					 array(
						"name"=>array("`\$Divorce Guilt (%s`\$)",$row['name']),
						"rounds"=>$this->buffrounds,
						"wearoff"=>"You feel fully single again.",
						"atkmod"=>0.9,
						"defmod"=>0.9,
						"minioncount"=>1,
						"survivenewday"=>1,
						"roundmsg"=>array("`)You feel very sad about `4%s`)!",$row['name']),
						"schema"=>"module-marriage"
						));
				break;				
				

				
			default:
			$this->displaypicture("modules/marriage/pics/chapelwatcher.jpg");			
			output("`xYou enter the small wooden chapel, located at the edge of the meadows.`n`nYou remember that %s`x looks after the married and engaged couples around here. She is wearing beautiful clothes fitting to her type, and you can see a shining tattoo: `\$%s`x.`n`n",$this->chapelwatcher,$love);			
			$does=array(
				"`gAs you close in, she is currently practicing her skills in meditation and self-discipline on a small carpet in the corner of the room.",
				"`gShe currently seems to be busy with something.",
				"`gCurrently there is a ceremony in progress... you decide not to make too much noise.",
				array("%s`g is here and selling some flowers for the bouquets in the chapel.",$this->ringgiver),
				array("%s`g is heard to be singing...\"`\$%s is all around...`g\"",$this->chapelwatcher,$love),
				);
			$does=translate_inline($does);
			$current=((int)date("d"))%(count($does));
			$quote=$does[$current];
			if (is_array($quote)) $quote=call_user_func_array("sprintf",$quote);
			output_notl($quote);
			addnav("Chapel");
			$sql="SELECT count(initiator) as counter FROM ".db_prefix('marriage_proposals')." WHERE responsedate='1970-01-01 00:00:00' AND proposed_to=".$user.";";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			addnav(array("Proposal Overview (`4%s unresponded`0)",(int)$row['counter']),"runmodule.php?module=marriage&op=flirt&subop=checkproposals");				
			if ($this->isengaged()) {
				if (get_module_pref('marry','marriage')==1) {
					$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=".$this->isengaged();
					$result=db_query($sql);
					invalidatedatacache("fiancee-".$session['user']['acctid']);
					$row=db_fetch_assoc($result);
					$fianceename=$row['name'];					
					output("`n`n`)After you enter the chapel - all activities stop and %s`x approaches you, smiling bright like the sun.`n`n\"`qToday is your great day! I hope you are ready? Now is the `bLAST`b chance to think over, you cannot stop after we've walked in! %s`x is already waiting for you...`x\"",$this->chapelwatcher,$fianceename);
					addnav("Marriage!");
					addnav(array("`\$YES, marry %s`\$!",$fianceename),"runmodule.php?module=marriage&op=chapel&subop=marry_yes");
					addnav(array("`\$NO, reject %s`\$!",$fianceename),"runmodule.php?module=marriage&op=chapel&subop=marry_no");
				} else {
					addnav("Cancel Engagement...","runmodule.php?module=marriage&op=chapel&subop=endengagement");
				}
			} elseif ($this->ismarried()) {
				if ($this->ismarried()==INT_MAX) {
					//seth/violet/lovers
					addnav("Get a divorce","runmodule.php?module=marriage&op=chapel&subop=divorcelover");
				} else {
					//player
					addnav("Get a divorce","runmodule.php?module=marriage&op=chapel&subop=divorceplayer");
				
				}
			}			
			break;
		
		}
	
		page_footer();
	}
	
}


?>
