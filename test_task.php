$input = [ 
	"submit_url" => "https://something/submit.php", 
	"auth_url"   => "https://home/auth.php" 
];
function query($sql) { 
	// don’t write implementation, imagine it is already done 
	// runs $sql in your DB 
	// returns 2-dimensional array of data
}
function secure_key($service_type, $customer_id) { 
	// don’t write implementation, imagine it is already done 
	// returns a string 
	// * the key may contain non-alphanumeric chars

}
function order_submit($url) { 
	// don’t write implementation, imagine it is already done 
	// makes a GET http request 
	// returns invoice_id (integer) 
	// * may throw an exception on error
}

$result = [];
$orders = query('SELECT `orders`.*, `charges`.`sum_prices`, `charges`.`list_prices` FROM `orders` LEFT JOIN (SELECT `order_id`, SUM(`value`) as `sum_prices`, GROUP_CONCAT(DISTINCT `price_entity_id`) as `list_prices` FROM `order_charges` GROUP BY `order_id`) as `charges` ON `charges`.`order_id`=`orders`.`order_id` ORDER BY `orders`.`order_id` ASC LIMIT 100');
foreach($orders as $order){
	try{
		$result[$order['order_id']]['sum'] = empty($result[$order['order_id']]['sum']) ? $order['sum_prices'] : $result[$order['order_id']]['sum']+$order['sum_prices'];
		$result[$order['order_id']]['invoice_id'][] = $invoice_id;
		
		$key = htmlspecialchars(secure_key($order['service_id'], $order['customer_id']));
		$auth_url = $input['auth_url']."?order_id={$order['order_id']}&secure_key={$key}";
		$invoice_id = order_submit($input["submit_url"]."?order_id={$order['order_id']}&amount={$order['sum_prices']}&prices={$order['list_prices']}&auth_url={$auth_url}");
		
		$result[$order['order_id']]['has_error'] = !empty($result[$order['order_id']]['has_error']) && $result[$order['order_id']]['has_error'] ? true : false;
	} catch (Exception $e) {
		$result[$order['order_id']]['has_error'] = true;
	}
}

$json = json_encode($result);
return $json;
