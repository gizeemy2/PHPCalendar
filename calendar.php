<?php

function days_in_month($month, $year){
    if ( empty($month) || empty($year) ){ return 0; }

    return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

$Dom = new DOMDocument();

$head = $Dom->createElement('head');
$Dom->appendChild($head);

$meta = $Dom->createElement('meta');
$meta->setAttribute("charset","UTF-8");
$head->appendChild($meta);

$title = $Dom->createElement('title');
$title->nodeValue='Event Calendar';
$head->appendChild($title);

$style = $Dom->createElement('link');
$style->setAttribute( 'rel', 'stylesheet' );
$style->setAttribute( 'type', 'text/css' );
$style->setAttribute('href','calendar.css');
$head->appendChild($style);

$script=$Dom->createElement("script");
$script->setAttribute("src","calendar.js");
$head->appendChild($script);


$scriptip = $Dom->createElement('script');
$scriptip->setAttribute('src','tip-core.js');
$head->appendChild($scriptip);

$body = $Dom->createElement('body');
$Dom->appendChild($body);

$form = $Dom->createElement('form');
$form->setAttribute('method','post');
$form->setAttribute('action','');
$form->setAttribute('class','form');
$body->appendChild($form);



$select = $Dom->createElement('select');
$select->setAttribute( 'name', 'month' );
$select->setAttribute( 'class', 'month' );

$options = [
    1=>'Ocak',
    2=>'Şubat',
    3=>'Mart',
    4=>'Nisan',
    5=>'Mayıs',
    6=>'Haziran',
    7=>'Temmuz',
    8=>'Ağustos',
    9=>'Eylül',
    10=>'Ekim',
    11=>'Kasım',
    12=>'Aralık',

];
foreach ($options as $code => $month) {
    $option_month = $Dom->createElement('option');
    $option_month->nodeValue = htmlspecialchars($month);
    $option_month->setAttribute ('value',$code);
    $select->appendChild($option_month);
    $form->appendChild($select);
};


$select_year = $Dom->createElement('select');
$select_year->setAttribute( 'name', 'year' );
$select_year->setAttribute( 'class', 'year' );
$option = ["","2000", "2001", "2002", "2003", "2004", "2005", "2006", "2007","2008","2009","2010","2011","2012","2013","2014","2015","2016","2017","2018","2019","2020","2021","2022"];
for ($i=1; $i<24; $i++){
    $optionyear = $Dom->createElement('option');
    $optionyear->setAttribute('class','__css_weekdays_name');
    $optionyear->nodeValue=$option[$i];
    $select_year->appendChild($optionyear);
    $form->appendChild($select_year);
};

$submit_btn = $Dom->createElement('input');
$submit_btn->setAttribute('type','submit');
$submit_btn->setAttribute('name','submit');
$submit_btn->setAttribute('class','submit_btn');
$form->appendChild($submit_btn);



$calendar_ext= $Dom->createElement('div');
$calendar_ext->setAttribute('class','__css_calendar_ext');
$body->appendChild($calendar_ext);



$calendar_week_all_days_holder=$Dom->createElement('div');
$calendar_week_all_days_holder->setAttribute('class','__css_calendar_week_all_days_holder');
$calendar_ext->appendChild($calendar_week_all_days_holder);

$calendar_weekdays = ["","Pazartesi","Salı","Çarşamba","Perşembe","Cuma","Cumartesi","Pazar"];
for ($i=1; $i<8; $i++){
  $weekdays_name = $Dom->createElement('div');
  $weekdays_name->setAttribute('class','__css_weekdays_name');
  $weekdays_name->nodeValue=$calendar_weekdays[$i];
  $calendar_week_all_days_holder->appendChild($weekdays_name);
}


$calendar_day_all = $Dom->createElement('div');
$calendar_day_all ->setAttribute('class','__css_calendar_day_all');
$calendar_ext->appendChild($calendar_day_all);

$popup= $Dom->createElement('div');
$popup->setAttribute('class', 'popup');
$popup->setAttribute('id', 'popup');
$calendar_ext->appendChild($popup);

$iframe = $Dom->createElement('iframe');
$iframe->setAttribute('class', 'iframe');
$iframe->setAttribute('id', 'iframe');
$popup->appendChild($iframe);

$button_cls = $Dom->createElement('button','&times;');
$button_cls->setAttribute('class','cls_btn');
$button_cls->setAttribute('onclick','closepopup()');
$popup->appendChild($button_cls);


$month = $_POST['month'] ?? 1;
$year = $_POST['year'] ?? 2022;
$daysCount = days_in_month($month, $year);
$weekDays = [
    '',
    'Pazartesi',
    'Salı',
    'Çarşamba',
    'Perşembe',
    'Cuma',
    'Cumartesi',
    'Pazar',
];

$host = "localhost";
$username = "root";
$password= "";
try {
    $conn = new PDO("mysql:host=$host;dbname=event_calendar1;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "";
}
catch(PDOException $e)
{
    echo "Bağlantı hatası: " . $e->getMessage();
}

$query = $conn->prepare('SELECT * FROM events where start_date >= :start_date AND end_date < :end_date');
$query->bindValue(':start_date', $year . '-'.sprintf('%02d', $month) .'-01 00:00:00');
$query->bindValue(':end_date', $year . '-' .sprintf('%02d',$month + 1) .'-01 00:00:00');
$query->execute();
$events = $query->fetchAll();

$events_sorted = [];    //array key
if (is_array($events) && (count($events)>0)) {
foreach ($events as $event){
    $start_date  = new DateTime($event['start_date'], new DateTimeZone('UTC'));
    $end_date  = new DateTime($event['end_date'], new DateTimeZone('UTC'));
    $event['start_date'] = $start_date;
    $event['end_date'] = $end_date;
    $events_sorted[$start_date->format('Y-m-d')][] = $event; //aynı gün olan eventler

  }

}

for ($i = 1; $i <= $daysCount; $i++) {
    $date = new DateTime("$year-$month-$i",new DateTimeZone('UTC')) ;
    $dayOfTheWeek = $date->format('N')  ;

    if ($i == 1) {
        for ($j = 0; $j < $dayOfTheWeek - 1; $j++) {
            $day = createDayElement($date, null, $Dom,null);
            $calendar_day_all->appendChild($day);
        }
    }

    $events_for_the_day = $events_sorted[$date->format('Y-m-d')] ?? null; //istediğim günün eventi
    $day = createDayElement($date, $events_for_the_day, $Dom,$i);
    $calendar_day_all->appendChild($day);
}

echo $Dom->saveHtml();
 function createDayElement(?DateTime $date,?array $_events,DOMDocument $_DomDocument,?int $day_of_the_month){
     $calendar_day_cell = $_DomDocument->createElement('div');
     $calendar_day_cell->setAttribute('class','__css_calendar_day_cell');

     $calendar_month_shell = $_DomDocument->createElement('div');
     $calendar_month_shell->setAttribute('class','__css_calendar_month_shell');
     $calendar_day_cell->appendChild($calendar_month_shell);

     $calendar_day_name = $_DomDocument->createElement('div');
     $calendar_day_name->setAttribute('class', '__css_calendar_mobile_day_name');
     $calendar_month_shell->appendChild($calendar_day_name);

     $calendar_month_label= $_DomDocument->createElement('div');
     $calendar_month_label->setAttribute('class','__css_calendar_month_label');
     $calendar_day_cell->appendChild($calendar_month_label);


     if ( !empty($date)) {
         if  ($day_of_the_month) {
             $day_of_the_month=$date->format('j');
             $date_name=$date->format('l');
             $calendar_day_name->nodeValue = $date_name;
             $calendar_month_label->nodeValue = $day_of_the_month;


             $calendar_events_holder = $_DomDocument->createElement('ul');
             $calendar_events_holder->setAttribute('class','__css_calendar_events_holder');
             $calendar_day_cell->appendChild($calendar_events_holder);

         }
     }
     else {
         return null;
     }



     if ( !empty($_events) ){
     foreach ($_events as $event)
     {
         $calendar_day_event_li = $_DomDocument->createElement('li');
         $calendar_day_event_li->setAttribute('class', '__css_calendar_day_event_li');
         $calendar_events_holder->appendChild($calendar_day_event_li);

         $calendar_day_event_time_label = $_DomDocument->createElement('div');
         $calendar_day_event_time_label->setAttribute('class', '__css_calendar_day_event_time_label');
         $calendar_day_event_li->appendChild($calendar_day_event_time_label);

         $calendar_day_event_time_label->nodeValue = $event['start_date']->format('H:i');


         $calendar_day_event_detail_shell = $_DomDocument->createElement('div');
         $calendar_day_event_detail_shell->setAttribute('class', '__css_calendar_day_event_detail_shell');
         $calendar_day_event_detail_shell->setAttribute('data-event-id', $event['id']);
         $calendar_day_event_detail_shell->setAttribute('onclick','openpopup(event)');
         $calendar_day_event_li->appendChild($calendar_day_event_detail_shell);


         $calendar_day_event_detail_label = $_DomDocument->createElement('div');
         $calendar_day_event_detail_label->setAttribute('class', '__css_calendar_day_event_detail_label');
         $calendar_day_event_detail_label->nodeValue = $event['title'];
         $calendar_day_event_detail_shell->appendChild($calendar_day_event_detail_label);

         }

     }

     return $calendar_day_cell;

 }