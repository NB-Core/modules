<?php


function homecounter_getmoduleinfo(){
	$info = array(
	    "name"=>"Homecounter",
		"version"=>"1.0",
		"author"=>"Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"http://me.todestanz.de",
		"settings"=>array(
		"Homecounter - Preferences,title",
		"year"=>"Year of the event,floatrange,2005,3000,1|2006",
		"month"=>"Month of the event,floatrange,1,12,1|7",
		"day"=>"Day of the event,floatrange,1,31,1|14",
		"Time is 24h a day,note",
		"hour"=>"Hour of the event,floatrange,0,23,1|18",
		"minute"=>"Minute of the event,floatrange,0,60,1|6",
		"second"=>"Second of the event,floatrange,0,60,1|24",
		"You may use colours and the like in this text(leave empty if you don't want to display the counter),note",
		"Text"=>"What text should be shown?,text|Judgement day is in",
		"Leave this empty if you want to have 0 years 0 month...etc... shown,note",
		"Overtext"=>"What text should be shown when the time is over(no colours),text|YES... JUDGEMENT DAY!!!",
		"Now replace the tags for years-months-etc according to your language or just for fun(singular),note",
		"nameyear"=>"What is the name for year,text|year",
		"namemonth"=>"What is the name for month,text|month",
		"nameday"=>"What is the name for day,text|day",
		"namehour"=>"What is the name for hour,text|hour",
		"nameminute"=>"What is the name for minute,text|minute",
		"namesecond"=>"What is the name for second,text|second",
		"...and now plural,note",
		"pnameyear"=>"What is the name for years,text|years",
		"pnamemonth"=>"What is the name for months,text|months",
		"pnameday"=>"What is the name for days,text|days",
		"pnamehour"=>"What is the name for hours,text|hours",
		"pnameminute"=>"What is the name for minutes,text|minutes",
		"pnamesecond"=>"What is the name for seconds,text|seconds",
		"separator"=>"What word will separate the last part (usually 'and'),text|and",
		"length"=>"Length of the box on your index page (you have to measure that yourself),int|85",
		"Check here what values you want to show,note",
		"showyear"=>"Show the year?,bool|1",
		"showmonth"=>"Show the month?,bool|1",
		"showday"=>"Show the day?,bool|1",
		"showhour"=>"Show the hour?,bool|1",
		"showminute"=>"Show the minute?,bool|1",
		"showsecond"=>"Show the second?,bool|1",
		),
		);
    return $info;
}

function homecounter_install(){
	module_addhook("index");
	if (is_module_active("Homecounter")) output_notl("`c`b`$ Module Homecounter updated`b`c`n`n");

	return true;
}

function homecounter_uninstall()
{
  output ("Thank you for using!`n`n");

  return true;
}


function homecounter_dohook($hookname, $args){
	global $session;
	switch ($hookname)
	{
	case "index":
		$text=get_module_setting("Text");
		$overtext=get_module_setting("Overtext");
		$year=get_module_setting("year");
		$month=get_module_setting("month");
		$day=get_module_setting("day");
		$hour=get_module_setting("hour");
		$minute=get_module_setting("minute");
		$second=get_module_setting("second");
		$nameyears=get_module_setting("nameyear");
		$namemonths=get_module_setting("namemonth");
		$namedays=get_module_setting("nameday");
		$namehours=get_module_setting("namehour");
		$nameminutes=get_module_setting("nameminute");
		$nameseconds=get_module_setting("namesecond");
		$pnameyears=get_module_setting("pnameyear");
		$pnamemonths=get_module_setting("pnamemonth");
		$pnamedays=get_module_setting("pnameday");
		$pnamehours=get_module_setting("pnamehour");
		$pnameminutes=get_module_setting("pnameminute");
		$pnameseconds=get_module_setting("pnamesecond");
		$sep=get_module_setting("separator");
		$length=get_module_setting("length");
		$showyear=get_module_setting("showyear");
		$showmonth=get_module_setting("showmonth");
		$showday=get_module_setting("showday");
		$showhour=get_module_setting("showhour");
		$showminute=get_module_setting("showminute");
		$showsecond=get_module_setting("showsecond");
		if (!$text) break;
	   rawoutput("
	   <script language='JavaScript'>
      // Ziel-Datum in MEZ
      var year=".$year.", month=".$month.", day=".$day.", hour=".$hour.", minute=".$minute.", second=".$second.";
      var targetDate=new Date(year,month-1,day,hour,minute,second);

      function countdown() {
        startDate=new Date();
        if(startDate<=targetDate)  {

          var jahre=0, monate=0, tage=0, stunden=0, minuten=0, sekunden=0;

          if(startDate<targetDate) {
            while(startDate<targetDate) {
              if(startDate.setFullYear(startDate.getFullYear()+1)<=targetDate) jahre++;
            }
            startDate.setFullYear(startDate.getFullYear()-1);
          }

          var restTage=0;
		  var dummy='';
          var m=startDate.getMonth();
          if(m==1-1|| m==3-1||m==5-1||m==7-1||m==8-1||m==10-1||m==12-1)
              restTage=31-startDate.getDate();
          else if(m==4-1|| m==6-1||m==9-1||m==11-1) restTage=30-startDate.getDate();
          else if(m==2-1) {
            if(startDate.getFullYear()%4==0 && (startDate.getFullYear()%100!=0
                || startDate.getFullYear()%400==0))
                    restTage=29-startDate.getDate(); // Schaltjahr
            else restTage=28-startDate.getDate();
          }

          var startTag=startDate.getDate();
          var zielTag=targetDate.getDate();
          startDate.setDate(1);
          targetDate.setDate(1);

          if(startDate<targetDate) {
            while(startDate<targetDate) {
              if(startDate.setMonth(startDate.getMonth()+1)<=targetDate) monate++;
            }
            startDate.setMonth(startDate.getMonth()-1);
          }

          if(startDate.getMonth()==targetDate.getMonth()) {
            if(startTag<=zielTag) startDate.setDate(startTag);
            else {
              monate--;
              tage=restTage+1;
            }
          }
          else {
            startDate.setMonth(startDate.getMonth()+1);
            if(startTag>=zielTag) tage=restTage+1;
            else {
              monate++;
              startDate.setDate(startTag);
            }
          }
          targetDate.setDate(zielTag);

          // Tage
          restTage=Math.floor((targetDate-startDate)/(24*60*60*1000));
          startDate.setTime(startDate.getTime()+restTage*24*60*60*1000);
          tage+=restTage;

          // Stunden
          stunden=Math.floor((targetDate-startDate)/(60*60*1000));
          startDate.setTime(startDate.getTime()+stunden*60*60*1000);

          // Minuten
          minuten=Math.floor((targetDate-startDate)/(60*1000));
          startDate.setTime(startDate.getTime()+minuten*60*1000);

          // Sekunden
          sekunden=Math.floor((targetDate-startDate)/1000);

          // Anzeige formatieren
          (jahre!=1)?jahre=jahre+' ".$pnameyears.",  ':jahre=jahre+' ".$nameyears.",  ';
          (monate!=1)?monate=monate+' ".$pnamemonths.",  ':monate=monate+' ".$namemonths.",  ';
          (tage!=1)?tage=tage+' ".$pnamedays.",  ':tage=tage+' ".$namedays.",  ';
          (stunden!=1)?stunden=stunden+' ".$pnamehours.",  ':stunden=stunden+' ".$namehours.",  ';
          (minuten!=1)?minuten=minuten+' ".$pnameminutes."  ".$sep."  ':minuten=minuten+' ".$nameminutes."  und  ';
          if(sekunden<10) sekunden='0'+sekunden;
          (sekunden!=1)?sekunden=sekunden+' ".$pnameseconds."':sekunden=sekunden+' ".$nameseconds."';

          document.countdownform.countdowninput.value=
              ".($showyear?"jahre+":"").($showmonth?"monate+":"").($showday?"tage+":"").($showhour?"stunden+":"").($showminute?"minuten+":"").($showsecond?"sekunden":"dummy").";

          setTimeout('countdown()',200);
        }
        // Anderenfalls alles auf Null setzen
        else document.countdownform.countdowninput.value=
            ");
			if (!$overtext)
			{
			rawoutput("'0 ".$pnameyears.",  0 ".$pnamemonths.",  0 ".$pnamedays.",  0 ".$namehours.",  0 ".$pnameminutes."  und  00 ".$pnameseconds."';");
			}else{
			rawoutput("'".$overtext."'");
			}
			rawoutput("
      }
    </script>
  <body onload='countdown()'>
    <form name='countdownform'>
      <p>
	  ");
	output_notl($text);
	rawoutput("
        <center><input readonly size='".$length."' name='countdowninput'></center>
      </p>
    </form>
  </body>
	");

		break;
	default:

	break;
	}
	return $args;
}

function homecounter_run(){
}
?>
