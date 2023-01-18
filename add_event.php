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


if ( isset($_POST) && is_array($_POST) && (count($_POST) > 0) ) { //parametre olarak aldığı değişkenin/değişkenlerin tanımlı olup olmadığını kontrol eder.

    $errors = array();

    if (isset($_POST['title']) && preg_match("/^[\p{L}\p{P}\ 0-9]+$/u", $_POST['title']))
    {
        $title = $_POST['title'];
    } else {
        $errors[]="Başlık tanımlı değil veya geçersiz karakter kullanılıyor";
    }


    if (isset($_POST['description']) && preg_match("/^[\p{L}\p{P}\s0-9]+$/u", $_POST['description']))
    {
        $description= $_POST['description'];
    } else {
        $errors[]="Açıklama tanımlı değil veya geçersiz karakter kullanılıyor";
    }


    if (isset($_POST['organizer']) && preg_match("/^[\p{L}\p{P}\s]+$/u", $_POST['organizer'])) //sayı girilemez
    {
        $organizer = $_POST['organizer'];
    } else {
        $errors[]="Organizatör tanımlı değil ,geçersiz karakter içeriyor olabilir ve sayı içermemelidir";
    }

    if (isset($_POST['location']) && preg_match("/^[\p{L}\p{P}\s]+$/u", $_POST['location'])) //sayı girilemez
    {
        $location = $_POST['location'];
    } else {
        $errors[]="Konum tanımlı değil ,geçersiz karakter içeriyor olabilir ve sayı içermemelidir";
    }

    if (isset($_POST['number_participant']) && preg_match("/^\d+$/", $_POST['number_participant'])) //sadece sayı girilir
    {
        $number_participant = $_POST['number_participant'];
    } else {
        $errors[]="Katılımcı sayısı tanımlı değil , geçersiz karakter içeriyor olabilir , sadece sayı içermelidir";
    }

    $MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    $CHROME_DATETIME_FORMAT = 'Y-m-d\TH:i';
    if ( isset($_POST["start_date"]) ){
        $start_date_g_mysql = null;
        try {
            $start_date_g = DateTime::createFromFormat($CHROME_DATETIME_FORMAT, $_POST["start_date"]);

            $start_date_limit = DateTime::createFromFormat($MYSQL_DATETIME_FORMAT, '2000-01-01 00:00:00');
            if ( $start_date_g < $start_date_limit ){
                $errors[] =  "Tarih 2000-01-01'den büyük veya ona eşit olmalıdır" ;
                $start_date_g = null;
            } else {
                $start_date_g_mysql = $start_date_g->format($MYSQL_DATETIME_FORMAT);
            }
        } catch (Exception $exception){
            $errors[] =  "Lütfen YYYY-MM-DD HH:ii:ss formatında geçerli bir tarih giriniz" ;
        }
    } else {
        $errors[] =  "Başlangıç tarihi boş olamaz";
    }

    if ( isset($_POST["end_date"]) ){
        $end_date_g_mysql = null;
        try {
            $end_date_g = DateTime::createFromFormat($CHROME_DATETIME_FORMAT, $_POST["end_date"]);
            $end_date_limit = DateTime::createFromFormat($MYSQL_DATETIME_FORMAT, '2023-12-31 23:59:59');
            if ( $end_date_g > $end_date_limit ){
                $errors[] =  "Tarih 2023-12-31'den küçük veya eşit olmalıdır" ;
                $end_date_g = null;
            } else {
                $end_date_g_mysql = $end_date_g->format($MYSQL_DATETIME_FORMAT);
            }
        } catch (Exception $exception){
            $errors[] =  "Lütfen YYYY-MM-DD HH:ii:ss formatında geçerli bir tarih giriniz" ;
        }
    } else {
        $errors[] =  "Başlangıç tarihi boş olamaz";
    }

    if ( strlen($_POST["title"])<5)  {
        $errors[] = "Başlık en az 5 karakterden oluşmalıdır.";
    }

    if ( strlen($_POST["description"])<10 )  {
        $errors[] = "Açıklama en az 10 karakterden oluşmalıdır.";
    }

    if ( strlen($_POST["organizer"])<5 )  {
        $errors[] = "Organizatör adı en az 5 karakterden oluşmalıdır.";
    }
    if ( strlen($_POST["location"])<5 )  {
        $errors[] = "Lokasyon adı en az 5 karakterden oluşmalıdır.";
    }
    if ( $_POST["number_participant"]>30 )  {
        $errors[] = "Katılımcı sayısı en fazla 30 olmalıdır.";
    }


    if (empty($errors)) { //hata arrayi boşsa kayıt ediyorum.
        $sql = "INSERT INTO events (start_date, end_date, title, description,organizer,location,number_participant) VALUES (:start_date, :end_date, :title, :description,:organizer,:location,:number_participant)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":start_date", $start_date_g_mysql);
        $stmt->bindParam(":end_date", $end_date_g_mysql);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":organizer", $organizer);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":number_participant", $number_participant);

        if ($stmt->execute()) {
            echo "Etkinlik başarıyla eklendi.";
        } else {
            echo "Etkinlik eklenirken hata oluştu.";
        }
        exit();
    } else { //boş değilse hataları liste şeklinde ekrana yazıyorum.

        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        exit();
    }

}

$Dom = new DOMDocument();

$head = $Dom->createElement('head');
$Dom->appendChild($head);

$style = $Dom->createElement('link');
$style->setAttribute( 'rel', 'stylesheet' );
$style->setAttribute( 'type', 'text/css' );
$style->setAttribute('href','calendar.css');
$head->appendChild($style);


$body = $Dom->createElement('body');
$Dom->appendChild($body);

$popup_add_event= $Dom->createElement('div');
$popup_add_event->setAttribute('class', 'add_event_popup');
$popup_add_event->setAttribute('id', 'add_event_popup');
$body->appendChild($popup_add_event);

$form_add_event = $Dom->createElement('form');
$form_add_event->setAttribute('method','post');
$form_add_event->setAttribute('action','');
$form_add_event->setAttribute('class','form_add_event');
$popup_add_event->appendChild($form_add_event);


$title = $Dom->createElement("h2", " Etkinlik Ekle  ");
$form_add_event->appendChild($title);

$startDateLabel = $Dom->createElement("label", "Başlangıç Tarihi : ");
$startDateLabel->setAttribute("for", "startDate");
$startDateLabel->setAttribute('type','date');
$form_add_event->appendChild($startDateLabel);


$startDateInput = $Dom->createElement("input");
$startDateInput->setAttribute("class", "input");
$startDateInput->setAttribute("type", "datetime-local");
$startDateInput->setattribute('min','2000-01-01');
$startDateInput->setAttribute("id", "startDate");
$startDateInput->setAttribute("name", "start_date");
$form_add_event->appendChild($startDateInput);

$br2 = $Dom->createElement("br");
$form_add_event->appendChild($br2);

$endDateLabel = $Dom->createElement("label", "Bitiş Tarihi: ");
$endDateLabel->setAttribute("for", "endDate");
$form_add_event->appendChild($endDateLabel);

$endDateInput = $Dom->createElement("input");
$endDateInput->setAttribute("type", "datetime-local");
$endDateInput->setAttribute("class", "input");
$endDateInput->setAttribute("id", "endDate");
$endDateInput->setAttribute("name", "end_date");
$form_add_event->appendChild($endDateInput);

$br3 = $Dom->createElement("br");
$form_add_event->appendChild($br3);

$titleLabel = $Dom->createElement("label", "Başlık : ");
$titleLabel->setAttribute("for", "title");
$form_add_event->appendChild($titleLabel);

$titleInput = $Dom->createElement("input");
$titleInput->setAttribute("type", "text");
$titleInput->setAttribute("class", "input");
$titleInput->setAttribute("id", "title");
$titleInput->setAttribute("name", "title");
$form_add_event->appendChild($titleInput);
$br4 = $Dom->createElement("br");
$form_add_event->appendChild($br4);

$descriptionLabel = $Dom->createElement("label", "Açıklama : ");
$descriptionLabel->setAttribute("for", "description");
$form_add_event->appendChild($descriptionLabel);

$descriptionInput = $Dom->createElement("textarea");
$descriptionInput->setAttribute("id", "description");
$descriptionInput->setAttribute("class", "input-desc");
$descriptionInput->setAttribute("name", "description");
$form_add_event->appendChild($descriptionInput);
$br6 = $Dom->createElement("br");
$form_add_event->appendChild($br6);

$organizerlabel = $Dom->createElement("label", "Organizator : ");
$organizerlabel->setAttribute("for", "organizer");
$form_add_event->appendChild($organizerlabel);

$organizerInput = $Dom->createElement("input");
$organizerInput->setAttribute("id", "organizator");
$organizerInput->setAttribute("class", "input");
$organizerInput->setAttribute("name", "organizer");
$form_add_event->appendChild($organizerInput);
$br7 = $Dom->createElement("br");
$form_add_event->appendChild($br7);

$locationlabel = $Dom->createElement("label", "Konum : ");
$locationlabel->setAttribute("for", "location");
$form_add_event->appendChild($locationlabel);

$locationInput = $Dom->createElement("input");
$locationInput->setAttribute("id", "location");
$locationInput->setAttribute("class", "input");
$locationInput->setAttribute("name", "location");
$form_add_event->appendChild($locationInput);
$br8 = $Dom->createElement("br");
$form_add_event->appendChild($br8);

$number_participant = $Dom->createElement("label", "Katılımcı sayısı : ");
$number_participant->setAttribute("for", "katılımcı sayısı");
$form_add_event->appendChild($number_participant);


$number_participant = $Dom->createElement("input");
$number_participant->setAttribute("id", "number_participant");
$number_participant->setAttribute("class", "input");
$number_participant->setAttribute("name", "number_participant");
$form_add_event->appendChild($number_participant);
$br8 = $Dom->createElement("br");
$form_add_event->appendChild($br8);


$submit_add_event_btn = $Dom->createElement("input");
$submit_add_event_btn->setAttribute('type','submit');
$submit_add_event_btn->setAttribute("class", "input");
$submit_add_event_btn->setAttribute('name','submit');
$submit_add_event_btn->setAttribute('class','submit_add_event_btn');
$form_add_event->appendChild($submit_add_event_btn);


echo $Dom->saveHtml();