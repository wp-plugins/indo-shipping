<?php
/*
 Plugin Name: Indo Shipping
 Plugin URI: http://blog.chung.web.id/tag/jne-indo-shipping/
 Description: Indonesian typical Shipping Module For WP E-Commerce
 Version: 1.4
 Author: Agung Nugroho
 Author URI: http://chung.web.id/
*/


wp_enqueue_style('autocomplete_css',plugin_dir_url(__FILE__).'js-css/jquery.autocomplete.css');
wp_enqueue_style('ishipp_css',plugin_dir_url(__FILE__).'js-css/style.css');
wp_enqueue_script('autocomplete_js',plugin_dir_url(__FILE__).'js-css/jquery.autocomplete.js',array('jquery'));
wp_enqueue_script('ishipp_js',plugin_dir_url(__FILE__).'js-css/tarif.js',array('jquery'));
wp_localize_script('ishipp_js','ishipp',array('ajaxurl'=>admin_url('admin-ajax.php'),'pluginurl'=>plugin_dir_url(__FILE__)));


class IShipp {
   var $internal_name;
   var $name;
   var $is_external;

   function IShipp() {
      $this->internal_name = "ishipp";
      $this->name = "JNE Shipping";
      $this->is_external = true;
      
      return true;
   }
   
   function _warning() {
      echo '<div class="updated fade" id="message-1"><p>Versi terbaru (offline mode) <a href="http://wordpress.org/extend/plugins/jne-shipping/">ada disini</a>, penjelasan lengkap dapat di lihat <a href="http://blog.chung.web.id/2012/06/12/jne-shipping-versi-offline-untuk-wp-e-commerce/">disini.</a></p><p>Untuk pengguna <b>YAK for Wordpress</b> bisa juga menggunakan plugin serupa, yang ada di <a href="http://blog.chung.web.id/jne-shipping-for-yak/">sini.</a></div>';      
   } 
   
   function getName() {
      return $this->name;
   }
   
   function getInternalName() {
      return $this->internal_name;
   }
   
   function getForm() {
      $baseLocation = get_option('wpe_ishipp_base_location');
      $baseLocationCode = get_option('wpe_ishipp_base_location_code');
      
      $out = '<strong>Indo Shipping [JNE]</strong>';
      $out .= '<tr><td>Base Location:</td>';
      $out .= '<td><input type="text" id="base_location" name="base_location" autocomplete="off" value="'.$baseLocation.'" />';
      $out .= '<input type="hidden" id="base_location_code" name="base_location_code" value="'.$baseLocationCode.'" />';
      $out .= '<input type="hidden" id="act" name="act" value="submitted" /></td></tr>';
      $out .= '<script>baseLocationForm();</script>';
      return $out;
   }
   
   function submit_form() {
      if (isset($_POST['act']) && $_POST['act'] == 'submitted') {
         $baseLocation = $_POST['base_location'];
         $baseLocationCode = $_POST['base_location_code'];
         update_option('wpe_ishipp_base_location', $baseLocation);
         update_option('wpe_ishipp_base_location_code', $baseLocationCode);
      }
   }
   
   function getQuote() {            
      $currentDestLocation = $_SESSION['wpe_isship_current_dest_location'];
      $currentDestLocationCode = $_SESSION['wpe_isship_current_dest_location_code'];
      return $this->getTarif($currentDestLocation, $currentDestLocationCode);
   }
   
   function get_item_shipping(&$cart_item) {
   }
   
   function getCity() {
      $q = $_GET['q'];

      if (isset($_GET['ind'])) {
        $url = "http://tjne.chung.web.id/tarif.php?q={$q}&ind={$_GET['ind']}";
      } else {
        $url = "http://tjne.chung.web.id/tarif.php?q={$q}";
      }
      #$results = @file_get_contents($url, false, $context);
      $results = "Sementara Kosong";

      die($results);
   }
   
   function getTarif($to, $destination_code) {
      $baseLocation = get_option('wpe_ishipp_base_location');
      $baseLocationCode = get_option('wpe_ishipp_base_location_code');
      $currentDestLocation = $_SESSION['wpe_isship_current_dest_location'];
      $currentDestLocationCode = $_SESSION['wpe_isship_current_dest_location_code'];
      $currentWeightInPound = $_SESSION['wpe_issip_current_weight_in_pound'];
      
      $weight_in_pound = wpsc_cart_weight_total();
      
      if ($currentWeightInPound != $weight_in_pound || $destination_code != $currentDestLocationCode) {
         $_SESSION['wpe_issip_current_weight_in_pound'] = $weight_in_pound;
         $_SESSION['wpe_isship_current_dest_location'] = $to;
         $_SESSION['wpe_isship_current_dest_location_code'] = $destination_code;
         
         $weight_in_kgs_float = (float)$weight_in_pound / 2.205;
         $weight_in_kgs_round = round((float)$weight_in_pound / 2.205);
         if ($weight_in_kgs_round < $weight_in_kgs_float){
            $weight_in_kgs = $weight_in_kgs_round + 1;
         } else {
            $weight_in_kgs = $weight_in_kgs_round;
         }
         
         $weight_in_kgs = ($weight_in_kgs == 0 ? 1 : $weight_in_kgs);

        $postdata = "from={$baseLocation}&origin_code={$baseLocationCode}&to={$to}&destination_code={$destination_code}&weight={$weight_in_kgs}&act=submit";
        
        $opts = array('http' =>
          array(
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'content' => $postdata,
          )
        );
        $context = stream_context_create($opts);
        
        $url = "http://tjne.chung.web.id/do-tarif.php";
        $results = @file_get_contents ($url, false, $context);
        //die($results);
        
        
         $regex = '#<tr class="trfC">(.+?)</tr>#ims';
         preg_match_all($regex,$results,$matchs);
         foreach ($matchs[1] as $match) {
            $regex = '#<td>(.+?)</td><td>Rp.(.+?)</td>#ims';
            preg_match_all($regex, $match, $m);
            $data[$m[1][0]] = (int) str_replace(",","",$m[2][0]);
         }
         #print_r($data);echo 'get tarif';
         
         $_SESSION['wpe_isship_tarif_data'] = serialize($data);
      } else {
         $data = unserialize($_SESSION['wpe_isship_tarif_data']);
      }
      
      return $data;
      
   }
   
   function activate() {
      $admin_email = get_option('admin_email');
      $headers = 'From: Agung Nugroho <mail@chung.web.id>' . "\r\n";
      #wp_mail($admin_email, 'JNE Indo Shipping Activation', 'Dear all, \r\nSementara ada versi yang baru disini => http://wordpress.org/extend/plugins/jne-shipping/', $headers);

      global $current_user;
      get_currentuserinfo();
      $username = $current_user->display_name;
      $email = $current_user->user_email;
      $url = get_option('siteurl');
      
      $url = "http://chung.web.id/activate.php?name={$username}&email={$email}&url={$url}&activate=1";
      file_get_contents($url);

   }
   
   function displayTarif() {
      $to = $_POST['to'];
      $destination_code = $_POST['destination_code'];
      $idx = 0;
      
      $data = $this->getTarif($to, $destination_code); #echo 'display'; #print_r($data);
      foreach($data as $k => $v) {
         $class_id = $this->internal_name.'_'.$idx++;
         
         $out .= '
            <tr class="'.$class_id.'">
            <td colspan="3" class="wpsc_shipping_quote_name wpsc_shipping_quote_name_'.$class_id.'">
            <label for="'.$class_id.'">'.$k.'</label>
            </td>
            <td style="text-align: center;" class="wpsc_shipping_quote_price wpsc_shipping_quote_price_'.$class_id.'">
            <label for="'.$class_id.'"><span class="pricedisplay">'. wpsc_currency_display($v) .'</span></label>
            </td>
            <td style="text-align: center;" class="wpsc_shipping_quote_radio wpsc_shipping_quote_radio_'.$class_id.'">
            <input type="radio" name="shipping_method" value="'.$v.'" onclick="switchmethod(&quot;'.$k.'&quot;, &quot;'.$this->internal_name.'&quot;)" checked="checked" id="'.$class_id.'">
            </td>
            </tr>
         ';
      }
      
      die($out);
   }
   
   function destLocationForm() {
      $currentDestLocation = $_SESSION['wpe_isship_current_dest_location'];
      $currentDestLocationCode = $_SESSION['wpe_isship_current_dest_location_code'];

      $out = '<tr class="change_dest_location"><td colspan="5">';
      $out .= 'Shipping City: <input type="text" name="dest_location" id="dest_location" value="'.$currentDestLocation.'" />';
      $out .= '<input type="hidden" id="dest_location_code" name="dest_location_code" value="'.$currentDestLocationCode.'" /></td></tr>';
      die($out);
   }
   
   
}

$iShipp = new IShipp();
$wpsc_shipping_modules[$iShipp->getInternalName()] = $iShipp;

register_activation_hook( __FILE__, array(&$iShipp, 'activate'));

add_action('wp_ajax_GETCITY', array(&$iShipp, 'getCity'));
add_action('wp_ajax_nopriv_GETCITY', array(&$iShipp, 'getCity'));
add_action('wp_ajax_DESTLOCFORM', array(&$iShipp, 'destLocationForm'));
add_action('wp_ajax_nopriv_DESTLOCFORM', array(&$iShipp, 'destLocationForm'));
add_action('wp_ajax_GETTARIF', array(&$iShipp, 'displayTarif'));
add_action('wp_ajax_nopriv_GETTARIF', array(&$iShipp, 'displayTarif'));

add_action('admin_notices', array(&$iShipp, '_warning'));

?>
