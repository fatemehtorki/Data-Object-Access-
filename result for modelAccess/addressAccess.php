<?php

class addressAccess
{

    private $database;

    public function __construct($db)
    {

        $this->database = $db;
    }


    public function select($where)
    {
        $query = "SELECT * FROM address" . $where;

        $q = $this->database->prepare($query);

        //$q->bindValue(1, $where);

        $q->execute();

        $out = $q->fetchAll(PDO::FETCH_ASSOC);

        return $out;
    }

    public function delete($model)
    {

        $query = "DELETE FROM address WHERE id= ?";

        $q = $this->database->prepare($query);

        $q->bindValue(1, $model->id);

        if ($q->execute()) {
            return true;
        } else {
            echo "failure";
        }

    }


    public function selectById($model)
    {
        $query = "SELECT * FROM address WHERE id= ?";

        $q = $this->database->prepare($query);

        $q->bindValue(1, $model->id);
        $q->execute();

        $out = $q->fetch(PDO::FETCH_ASSOC);
        return $out;
    }


    public function getLatestaddress($start = 0, $end = 20)
    {
        $q = DB::getInstance()->getCon()->query("SELECT * FROM  address ORDER BY id DESC limit $start,$end");

        $out = $q->fetchAll(PDO::FETCH_ASSOC);

        return $out;

    }

    public function insert($model)
    {
        $query = "INSERT INTO address (id, name_of_reciever, phone_home_pre, phone_home, phone_mobile, state_id, city_id, full_address, post_code, lat, lang, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";

        $q = $this->database->prepare($query);


        $q->bindValue(1, $model->id);
        $q->bindValue(2, $model->name_of_reciever);
        $q->bindValue(3, $model->phone_home_pre);
        $q->bindValue(4, $model->phone_home);
        $q->bindValue(5, $model->phone_mobile);
        $q->bindValue(6, $model->state_id);
        $q->bindValue(7, $model->city_id);
        $q->bindValue(8, $model->full_address);
        $q->bindValue(9, $model->post_code);
        $q->bindValue(10, $model->lat);
        $q->bindValue(11, $model->lang);
        $q->bindValue(12, $model->user_id);


        $q->execute();

        $m = new addressModel();
        $m->id = $this->database->lastInsertId();

        $out = $this->selectById($m);
        return $out;

    }

}

?>