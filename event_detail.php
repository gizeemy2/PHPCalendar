<?php

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

$event_id = isset( $_GET['event_id']) ? $_GET['event_id'] : '';

$sql = "SELECT * FROM events WHERE id = :event_id";
$result = $conn->prepare($sql);
$result->bindValue(':event_id', $event_id);
$result->execute();
$event = $result->fetchAll();

$event=$event[0] ?? null;

if (!is_array($event)) {
    echo "Event yok!";
}

else {
    $doc = new DOMDocument();

    $root = $doc->createElement('html');
    $doc->appendChild($root);

    $body = $doc->createElement('body');
    $root->appendChild($body);

    $title = "Etkinlik : " . $event['title'];
    $text = $doc->createTextNode($title);
    $h1 = $doc->createElement('h1');
    $h1->appendChild($text);
    $body->appendChild($h1);


    $start_date = "Etkinlik başlangıç  : " . $event['start_date'];
    $text = $doc->createTextNode($start_date);
    $p = $doc->createElement('p');
    $p->appendChild($text);
    $body->appendChild($p);

    $end_date = "Etkinlik bitiş  : " . $event['end_date'];
    $text = $doc->createTextNode($end_date);
    $p = $doc->createElement('p');
    $p->appendChild($text);
    $body->appendChild($p);


    $p = $doc->createElement('p', $event['description']);
    $body->appendChild($p);

    $organizator = "Organizator : " . $event['organizer'];
    $text = $doc->createTextNode($organizator);
    $p = $doc->createElement('p');
    $p->appendChild($text);
    $body->appendChild($p);


    $number_participants = "Katılımcı sayısı : " . $event['number_participant'];
    $text = $doc->createTextNode($number_participants);
    $p = $doc->createElement('p');
    $p->appendChild($text);
    $body->appendChild($p);

    $location = "Yer : " . $event['location'];
    $text = $doc->createTextNode($location);
    $p = $doc->createElement('p');
    $p->appendChild($text);
    $body->appendChild($p);

    echo $doc->saveHtml();


}

