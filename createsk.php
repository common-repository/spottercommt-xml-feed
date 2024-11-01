<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

require_once('simplexml.php');

if (!file_exists(wp_upload_dir()['basedir'] . '/spotter')) {
    wp_mkdir_p(wp_upload_dir()['basedir'] . '/spotter');
}

if (!file_exists(wp_upload_dir()['basedir'] . '/spotter/spotter.xml')) {
    touch(wp_upload_dir()['basedir'] . '/spotter/spotter.xml');
}

if (file_exists(wp_upload_dir()['basedir'] . '/spotter/spotter.xml')) {
    $xmlFile = wp_upload_dir()['basedir'] . '/spotter/spotter.xml';
} else {
    echo "Could not create file.";
}

$xml = new spottercommt_SimpleXMLExtended('<?xml version="1.0" encoding="utf-8"?><webstore/>');
$now = date('Y-n-j G:i');
$xml->addChild('created_at', "$now");
$products = $xml->addChild('products');
$xml_rows = generate_products_xml_data();
foreach ($xml_rows as $prod_id => $row) {
    $product = $products->addChild('product');

    $product->sku = NULL;
    $product->sku->addCData(addslashes(trim($row['skus_ds']) == '' ? $prod_id : $row['skus_ds']));

    if (isset($row['gtin'])) {
        $label = array_keys($row['gtin'])[0];
        $product->addChild($label)->addCData($row['gtin'][$label]);
    }

    $product->addChild('uid', $prod_id);
    $product->name = NULL;
    $product->name->addCData($row['title']);
    $product->link = NULL;
    $product->link->addCData($row['link']);

    $product->image = NULL;
    $product->image->addCData($row['image_big']);

    $product->category = NULL;
    $product->category->addCData($row['category_path']);
    if (isset($row['additional_image'])) {
        foreach ($row['additional_image'] as $id) {
            $product->addChild('additional_image')->addCData($id);
        }
    }

    $product->addChild('price_with_vat', $row['price']);

    if (strcmp($row['stockstatus'], "instock") == 0) {
        $product->addChild('instock', "Y");
        $product->addChild('availability', $row['availabilityST']);
    } else {

        if (strcmp($row['backorder'], "notify") == 0) {
            $product->addChild('instock', "N");
            $product->addChild('availability', __('Upon order', 'spotter-wooshop-feed'));
        } else if (strcmp($row['backorder'], "yes") == 0) {
            $product->addChild('instock', "Y");
            $product->addChild('availability', $row['availabilityST']);
        } else {
            $product->addChild('instock', "N");
        }
    }
    $product->addChild('size', $row['sizestring']);

    $product->manufacturer = NULL;
    $product->manufacturer->addCData($row['manufacturer']);

    $product->color = NULL;
    $product->color->addCData($row['colorstring']);
    $product->addChild('weight', floatval($row['_weight_ds']) > 0 ? round(floatval($row['_weight_ds']) * 1000) : 0);
}


echo '</br>' . __('SUCCESSFUL CREATION OF spotter XML @ ' . $now, 'spotter-wooshop-feed') . '</br>';
$xml->saveXML($xmlFile);
echo __('The file is located at', 'spotter-wooshop-feed') . ' <a href="' . wp_upload_dir()['baseurl'] . '/spotter/spotter.xml" target="_blank">' . wp_upload_dir()['baseurl'] . '/spotter/spotter.xml</a>';

// function format_number_spotter($pa_size) {
//     return str_replace(',', '.', $pa_size);
// }