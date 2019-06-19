<?php 

    require_once("DBconfig.php");

    $JSON = file_get_contents("bouquetTEST.json");
    $file = json_decode($JSON, true);

    filter($file);

    function filter($file) {

        $countProducts = 0;
        $countItems = 0;

        foreach($file as $key => $value) {

            list($type, $size) = explode("-", $value["product"]);

            if(empty($size)) {
                $size = "default";
            }

            $price = $value["price"];
            $productID = checkForProductID($type);
            
            if($productID == null) {
                $productID = insertProduct($type);
                $countProducts++;
                insertItem($productID, $size, $price);
                $countItems++;
            }
            else {

                $itemID = checkForItemID($size, $productID);

                if($itemID == null) {
                    insertItem($productID, $size, $price);
                    $countItems++;
                }

            }

        }

        print("Er zijn ".$countProducts." producten toegevoegd aan tabel product_items.<br>");
        print("Er zijn ".$countItems." items toegevoegd aan tabel productxprices.");

    }

    function checkForProductID($type) {

        $query = "SELECT productID FROM product_items WHERE name = :name";
        $dbh = new PDO(DBconfig::$DB_CONNSTR, DBconfig::$DB_USERNAME, DBconfig::$DB_PASSWORD);
        $stmt = $dbh->prepare($query);
        $stmt->execute([':name' => $type]);
        $productID = $stmt->fetchColumn();
        $dbh = null;

        return $productID;

    }

    function checkForItemID($size, $productID) {

        $query = "SELECT pricesID FROM productxprices WHERE product_name = :size AND productID = :productID";
        $dbh = new PDO(DBconfig::$DB_CONNSTR, DBconfig::$DB_USERNAME, DBconfig::$DB_PASSWORD);
        $stmt = $dbh->prepare($query);
        $stmt->execute([
            ':size' => $size,
            ':productID' => $productID
        ]);
        $itemID = $stmt->fetchColumn();
        $dbh = null;

        return $itemID;

    }

    function insertProduct($type) {

        $query = "INSERT INTO product_items (name, btw_perc) VALUES (:name, 6)";
        $dbh = new PDO(DBconfig::$DB_CONNSTR, DBconfig::$DB_USERNAME, DBconfig::$DB_PASSWORD);
        $stmt = $dbh->prepare($query);
        $stmt->execute([':name' => $type]);
        $productID = $dbh->lastInsertId();
        $dbh = null;

        return $productID;

    }

    function insertItem($productID, $size, $price) {

        $query = "INSERT INTO productxprices (productID, product_name, price) VALUES (:productID, :size, :price)";
        $dbh = new PDO(DBconfig::$DB_CONNSTR, DBconfig::$DB_USERNAME, DBconfig::$DB_PASSWORD);
        $stmt = $dbh->prepare($query);
        $stmt->execute([
            ':productID'   => $productID,
            ':size'        => $size,
            ':price'       => $price
        ]);
        $productID = $dbh->lastInsertId();
        $dbh = null;

    }