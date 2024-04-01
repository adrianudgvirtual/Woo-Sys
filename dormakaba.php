<?php

require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

// Conexión WooCommerce API destino
// ================================
$url_API_woo = 'https://conmujal.com';
$ck_API_woo = 'ck_a2327d9368eb5216794707a0e015bbbb75ceea9c';
$cs_API_woo = 'cs_f48fd759190c338339e6d0b4ee88f76ee181c743';

$woocommerce = new Client(
    $url_API_woo,
    $ck_API_woo,
    $cs_API_woo,
    [wp_api =>true, 'version' => 'wc/v3']
);

// Obtener Token Syscom
// ===================

$url_TOKEN="https://developers.syscom.mx/oauth/token";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url_TOKEN);
curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=Sjjefyds1qdQnyurqjd17qKFKY0mfy4K&client_secret=QtcEMPSi3jTt5XNoOT3VrxxKu4RrmKpgcaMllDNO&grant_type=client_credentials");

// Recibimos la respuesta y la guardamos en una variable
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$remote_server_output = curl_exec ($ch);

curl_close ($ch);

$respuesta = json_decode($remote_server_output, true);
$token=$respuesta[access_token];

// Conexión API origen
// ===================

//Sacamos el numero de paginas que tiene la marca
$url_API="https://developers.syscom.mx/api/v1/marcas/dormakaba/productos";

$ch = curl_init();
$authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url_API);

$items_origin = curl_exec($ch);
curl_close($ch);

$items_origin = json_decode($items_origin, true);

$paginas = $items_origin[paginas];


//Sacamos tipo de cambio
$url_API="https://developers.syscom.mx/api/v1/tipocambio";

$ch = curl_init();
$authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url_API);

$items_origin = curl_exec($ch);
curl_close($ch);

$items_origin = json_decode($items_origin, true);

//VARIABLES
$tipocambio = $items_origin[normal];
$iva = 1.16;
$utilidad = 1.30;




//Ciclo para recorrer todas las paginas de la marca
// ===================

for ($i=1; $i<=$paginas; $i++){
    $url_API="https://developers.syscom.mx/api/v1/marcas/dormakaba/productos?pagina=$i";

    $ch = curl_init();
    $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$url_API);

    echo "➜ Obteniendo datos origen ... \n";

    $items_origin = curl_exec($ch);
    curl_close($ch);

    if ( ! $items_origin ) {
        exit('❗Error en API origen');
    }

    // Obtenemos datos de la API de origen
    $items_origin = json_decode($items_origin, true);

    // Formamos el parámetro de lista de SKUs a actualizar

    $param_sku='';

    foreach($items_origin['productos'] as $item => $detalles)
    {
        $param_sku .= $detalles['modelo'] . ',';

        foreach($detalles as $indice => $valores)
        {
            //echo "<h2> $indice:$valores</h2>";

            foreach($valores as $valor => $descripciones)
            {
                //echo "<h3>$valor:$descripciones</h3>";

                foreach($descripciones as $descripcion =>$descripciones2)
                {
                    //echo "<p>$descripcion:$descripciones2</p>";
                }
            }
        }
    }

    echo "➜ Obteniendo los ids de los productos... \n";

    //var_dump ($param_sku);

    // Obtenemos todos los productos de la lista de SKUs
    $products = $woocommerce->get('products/?sku='. $param_sku);

    //var_dump ($products);

    // Construimos la data en base a los productos recuperados
    $item_data = [];
    foreach($products as $product){

        // Filtramos el array de origen por sku
        $sku = $product->sku;
        $search_item = array_filter($items_origin['productos'], function($detalles) use($sku) {
            return $detalles['modelo'] == $sku;
        });

        //var_dump ($search_item);

        $search_item = reset($search_item);

        // Formamos el array a actualizar
        $item_data[] = [
            'id' => $product->id,
            'regular_price' => ($search_item['precios']['precio_descuento'])*$iva*$tipocambio*$utilidad,
            'stock_quantity' => $search_item['total_existencia'],
        ];

    }

    // Construimos información a actualizar en lotes
    $data = [
        'update' => $item_data,
    ];

    echo "➜ Actualización en lote ... \n";
    // Actualización en lotes
    $result = $woocommerce->post('products/batch', $data);

    if (! $result) {
        echo("❗Error al actualizar productos <br/>");
    } else {
        echo("✔ Productos actualizados correctamente pagina: $i <br/>");
    }
}

?>
