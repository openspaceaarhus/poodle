<?php

setlocale(LC_TIME, "da_DK.utf8");

function get_poodle_uuid() {
  $salt = "4d9de2624eb26";
  $raw_id = $salt . uniqid();
  return sha1($raw_id);
}

function cell($s) {
    echo "<td>" . $s . "</td>\n";
 }

function cell_h($s) {
    echo "<th>" . $s . "</th>\n";
 }



function template_header($title, $pid) {
  return "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
  <head>

    <title>Poodle</title>
    <link rel='stylesheet' href='poodle.css'/>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <link href='fonts/bender-black.css' rel='stylesheet' type='text/css'>
    <link rel='shortcut icon' type='image/x-icon' href='favicon.ico'>
<script  type='text/javascript'> var pid='$pid';</script>
<script  type='text/javascript' src='./js/jq.js' ></script>
<style type='text/css'>import 'js/jquery.countdown.css';</style> 
<script type='text/javascript' src='js/jquery.countdown.js'></script>
<script  type='text/javascript' src='./js/hello.js' ></script>
  </head>
<body>
<canvas id='canvas' > <h1>no canvas for you</h1> </canvas>
<div id='wrapper'>

  <div id='header'>
<div id='top'>
<div id='msg'> </div>
    <div id='countdown'>&nbsp;</div>
</div>
    <img src='images/poodle-logo.png'><h1>Poodle</h1>

  </div>
    <div id='content'>
";
}

function template_footer() {
return "

   </div>
  </div>

</body>
</html>
";
}

function create_poodle() {
    return '<img src="./images/poodle.png" alt="no such poodle" />';
}

function create_unicornpoodle() {
        return '<img src="./images/unicorn.jpg" alt="no such poodle" />';
}

function get_dbconnection() {
    $tmp = pg_connect("dbname=poodle user=poodle") or die("Could not connect");
    return $tmp;
}

function pizza_adder($orderid) {
    $str=<<<EOT
        <form name="input" action="index.php?id=$orderid" method="post">
        <table>
        <tr>
        <td>Name:</td>
        <td><input type="text" name="username"/></td><td> </td>
        </tr>

        <tr>
        <td>Pizza:</td>
        <td><input type="text" name="pizza_id"/></td><td>fx 12a</td>
        </tr>

        <tr>
        <td>Price:</td>
        <td><input type="text" name="price" value="60" /> </td><td></td>
        </tr>

        <tr>
        <td>Comment:</td>
        <td><input type="text" name="comment"/></td><td>fx "please draw a unicorn on the box"</td>
        </tr>
        <tr><td></td><td class='submit'><input type="submit" name="ADD" value="Go Fetch!"/></td><td></td></tr>
        </table>
        </form>

EOT;
    return $str;
}

function clean_str($s) {
    return htmlspecialchars($s);
}

function edit_pizza($row, $poodle_id, $i) {
    $checked = $row['paid']=='t' ? "checked" : "";
    $str=<<<EOT
        <tr>
        <form name="input" action="index.php?id=$poodle_id" method="post">
        <input type="hidden" name="pid" value="$row[id]" />
        <td>$i</td>
        <td><input type="text" name="username" value="$row[username]"/> </td>
        <td><input type="text" name="pizza_id" value="$row[pizza_id]"/> </td>
        <td><input type="text" name="comment" value="$row[comment]"/> </td>
        <td><input type="text" name="price" value="$row[price]"/> </td>
        <!--- TODO onclick submit --->
        <td><input type="checkbox" name="paid" $checked /> </td>
        <td> <input type="submit" name="EDIT" value="edit" /> </td>
        <td> <input type="submit" name="DELETE" value="delete" /> </td>
        </form>
        </tr>
EOT;
    return $str;
}

function check_error($res) {
    if (!$res) {
        echo "errorhandling";
        echo create_unicornpoodle();
    }
}

function create_form() {
    $conn = get_dbconnection();
    $res = pg_prepare($conn, "create", "SELECT name, id FROM pizza_place ORDER BY id"); 
    check_error($res);
    $res = pg_execute($conn, "create", array());

    $str = '<form name="input" action="create.php" method="post">
            pizza place: <select name="pizza_place">';

    while ($row = pg_fetch_assoc($res)) {
        $val = $row['id'];
        $name = $row['name'];
        $str .= "<option value=\"$val\">$name</option>";
    }

    $str .= "</select> <br />";

    $str .= '
      Order title: <input type="text" name="order_title" />
        <br />
      collector: <input type="text" name="collector" />
        <br />
      driver: <input type="text" name="driver" />
        <br />
        <input type="submit" value="Submit" />
      </form>';

    pg_close($conn);

    return $str;
}


function pizza_place($conn, $id) {
    $res = pg_prepare($conn, "place", "SELECT * FROM pizza_place WHERE id=$1");
    check_error($res);
    $res = pg_execute($conn, "place", array($id));
    check_error($res);
    $row = pg_fetch_assoc($res);
    check_error($row);
    
    $str = "<p>We are ordering food from ";
    $str .= '<a target="_blank" href="' . $row['url'] . '" >' . $row['name'] . '</a>. ';
    $str .= 'See the <a target="_blank" href="' . $row['catalog_url'] . '" >menu</a></p>'; 
    
    

    //TODO use a logo image some how
    $str .= '<p>Call them at ' . $row['phone_1'];
    $phone_2 = $row['phone_2'];
    if ($phone_2 != NULL && $phone_2 != 0)
        $str .= " or $phone_2";
    $str .= "</p>";
    return $str;
}

function get_paid($conn, $order_id) {

    $res = pg_prepare($conn, "paid", "SELECT SUM(price) FROM pizza WHERE order_id=$1 AND paid");
    check_error($res);
    $res = pg_execute($conn, "paid", array($order_id));
    check_error($res);
    $row = pg_fetch_row($res);
    check_error($row);
    return $row[0];

}


function get_total($conn, $order_id) {

    $res = pg_prepare($conn, "total", "SELECT SUM(price) FROM pizza WHERE order_id=$1");
    check_error($res);
    $res = pg_execute($conn, "total", array($order_id));
    check_error($res);
    $row = pg_fetch_row($res);
    check_error($row);
    return $row[0];

}

function get_timestamp($str) {
  if( $str == "")
    return NULL;
  $tmp = date("Y m d ") . $str;
  return $tmp;
}


function edit_order($row) {
  $poodle_id = $row['admin_uuid'];
  
  $pickup_time = isset($row['pickup_time']) ? date("H:i", strtotime($row['pickup_time'])) : "";
  $order_time = isset($row['order_time']) ? date("H:i", strtotime($row['order_time'])) : "";
    $str=<<<EOT
      <table>
      <tr>
      <form name="input" action="index.php?id=$poodle_id" method="post">
      <td>Collector:<input type="text" name="collector" value="$row[collector]"/> </td>
      <td>Driver:<input type="text" name="driver" value="$row[driver]"/> </td>
      <td>pickup time<input type="text" name="pickup_time" value="$pickup_time"/> </td>
      <td>order time<input type="text" name="order_time" value="$order_time"/> </td>
      
      <td> <input type="submit" name="EDIT_ORDER" value="edit" /> </td>
      </form>
      </tr>
EOT;
    return $str;
}


function show_poodle_list($heading, $res, $conn) {
   echo "<h2>$heading</h2>";
   echo "<table class='ordertable'>";
   echo "<tr>";
    cell_h("&nbsp;");
    cell_h("Driver");
    cell_h("Collector");
    cell_h("Place");
    cell_h("Created");
    cell_h("Access");
    echo "</tr>";

    while ($row = pg_fetch_assoc($res)) {
        echo "<tr>";
        $order_title = $row['order_title'];
        $driver = $row['driver'];
        $collector = $row['collector'];
        $uuid = $row['user_uuid'];
        

        $name_res = pg_query_params($conn, "SELECT name FROM pizza_place WHERE id=$1", array($row['pizza_place']));
        check_error($name_res);
        $name_row = pg_fetch_row($name_res);
        check_error($name_row);
        $pizza_place = $name_row ? $name_row[0] : "unknown pizzapplace";

        $created = $row['created'];

        cell($order_title);
        cell($driver);
        cell($collector);
        cell($pizza_place);
        cell(strftime("%A den %e. %B kl %R", strtotime($created)));
        cell("<a href=\"index.php?id=$uuid\" >Show poodle </a>");
        echo "</tr>";
    }
    echo "</table>";


}

function show_poodles() {
    $conn = get_dbconnection();
    $res = pg_prepare($conn, "live", "SELECT * FROM pizza_order WHERE pickup_time IS NULL
                                       AND date_part('epoch', age(order_time)) < 36*24*60
                                       ORDER BY created ASC");
    check_error($res);
    $res = pg_execute($conn, "live", array());
    show_poodle_list("Active poodles", $res, $conn);

//dead poodles
    $res = pg_prepare($conn, "dead", "SELECT * FROM pizza_order
                                      WHERE  NOT pickup_time IS NULL
                                       ORDER BY created DESC
                                       LIMIT 5");
    check_error($res);
    $res = pg_execute($conn, "dead", array());
    show_poodle_list("Recently deceased poodles", $res, $conn);


    pg_close($conn);

}

?>
