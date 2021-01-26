<?php

$host = "89.163.242.115";
$user = "etutsold_mobil";
$pass = "dbmobil1453";
$db = "etutsold_iot";


try {
    //database bağlantısını kuruyoruz
    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $db . ';charset=utf8', $user, $pass);

    //json olarak ekrana bastırılacak error message ve işlem yapılan datayı array olarak döndüren fonksiyon
    function createOutput($error, $response_message, $response_data = array())
    {
        $output_array = array(
            'error' => $error,
            'message' => $response_message,
            'data' => $response_data
        );
        return $output_array;
    }

    //işlem türünü istek olarak alıyoruz
    $operation_type = $_GET['operation_type'];
    //işlem yapılacak bölümü seçiyoruz
    $service_type = $_GET['service_type'];


    if ($operation_type == "get_as_limit" && $service_type == "Channel") {

        $limit = $_GET["limit"];
        $queryChannel = $pdo->prepare("SELECT * FROM Channel");
        $queryChannel->execute();
        $resultChannel = $queryChannel->fetchAll(PDO::FETCH_ASSOC);

        for($i = 0 ; $i<count($resultChannel); $i++){

            $queryFeed =  $pdo->prepare("SELECT * FROM Feed WHERE channel_id = :id ORDER BY created_at DESC LIMIT :lmt");
            $queryFeed->bindParam(":id",$resultChannel[$i]['id'],PDO::PARAM_INT);
            $queryFeed->bindParam(":lmt",$limit,PDO::PARAM_INT);
            $queryFeed->execute();
            $resultFeed = $queryFeed->fetchAll(PDO::FETCH_ASSOC);

            $data[$i] = array(
                'Channel' => $resultChannel[$i],
                'Feed' => $resultFeed
            );

        }

        if (count($data) == 0) {

            $output = createOutput('true', 'Hatalı Giriş', []);
            echo json_encode($output);
            return;
        }

        $output = createOutput('false', "Veriler Getirildi",$data);
        echo json_encode($output);
    }elseif ($operation_type=='get_channel'&$service_type=='channel'){

        $id=$_GET['id'];
        $queryChannel = $pdo->prepare("SELECT * FROM Channel WHERE id=:id");
        $queryChannel->bindParam(":id", $id, PDO::PARAM_INT);
        $queryChannel->execute();
        $resultChannel = $queryChannel->fetchAll(PDO::FETCH_ASSOC);
        $queryFeed =  $pdo->prepare("SELECT * FROM Feed WHERE channel_id = :id ORDER BY created_at DESC");
        $queryFeed->bindParam(":id", $id, PDO::PARAM_INT);
        $queryFeed->execute();
        $resultFeed = $queryFeed->fetchAll(PDO::FETCH_ASSOC);

        $data= array(
            'Channel' => $resultChannel,
            'Feed' => $resultFeed
        );

        if (count($data) == 0) {

            $output = createOutput('true', 'Hatalı Giriş', []);
            echo json_encode($output);
            return;
        }

        $output = createOutput('false', "Veriler Getirildi",$data);
        echo json_encode($output);

    }
    else if($operation_type == 'add_channel' && $service_type == 'Channel' ){

        $channel_name = $_GET['channel_name'];
        $description = $_GET['description'];
        $latitude = $_GET['latitude'];
        $longitude = $_GET['longitude'];
        $field1 = $_GET['field1'];
        $created_at = date('Y-m-d H:i:s');


        $query = $pdo->prepare("INSERT INTO Channel( channel_name, description, latitude, longitude, field1, created_at) VALUES ( :channel_name, :description, :latitude, :longitude, :field1, :created_at)");
        $query->bindParam(":channel_name",$channel_name,PDO::PARAM_STR);
        $query->bindParam(":description",$description,PDO::PARAM_STR);
        $query->bindParam(":latitude",$latitude,PDO::PARAM_INT);
        $query->bindParam(":longitude",$longitude,PDO::PARAM_INT);
        $query->bindParam(":field1", $field1,PDO::PARAM_STR);
        $query->bindParam(":created_at",$created_at,PDO::PARAM_STR);
        $process = $query->execute();

        if(!$process){
            $output = createOutput('true', 'Bir Hata Oluştu', []);
            echo json_encode($output);
            return;
        }

        $data = array(

            'channel_name' => $channel_name,
            'description' => $description,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'field1' => $field1,
            'created_at' => $created_at,
        );

        $output = createOutput('true', 'Yeni Channel Eklendi', $data);
        echo json_encode($output);
        return;

    }else if($operation_type == "add_feed" && $service_type = "Feed"){


        $c_id = $_GET['channel_id'];
        $field = $_GET['field'];
        $created_at = date('Y-m-d H:i:s');

        $query = $pdo->prepare("INSERT INTO Feed(channel_id, field1, created_at) VALUES( :channel_id, :field, :created_at)");
        $query->bindParam(":channel_id", $c_id, PDO::PARAM_INT);
        $query->bindParam(":field", $field, PDO::PARAM_STR);
        $query->bindParam("created_at",$created_at, PDO::PARAM_STR);
        $process = $query->execute();

        if(!$process){
            $output = createOutput('true', 'Bir Hata Oluştu', []);
            echo json_encode($output);
            return;
        }

        $data = array(
            'channel_id' => $c_id,
            'field1' => $field,
            'created_at' => $created_at,
        );

        $output = createOutput('true', 'Yeni Feed Eklendi', $data);
        echo json_encode($output);
        return;

    }
    else{
        echo "ERROR ! 404 NOT FOUND...";
    }

}catch (PDOException $e){
    echo "Error!: ".$e->getMessage();
}
?>