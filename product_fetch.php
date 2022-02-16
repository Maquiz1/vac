<?php
include 'database_connection.php';
include 'function.php';


$query = ' ';
$output = array();
$query .= "SELECT * FROM product
INNER JOIN brand
ON brand.brand_id = product.product_id
INNER JOIN category 
ON category.category_id = product.category_id
INNER JOIN user 
ON user.user_id = product.product_entered_by
";

if (isset($_POST["search"]["value"])) {
    $query .= 'WHERE brand.brand_name LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= 'OR category.category_name LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= 'OR product.product_name LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= 'OR product.product_quantity LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= 'OR user.user_name LIKE "%' . $_POST["search"]["value"] . '%" ';
    $query .= 'OR product.product_id LIKE "%' . $_POST["search"]["value"] . '%" ';
}

if (isset($_POST["order"])) {
    $query .= 'ORDER BY ' . $_POST['order']['0']['column'] . '' . $_POST['order']['0']['dir'] . ' ';
} else {
    $query .= 'ORDER BY product.product_id DESC ';
}


if ($_POST['length'] != -1) {
    $query .= ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}


$statement = $connect->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$data = array();
$filtered_rows = $statement->rowCount();
foreach ($result as $row) {
    $status = ' ';
    if ($row['product_status'] == 'active') {
        $status = '<span class="label label-success">Active</span>';
    } else {
        $status = '<span class="label label-danger">Inactive</span>';
    }

    $sub_array = array();
    $sub_array[] = $row['product_id'];
    $sub_array[] = $row['category_name'];
    $sub_array[] = $row['brand_name'];
    $sub_array[] = $row['product_name'];
    // $sub_array[] = $row['product_quantity'];
    $sub_array[] = available_product_quantity($connect, $row["product_id"]) . ' ' . $row["product_id"];
    $sub_array[] = $row['user_name'];
    $sub_array[] = $status;
    $sub_array[] = '<button type="button" name="view" id="' . $row['product_id'] . '" class="btn btn-warning btn-xs view">View</button>';
    $sub_array[] = '<button type="button" name="update" id="' . $row['product_id'] . '" class="btn btn-warning btn-xs update">Update</button>';
    $sub_array[] = '<button type="button" name="delete" id="' . $row['product_id'] . '" class="btn btn-danger btn-xs delete" data-status="' . $row['product_status'] . '">Delete</button>';
    $data[] = $sub_array;
}

// Response
$output = array(
    "draw" => intval(intval($_POST["draw"])),
    "recordsTotal" => $filtered_rows,
    "recordsFiltered" => get_total_all_records($connect),
    "data" => $data
);

function get_total_all_records($connect)
{
    $statement = $connect->prepare('SELECT * FROM product');
    $statement->execute();
    return $statement->rowCount();
}

echo json_encode($output);