<?php 
   $cnt=0;
   
   $response_msg = '';
   function orderView()
   {
   if(isset($_POST['addtocart'])) {
   	$rowcount = $_POST['rowcount']; $add = 1;	$cnt= 1;	
   	for($i=1;$i<=$rowcount;$i++) {				
   		if($_POST['product_id_'.$i]!="") {
   			$cnt++;
   			if($_POST['quantity'.$i]=="" || $_POST['quantity'.$i]<1) {
   				$add++;	
   			}
   		}
   	}
   	if($add==$cnt) {
   		$response_msg= '<div class="woocommerce-error">Product quantity cannnot be empty.</div>';						
   	}else { 
   	for($i=1;$i<=$rowcount;$i++) {				
   		global $woocommerce;
   		if($_POST['product_id_'.$i]!="" && $_POST['quantity'.$i]!="" && $_POST['quantity'.$i]>0) {
   			$woocommerce->cart->add_to_cart($_POST['product_id_'.$i],$_POST['quantity'.$i]);		
   		}
   	}		
   	$response_msg= '<div class="woocommerce-message"><a href="'.get_site_url().'/cart/" class="button wc-forward">View Cart</a> Products has been added to your cart.</div>';			
   	}
   }
   	?>
<?php
   $dir = '"'.$plugins_url = plugins_url().'/order/search.php'.'"';
   $dir = '"'.get_site_url().'/wp-admin/admin-ajax.php'.'"';
   ?>
<style>
</style>
<!-- <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>-->
<script>
   var url = '<?php echo plugins_url().'/order/LoaderIcon.gif'; ?>';
   var css = '#FFF url("'+ '<?php echo plugins_url().'/order/LoaderIcon.gif'; ?>' +'") no-repeat 165px';
   jQuery(document).ready(function(){
      jQuery(".suggesstion-box").focusout(function() { console.log("A");
          if (jQuery(this).has(document.activeElement).length == 0) {
                  jQuery(this).hide();
         }
      });
      var activity= 0;
     jQuery("#old_orders").change(function(){  
       if(activity==1) {
   		jQuery(".next_loader_2").show(); 
   		jQuery(".next_loader_2 div").addClass("loading_animation");
   		jQuery("#newcontainer").html("");
   	}
       jQuery.ajax({
       type: "POST",
       url: <?php echo $dir; ?>, // This file is not founded that is error.
       data:'records='+jQuery(this).val()+'&action=fetch_old_orders',
       success: function(data){	
       jQuery(".next_loader_2 div").removeClass("loading_animation");	
   	      jQuery(".next_loader_2").hide();	
   	  var parse_data = data.split('%');	
         jQuery("#old_records").html(parse_data[0]);	  
         jQuery("#total").val(parse_data[2]);
         jQuery("#rowcount").val(parse_data[1]); activity = 1;	  
       }
       });
     });  
     jQuery("#old_orders").trigger("change");
   
   
   });
   
   var mouse_is_inside = false;
   
   jQuery(document).ready(function()
   {
       jQuery('.suggesstion-box').hover(function(){ 
           mouse_is_inside=true; 
       }, function(){ 
           mouse_is_inside=false; 
       });
   
       jQuery("body").mouseup(function(){ 
           if(! mouse_is_inside) jQuery('.suggesstion-box').hide();
       });
   });
   
   function selectProduct(id,text,price,active) { 
   jQuery("#product"+active).val(text);
   jQuery("#product_id_"+active).val(id);
   jQuery("#product_price_"+active).val(price);
   jQuery("#price"+active).val(price);
   jQuery("#quantity"+active).val(0);
   jQuery("#suggesstion-box-"+active).hide();
    update_total();
   }
   function change_price(id) {
   	var price = jQuery("#product_price_"+id).val();	
   	var total = jQuery("#quantity"+id).val() * price;
   	jQuery("#price"+id).val(total.toFixed(2));			
   	update_total();
   }
   function update_total() {
   	var rowcount = jQuery("#rowcount").val();
   	var total = 0;	
   	for(i=1;i<=rowcount;i++){
   		var price = jQuery("#product_price_"+i).val();	
   		var price2= jQuery("#price"+i).val();	
   		if(price2!="" || price2!='0') {
   			total = parseFloat(total) + parseFloat(jQuery("#quantity"+i).val() * price);
   			total = parseFloat(total);			
   		}
   	}
   	jQuery("#total").val(total.toFixed(2));
   }
</script>
<script type="text/javascript">
   jQuery(document).ready(function(){
   	
     jQuery("#addrowbtn").click(function () {
     	  //	Get value
       var valcnt = jQuery('#rowcount').val();
       valcnt++;
   
   	 jQuery("#newcontainer").append('<div class="row"> <div class="col-lg-5"><div class="form-group"><input class="form-control search_product" type="text"  id="product'+valcnt+'" data-id="'+valcnt+'" name="product'+valcnt+'" ><div class="next_loader text_area_loader" id="animation'+valcnt+'" style="display:none" ><div class=""></div></div><div id="suggesstion-box-'+valcnt+'" class="suggesstion-box"></div><input type="hidden" name="product_id_'+valcnt+'" id="product_id_'+valcnt+'" value="" /><input type="hidden" name="product_price_'+valcnt+'" id="product_price_'+valcnt+'" value="" /></div></div><div class="col-lg-4"><div class="form-group "><input class="form-control" type="number" id="quantity'+valcnt+'" name="quantity'+valcnt+'" onchange="change_price(\''+valcnt+'\')" min="1"></div></div><div class="col-lg-3"><div class="form-group"><input class="form-control" type="text"  id="price'+valcnt+'" name="price'+valcnt+'" readonly></div></div></div>');
    	 	
   
    jQuery('#rowcount').val(valcnt);
   	});
   });
   
    
   
</script>    
<style type="text/css">
</style>
<section class="orderview">
   <div class="">
      <div class="">
         <?php echo $response_msg; ?>
         <div class="row">
            <div class="col-lg-1 no_right_pad custom_width custom_line_height" >	
               Load
            </div>
            <div class="col-lg-1 no_pad">
               <div class="form-group ">
                  <select name="old_orders" id="old_orders" class="form-control" >
                     <option value="0">0</option>
                     <option value="10">10</option>
                     <option value="25">25</option>
                     <option value="50">50</option>
                     <option value="all">All</option>
                  </select>
               </div>
            </div>
            <div class="col-lg-3 custom_line_height">
               Previously Purchased Products
            </div>
            <div class="col-lg-2">
               <div class="next_loader_2">
                  <div class=""></div>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-lg-5">  <label class="csslabel">Product </label>   	</div>
            <div class="col-lg-4">  <label class="csslabel">Quantity </label>	  	</div>
            <div class="col-lg-3">  <label class="csslabel">Price </label>	  	</div>
         </div>
         <form method="post">
            <div id="old_records">
            </div>
            <div id="new_records">
               <?php 
                  $start = 1;
                  $total = 0.00;
                  $limit = 10;
                  ?>
            </div>
            <div id="newcontainer">
            </div>
            <br>
            <div class="row">  
               <input type="hidden" name="rowcount" id="rowcount" value="<?php echo $cnt; ?>">
            </div>
            <div class="row">
               <div class="col-lg-9">
                  <label style="float: right;" class="csslabel">Total Price: </label>
               </div>
               <div class="col-lg-3">
                  <div class="form-group">
                     <input type="text" name="total" id="total" class="form-control" value="<?php echo $total; ?>" readonly />
                  </div>
               </div>
            </div>
            <br>
            <div class="row">
               <div class="col-lg-9"></div>
               <div class="col-lg-3 full_width">
                  <button type="button" class="btn btn-primary cssbtn"  name="addrow" id="addrowbtn">Add Row</button>
                  <button type="submit" class="btn btn-success cssbtn" name="addtocart">Add To Cart</button>
               </div>
            </div>
         </form>
      </div>
   </div>
</section>
<?php 
   }
   ?>
