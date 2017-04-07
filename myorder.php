<?php
/*
Plugin Name: My Quick Order
Plugin URI: http://adroittechnosys.com/
Description: Easy Quick Order Functionality For End User & Register User
Author: Keval Thacker - Adroit Technosys
Author URI: http://adroittechnosys.com/
*/
function mfwp_load_scripts()
{
    wp_enqueue_style('mfwp_styles',plugin_dir_url(__FILE__).'css/style.css');
    //wp_enqueue_style('mfwp_styles',plugin_dir_url(__FILE__).'css/bootstrap/3.3.7/css/bootstrap-theme.min.css');
}
add_action('wp_enqueue_scripts', 'mfwp_load_scripts');
// Global function call
include('functions.php');
add_shortcode('myorder', 'orderView');
/* Fetch Products */
add_action('wp_ajax_fetch_products', 'fetch_products_fn');
add_action('wp_ajax_nopriv_fetch_products', 'fetch_products_fn');
function fetch_products_fn()
{
    $searchTerm = $_POST['keyword'];
    $active     = $_POST['avtive_item'];
    if (strlen($searchTerm) < 2) {
        $response = '<ul id="country-list"></ul>';
        echo substr($response, 0, -1);
    } else {
        global $wpdb;
        /*    $query = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."posts p,".$wpdb->prefix."postmeta pm WHERE p.post_type='product' and p.post_status='publish' and pm.meta_key='_sku' and p.ID=pm.post_id and (p.post_title LIKE '%".$searchTerm."%' or pm.meta_value LIKE '".$searchTerm."%' )");*/
        $query    = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "posts p," . $wpdb->prefix . "postmeta pm WHERE p.post_type='product' and p.post_status='publish' and pm.meta_key='_sku' and p.ID=pm.post_id and  pm.meta_value LIKE '" . $searchTerm . "%'");
        $response = '<ul id="country-list">';
        $t        = '0';
        foreach ($query as $val) {
            $price    = get_post_meta($val->ID, '_regular_price', true);
            $text_val = $val->meta_value . ' - ' . str_replace('"', '', $val->post_title) . ' - ' . get_woocommerce_currency_symbol() . ' ' . $price;
            $response .= '<li onClick="selectProduct(\'' . $val->ID . '\',\'' . $text_val . '\',\'' . $price . '\',\'' . $active . '\');">' . $text_val . '</li>';
            $t = '1';
        }
        if ($t == '0') {
            $query = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "posts p," . $wpdb->prefix . "postmeta pm WHERE p.post_type='product' and p.post_status='publish' and pm.meta_key='_sku' and p.ID=pm.post_id and p.post_title LIKE '%" . $searchTerm . "%' ");
            foreach ($query as $val) {
                $price    = get_post_meta($val->ID, '_regular_price', true);
                $text_val = $val->meta_value . ' - ' . str_replace('"', '', $val->post_title) . ' - ' . get_woocommerce_currency_symbol() . ' ' . $price;
                $response .= '<li onClick="selectProduct(\'' . $val->ID . '\',\'' . $text_val . '\',\'' . $price . '\',\'' . $active . '\');">' . $text_val . '</li>';
                
            }
        }
    }
    $response .= '</ul>';
    echo substr($response, 0, -1);
}
/* Loading Quick Order Form */
add_action('wp_ajax_fetch_old_orders', 'fetch_old_orders_fn');
add_action('wp_ajax_nopriv_fetch_old_orders', 'fetch_old_orders_fn');
function fetch_old_orders_fn()
{
    $dir = '"' . get_site_url() . '/wp-admin/admin-ajax.php' . '"';
    global $woocommerce, $posts;
    $existing        = array();
    $response        = '';
    // Get the current customer info (as an object)
    $customer        = wp_get_current_user();
    $customer_id     = $customer->ID; // customer ID
    $limit           = $_POST['records'];
    // Get all orders for this customer_id
    $customer_orders = get_posts(array(
        'numberposts' => -1,
        'meta_key' => '_customer_user',
        'meta_value' => $customer_id,
        'post_type' => wc_get_order_types(),
        'post_status' => array_keys(wc_get_order_statuses())
    ));
    $start           = 1;
    $existing_record = array();
    $all             = '';
    if ($customer_orders) {
        foreach ($customer_orders as $customer_order) {
            
            //$order        = wc_get_order();
            $order    = new WC_Order($customer_order->ID);
            $order_id = $order->id; // get the order ID )or may be "order->ID")
            // getting all products items for each order
            $items    = $order->get_items();
            
            foreach ($items as $item) {
                if ($start <= $limit || $limit == 'all') {
                    if (!in_array($item['product_id'], $existing_record)) {
                        $product_id  = $item['product_id']; // product id
                        $product_qty = $item['qty']; // product quantity			
                        $price       = get_post_meta($product_id, '_regular_price', true);
                        $text_val    = get_the_title($product_id) . ' - ' . get_woocommerce_currency_symbol() . ' ' . $price;
                        $temp_total  = number_format($price * $product_qty, 2);
                        $total += $temp_total;
                        $response .= '<div class="row">  
			  <div class="col-lg-5">  	  
			  <div class="form-group">  	 
					<input class="form-control search_product" type="text"  id="product' . $start . '" data-id="' . $start . '" name="product' . $start . '" value="' . $text_val . '" >
					 <div class="next_loader text_area_loader" id="animation' . $start . '"  style="display:none"><div class=""></div></div>
					<div id="suggesstion-box-' . $start . '" class="suggesstion-box"></div>
					<input type="hidden" name="product_id_' . $start . '" id="product_id_' . $start . '" value="' . $product_id . '" />
					<input type="hidden" name="product_price_' . $start . '" id="product_price_' . $start . '" value="' . $price . '" />			
				  </div> 
			  </div>
			  <div class="col-lg-4">	  
			  <div class="form-group ">  
				<input class="form-control" type="number" id="quantity' . $start . '" value="0" name="quantity' . $start . '" onchange="change_price(\'' . $start . '\')" min="0">
			  </div>
			  </div>
				 <div class="col-lg-3"> 	  
			 <div class="form-group">
					 <input class="form-control" type="text"  id="price' . $start . '" name="price' . $start . '" value="0.00" readonly>
				  </div> 	
			  </div>
			  </div>';
                        $all .= 'jQuery("body").on("focusout", "div#suggesstion-box-' . $start . '", function() { console.log("A"); });';
                        $start++;
                        $existing_record[] = $product_id;
                    }
                }
            }
        }
        $total = 0;
    }
    if ($limit == 0) {
        $start = 1;
    }
    $extend = $start + 10;
    for ($i = $start; $i <= $extend; $i++) {
        $response .= '<div class="row">  
      <div class="col-lg-5">  	  
      <div class="form-group">  	 
    	    <input class="form-control search_product" type="text"  id="product' . $i . '" data-id="' . $i . '" name="product' . $i . '" >
			 <div class="next_loader text_area_loader" id="animation' . $i . '" style="display:none" ><div class=""></div></div>
			<div id="suggesstion-box-' . $i . '" class="suggesstion-box" ></div>
			<input type="hidden" name="product_id_' . $i . '" id="product_id_' . $i . '" value="" />
			<input type="hidden" name="product_price_' . $i . '" id="product_price_' . $i . '" value="" />			
    	  </div> 
      </div>
      <div class="col-lg-4">	  
      <div class="form-group ">  
        <input class="form-control" type="number" id="quantity' . $i . '" name="quantity' . $i . '" onchange="change_price(\'' . $i . '\')" min="0">
      </div>
      </div>
         <div class="col-lg-3"> 	  
     <div class="form-group">
    	     <input class="form-control" type="text"  id="price' . $i . '" name="price' . $i . '" readonly>
    	  </div> 	
      </div>
      </div>';
        $all .= 'jQuery("body").on("focusout", "div#suggesstion-box-' . $start . '", function() { console.log("A"); });';
        $start++;
    }
    $start  = $start - 1;
    $total  = number_format($total, 2);
    $append = "%" . $start . "%" . $total;
    $t      = 'jQuery(this).val()';
    $response .= '<script>jQuery(document).ready(function(){  
 ' . $all . '
 jQuery("body").on("keyup", "input.search_product", function() {
 var active = jQuery(this).data("id");
 jQuery("#animation"+ active).hide();
jQuery("#suggesstion-box-"+ active).hide();
 jQuery("#suggesstion-box-"+ active).html("");
jQuery("#product"+active).removeClass("remove");
jQuery("#product"+active).css("background","#FFF");
jQuery("#animation"+ active).removeClass("loading_animation");
if(jQuery(this).val()!="" && jQuery(this).val().length>1) { 
jQuery("#animation"+ active).show();
jQuery("#animation"+ active).addClass("loading_animation");
    jQuery.ajax({
    type: "POST",
    url: ' . $dir . ', // This file is not founded that is error.
    data:\'keyword=\'+jQuery(this).val()+\'&action=fetch_products&avtive_item=\'+active,
    success: function(data){ 
	jQuery("#animation"+ active).removeClass("loading_animation");
	jQuery("#animation"+ active).hide();
jQuery("#product"+active).addClass("remove");
jQuery("#suggesstion-box-"+ active).html("");
      jQuery("#suggesstion-box-"+ active).show();
      jQuery("#suggesstion-box-"+active).html(data);	  
      
    }
    
	  }); }  });});</script>';
    $response = $response . $append;
    
    echo substr($response, 0, -1);
}
?>
