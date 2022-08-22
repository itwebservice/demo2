<?php 
$flag = true;
class ticket_payment_master{

public function ticket_payment_master_save()
{
	$train_ticket_id = $_POST['train_ticket_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];	
	$bank_id = $_POST['bank_id'];
	$branch_admin_id = $_POST['branch_admin_id'];
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];

	$payment_date = date('Y-m-d', strtotime($payment_date));

	if($payment_mode=="Cheque"||$payment_mode=="Credit Card"){ 
		$clearance_status = "Pending"; } 
	else {  $clearance_status = ""; }	

	$financial_year_id = $_SESSION['financial_year_id'];

	begin_t();

	$sq_max = mysqli_fetch_assoc(mysqlQuery("select max(payment_id) as max from train_ticket_payment_master"));
	$payment_id = $sq_max['max'] + 1;

	$sq_payment = mysqlQuery("insert into train_ticket_payment_master (payment_id, train_ticket_id, branch_admin_id, financial_year_id, payment_date, payment_amount, payment_mode, bank_name, transaction_id, bank_id, clearance_status,credit_charges,credit_card_details) values ('$payment_id', '$train_ticket_id', '$branch_admin_id', '$financial_year_id', '$payment_date', '$payment_amount', '$payment_mode', '$bank_name', '$transaction_id', '$bank_id', '$clearance_status','$credit_charges','$credit_card_details') ");
	if(!$sq_payment){
		rollback_t();
		echo "error--Sorry, Payment not saved!";
		exit;
	}
	else{

		//Finance save
    	$this->finance_save($payment_id, $branch_admin_id);

    	//Bank and Cash Book Save
		$this->bank_cash_book_save($payment_id, $branch_admin_id);

		if($GLOBALS['flag']){
			commit_t();
			//Payment email notification
	    	$this->payment_email_notification_send($train_ticket_id, $payment_amount, $payment_mode, $payment_date);

	    	//Payment sms notification
			if($payment_amount != 0){
				$this->payment_sms_notification_send($train_ticket_id, $payment_amount, $payment_mode);
			}

			echo "Train Ticket Payment has been successfully saved.";
			exit;	
		}
		else{
			rollback_t();
			exit;
		}
		
	}
	
}

public function finance_save($payment_id, $branch_admin_id){

	$row_spec = 'sales';
	$train_ticket_id = $_POST['train_ticket_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id1 = $_POST['transaction_id'];
	$bank_id1 = $_POST['bank_id'];	
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];
	$payment_amount1 = floatval($payment_amount1) + floatval($credit_charges);

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$year2 = explode("-", $payment_date);
	$yr2 =$year2[0];

	$sq_train_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$train_ticket_id'"));
	$customer_id = $sq_train_info['customer_id'];
	$created_at = $sq_train_info['created_at'];  
	$booking_date = date('Y-m-d', strtotime($created_at));
	$year1 = explode("-", $booking_date);
	$yr1 = $year1[0];

	global $transaction_master;
	//Getting cash/Bank Ledger
	if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
	else{
		$sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id1' and user_type='bank'"));
		$pay_gl = $sq_bank['ledger_id'];
		$type='BANK RECEIPT';
	}

    //Getting customer Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
	$cust_gl = $sq_cust['ledger_id'];

	//////////Payment Amount///////////
	if($payment_mode != 'Credit Note'){
		if($payment_mode == 'Credit Card'){

		//////Customer Credit charges///////
		$module_name = "Train Ticket Booking";
		$module_entry_id = $train_ticket_id;
		$transaction_id = $transaction_id1;
		$payment_amount = $credit_charges;
		$payment_date = $payment_date;
		$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $credit_charges, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id1,$transaction_id1);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = $cust_gl;
		$payment_side = "Debit";
		$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

		//////Credit charges ledger///////
		$module_name = "Train Ticket Booking";
		$module_entry_id = $train_ticket_id;
		$transaction_id = $transaction_id1;
		$payment_amount = $credit_charges;
		$payment_date = $payment_date;
		$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $credit_charges, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id1,$transaction_id1);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = 224;
		$payment_side = "Credit";
		$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

		//////Get Credit card company Ledger///////
		$credit_card_details = explode('-',$credit_card_details);
		$entry_id = $credit_card_details[0];
		$sq_cust1 = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$entry_id' and user_type='credit company'"));
		$company_gl = $sq_cust1['ledger_id'];
		//////Get Credit card company Charges///////
		$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select * from credit_card_company where entry_id='$entry_id'"));
		//////company's credit card charges
		$company_card_charges = ($sq_credit_charges['charges_in'] =='Flat') ? $sq_credit_charges['credit_card_charges'] : ($payment_amount1 * ($sq_credit_charges['credit_card_charges']/100));
		//////company's tax on credit card charges
		$tax_charges = ($sq_credit_charges['tax_charges_in'] =='Flat') ? $sq_credit_charges['tax_on_credit_card_charges'] : ($company_card_charges * ($sq_credit_charges['tax_on_credit_card_charges']/100));
		$finance_charges = $company_card_charges + $tax_charges;
		$finance_charges = number_format($finance_charges,2);
		$credit_company_amount = $payment_amount1 - $finance_charges;

		//////Finance charges ledger///////
		$module_name = "Train Ticket Booking";
		$module_entry_id = $train_ticket_id;
		$transaction_id = $transaction_id1;
		$payment_amount = $finance_charges;
		$payment_date = $payment_date;
		$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $finance_charges, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id1,$transaction_id1);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = 231;
		$payment_side = "Debit";
		$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		//////Credit company amount///////
		$module_name = "Train Ticket Booking";
		$module_entry_id = $train_ticket_id;
		$transaction_id = $transaction_id1;
		$payment_amount = $credit_company_amount;
		$payment_date = $payment_date;
		$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $credit_company_amount, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id1,$transaction_id1);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = $company_gl;
		$payment_side = "Debit";
		$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
	else{
	
		$module_name = "Train Ticket Booking";
		$module_entry_id = $train_ticket_id;
		$transaction_id = $transaction_id1;
		$payment_amount = $payment_amount1;
		$payment_date = $payment_date;
		$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $payment_amount1, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id1,$transaction_id1);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = $pay_gl;
		$payment_side = "Debit";
		$clearance_status = ($payment_mode=="Cheque" || $payment_mode == 'Credit Card') ? "Pending" : "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}

	$module_name = "Train Ticket Booking";
	$module_entry_id = $train_ticket_id;
	$transaction_id = $transaction_id1;
	$payment_amount = $payment_amount1;
	$payment_date = $payment_date;
	$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $payment_amount1, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id1,$transaction_id1);
	$ledger_particular = get_ledger_particular('By','Cash/Bank');
	$gl_id = $cust_gl;
	$payment_side = "Credit";
	$clearance_status = "";
	$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
	}
}

public function bank_cash_book_save($payment_id, $branch_admin_id)
{
	global $bank_cash_book_master;

	$train_ticket_id = $_POST['train_ticket_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];
	$payment_date = date('Y-m-d', strtotime($payment_date));
	$year2 = explode("-", $payment_date);
	$yr2 =$year2[0];

	$sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select customer_id,created_at from train_ticket_master where train_ticket_id='$train_ticket_id'"));
	$booking_date = $sq_ticket_info['created_at'];
	$booking_date = date('Y-m-d', strtotime($booking_date));
	$year1 = explode("-", $booking_date);
	$yr1 = $year1[0];
	
	if($payment_mode == 'Credit Card'){
	
		$payment_amount = $payment_amount + $credit_charges;
		$credit_card_details = explode('-',$credit_card_details);
		$entry_id = $credit_card_details[0];
		$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select bank_id from credit_card_company where entry_id ='$entry_id'"));
		$bank_id = $sq_credit_charges['bank_id'];
	}
	$module_name = "Train Ticket Booking";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $payment_amount, $sq_ticket_info['customer_id'], $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id,$transaction_id);
	$clearance_status = ($payment_mode=="Cheque" || $payment_mode=="Credit Card") ? "Pending" : "";
	$payment_side = "Debit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_save($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type, $branch_admin_id);
	
}


public function ticket_payment_master_update()
{
	$payment_id = $_POST['payment_id'];
	$train_ticket_id = $_POST['train_ticket_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];	
	$bank_id = $_POST['bank_id'];
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];

	$payment_date = date('Y-m-d', strtotime($payment_date));

	$financial_year_id = $_SESSION['financial_year_id'];

	$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_payment_master where payment_id='$payment_id'"));

	$clearance_status = ($sq_payment_info['payment_mode']=='Cash' && $payment_mode!="Cash") ? "Pending" : $sq_payment_info['clearance_status'];
	if($payment_mode=="Cash"){ $clearance_status = ""; }

	begin_t();

	$sq_payment = mysqlQuery("update train_ticket_payment_master set train_ticket_id='$train_ticket_id', financial_year_id='$financial_year_id', payment_date='$payment_date', payment_amount='$payment_amount', payment_mode='$payment_mode', bank_name='$bank_name', transaction_id='$transaction_id', bank_id='$bank_id', clearance_status='$clearance_status',credit_charges='$credit_charges' where payment_id='$payment_id' ");
	if(!$sq_payment){

		rollback_t();
		echo "error--Sorry, Payment not update!";
		exit;
	}
	else{

		//Finance update
		$this->finance_update($sq_payment_info, $clearance_status);

		//Bank and Cash Book Save
		$this->bank_cash_book_update($clearance_status);

		if($GLOBALS['flag']){
			commit_t();
			//Payment email notification
			$this->payment_update_email_notification_send($payment_id);
			
			echo "Train Ticket Payment has been successfully updated.";
			exit;	
		}
		else{
			rollback_t();
			exit;
		}
		
	}
	
}

public function finance_update($sq_payment_info, $clearance_status1)
{
	$row_spec = 'sales';
	$payment_id = $_POST['payment_id'];
	$train_ticket_id = $_POST['train_ticket_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount1 = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$bank_id = $_POST['bank_id'];
	$transaction_id1 = $_POST['transaction_id'];
	$payment_old_value = $_POST['payment_old_value'];
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];
	$credit_charges_old = $_POST['credit_charges_old'];
	$branch_admin_id = $_SESSION['branch_admin_id'];

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$year2 = explode("-", $payment_date);
	$yr2 =$year2[0];

	$sq_train_info = mysqli_fetch_assoc(mysqlQuery("select customer_id,created_at from train_ticket_master where train_ticket_id='$train_ticket_id'"));
	$booking_date = $sq_train_info['created_at'];
	$year1 = explode("-", $booking_date);
	$yr1 =$year1[0];
	$customer_id = $sq_train_info['customer_id'];  
	global $transaction_master;

    //Getting cash/Bank Ledger
    if($payment_mode == 'Cash') {  $pay_gl = 20; $type='CASH RECEIPT'; }
    else{ 
	    $sq_bank = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$bank_id' and user_type='bank'"));
	    $pay_gl = $sq_bank['ledger_id'];
		$type='BANK RECEIPT';
     } 

     //Getting customer Ledger
	$sq_cust = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$customer_id' and user_type='customer'"));
	$cust_gl = $sq_cust['ledger_id'];

	
	if($payment_amount1 != $payment_old_value){
		if($payment_mode != 'Credit Note'){
		//////////Payment Amount///////////
			if($payment_mode == 'Credit Card'){

			$payment_old_value = $payment_old_value + $credit_charges_old;
			//////Customer Credit charges///////
			$module_name = "Train Ticket Booking";
			$module_entry_id = $train_ticket_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_charges_old;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $credit_charges_old, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $cust_gl;
			$payment_side = "Credit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

			//////Credit charges ledger///////
			$module_name = "Train Ticket Booking";
			$module_entry_id = $train_ticket_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_charges_old;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $credit_charges_old, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = 224;
			$payment_side = "Debit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);

			//////Get Credit card company Ledger///////
			$credit_card_details = explode('-',$credit_card_details);
			$entry_id = $credit_card_details[0];
			$sq_cust1 = mysqli_fetch_assoc(mysqlQuery("select * from ledger_master where customer_id='$entry_id' and user_type='credit company'"));
			$company_gl = $sq_cust1['ledger_id'];
			//////Get Credit card company Charges///////
			$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select * from credit_card_company where entry_id='$entry_id'"));
			//////company's credit card charges
			$company_card_charges = ($sq_credit_charges['charges_in'] =='Flat') ? $sq_credit_charges['credit_card_charges'] : ($payment_old_value * ($sq_credit_charges['credit_card_charges']/100));
			//////company's tax on credit card charges
			$tax_charges = ($sq_credit_charges['tax_charges_in'] =='Flat') ? $sq_credit_charges['tax_on_credit_card_charges'] : ($company_card_charges * ($sq_credit_charges['tax_on_credit_card_charges']/100));
			$finance_charges = $company_card_charges + $tax_charges;
$finance_charges = number_format($finance_charges,2);
			$credit_company_amount = $payment_old_value - $finance_charges;

			//////Finance charges ledger///////
			$module_name = "Train Ticket Booking";
			$module_entry_id = $train_ticket_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $finance_charges;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $finance_charges, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = 231;
			$payment_side = "Credit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
			//////Credit company amount///////
			$module_name = "Train Ticket Booking";
			$module_entry_id = $train_ticket_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $credit_company_amount;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $credit_company_amount, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $company_gl;
			$payment_side = "Credit";
			$clearance_status = ($payment_mode=="Cheque"||$payment_mode=="Credit Card") ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id,'', $payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}
		else{
		
			$module_name = "Train Ticket Booking";
			$module_entry_id = $train_ticket_id;
			$transaction_id = $transaction_id1;
			$payment_amount = $payment_old_value;
			$payment_date = $payment_date;
			$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $payment_old_value, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id,$transaction_id1);
			$ledger_particular = get_ledger_particular('By','Cash/Bank');
			$gl_id = $pay_gl;
			$payment_side = "Credit";
			$clearance_status = ($payment_mode=="Cheque" || $payment_mode == 'Credit Card') ? "Pending" : "";
			$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}

		$module_name = "Train Ticket Booking";
		$module_entry_id = $train_ticket_id;
		$transaction_id = $transaction_id1;
		$payment_amount = $payment_old_value;
		$payment_date = $payment_date;
		$payment_particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $payment_old_value, $customer_id, $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id,$transaction_id1);
		$ledger_particular = get_ledger_particular('By','Cash/Bank');
		$gl_id = $cust_gl;
		$payment_side = "Debit";
		$clearance_status = "";
		$transaction_master->transaction_save($module_name, $module_entry_id, $transaction_id, $payment_amount, $payment_date, $payment_particular, $gl_id, '',$payment_side, $clearance_status, $row_spec,$branch_admin_id,$ledger_particular,$type);
		}
	}
}

public function bank_cash_book_update($clearance_status)
{
	global $bank_cash_book_master;

	$payment_id = $_POST['payment_id'];
	$train_ticket_id = $_POST['train_ticket_id'];
	$payment_date = $_POST['payment_date'];
	$payment_amount = $_POST['payment_amount'];
	$payment_mode = $_POST['payment_mode'];
	$bank_name = $_POST['bank_name'];
	$transaction_id = $_POST['transaction_id'];
	$bank_id = $_POST['bank_id'];
	$credit_charges = $_POST['credit_charges'];
	$credit_card_details = $_POST['credit_card_details'];

	if($payment_mode == 'Credit Card'){

		$payment_amount = $payment_amount + $credit_charges;
		$credit_card_details = explode('-',$credit_card_details);
		$entry_id = $credit_card_details[0];
		$sq_credit_charges = mysqli_fetch_assoc(mysqlQuery("select bank_id from credit_card_company where entry_id ='$entry_id'"));
		$bank_id = $sq_credit_charges['bank_id'];
	}

	$payment_date = date('Y-m-d', strtotime($payment_date));
	$year2 = explode("-", $payment_date);
	$yr2 =$year2[0];

	$sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select created_at,customer_id from train_ticket_master where train_ticket_id='$train_ticket_id'"));
	$booking_date = $sq_ticket_info['created_at'];
	$booking_date = get_date_db($booking_date);
	$year1 = explode("-", $booking_date);
	$yr1 =$year1[0];
	
	$module_name = "Train Ticket Booking";
	$module_entry_id = $payment_id;
	$payment_date = $payment_date;
	$payment_amount = $payment_amount;
	$payment_mode = $payment_mode;
	$bank_name = $bank_name;
	$transaction_id = $transaction_id;
	$bank_id = $bank_id;
	$particular = get_sales_paid_particular(get_train_ticket_booking_payment_id($payment_id,$yr2), $payment_date, $payment_amount, $sq_ticket_info['customer_id'], $payment_mode, get_train_ticket_booking_id($train_ticket_id,$yr1),$bank_id,$transaction_id);
	$clearance_status = $clearance_status;
	$payment_side = "Debit";
	$payment_type = ($payment_mode=="Cash") ? "Cash" : "Bank";

	$bank_cash_book_master->bank_cash_book_master_update($module_name, $module_entry_id, $payment_date, $payment_amount, $payment_mode, $bank_name, $transaction_id, $bank_id, $particular, $clearance_status, $payment_side, $payment_type);
}



//////////////////////////////////**Payment email notification send start**/////////////////////////////////////
public function payment_email_notification_send($train_ticket_id, $payment_amount, $payment_mode, $payment_date)
{
   global $encrypt_decrypt,$secret_key;
   $sq_train_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$train_ticket_id'"));
  
   $date = $sq_train_ticket_info['created_at'];
	$yr = explode("-", $date);
	$year =$yr[0];
   $sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_train_ticket_info[customer_id]'"));
   $email_id = $encrypt_decrypt->fnDecrypt($sq_customer_info['email_id'], $secret_key);
   $customer_name = ($sq_customer_info['type'] == 'Corporate' || $sq_customer_info['type'] == 'B2B') ? $sq_customer_info['company_name'] : $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];

   $due_date = ($sq_train_ticket_info['payment_due_date'] == '1970-01-01') ? '' : $sq_train_ticket_info['payment_due_date'];  
   $sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum , sum(credit_charges) as sumc from train_ticket_payment_master where train_ticket_id='$train_ticket_id' and clearance_status!='Cancelled'"));
  
   $credit_card_amount = $sq_total_amount['sumc'];
	$total_pay_amt = $sq_total_amount['sum']+$credit_card_amount;
	$total_amount = $sq_train_ticket_info['net_total']+$credit_card_amount;

   $subject = 'Payment Acknowledgement (Booking ID : '.get_train_ticket_booking_id($train_ticket_id,$year).' )';
   global $model;
   $model->generic_payment_mail('49',$payment_amount, $payment_mode, $total_amount, $total_pay_amt, $payment_date, $due_date,$email_id,$subject, $customer_name);
}
/////////////////**Payment email notification send end**///////////////////////

////////////////////////**Payment update email notification send start/////////
public function payment_update_email_notification_send($payment_id){
	global $encrypt_decrypt,$secret_key;
	$sq_payment_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_payment_master where payment_id='$payment_id' and clearance_status!='Cancelled'"));
	$train_ticket_id = $sq_payment_info['train_ticket_id'];
	$payment_amount = $sq_payment_info['payment_amount'];
   	$payment_mode = $sq_payment_info['payment_mode'];
   	$payment_date = $sq_payment_info['payment_date'];
	$update_payment = true;

	$sq_train_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$train_ticket_id'"));
	$total_amount = $sq_train_ticket_info['net_total'];
	$date = $sq_train_ticket_info['created_at'];
	$yr = explode("-", $date);
	$year =$yr[0];
	$sq_total_amount = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum, sum(credit_charges) as sumc from train_ticket_payment_master where train_ticket_id='$train_ticket_id' and clearance_status!='Cancelled'"));
	$credit_card_amount = $sq_total_amount['sumc'];
	$total_pay_amt = $sq_total_amount['sum']+$credit_card_amount;
	$total_amount = $sq_train_ticket_info['net_total']+$credit_card_amount;

	$due_date = ($sq_train_ticket_info['payment_due_date'] == '1970-01-01') ? '' : $sq_train_ticket_info['payment_due_date'];  
	$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$sq_train_ticket_info[customer_id]'"));
	$email_id = $encrypt_decrypt->fnDecrypt($sq_customer_info['email_id'], $secret_key);
	$customer_name = ($sq_customer_info['type'] == 'Corporate' || $sq_customer_info['type'] == 'B2B') ? $sq_customer_info['company_name'] : $sq_customer_info['first_name'].' '.$sq_customer_info['last_name'];

	$payment_id = get_train_ticket_booking_payment_id($payment_id,$year);
	$subject = 'Train Ticket Booking Payment Correction (Booking ID : '.get_train_ticket_booking_id($train_ticket_id,$year).' )';
	global $model;
	   $model->generic_payment_mail('59',$payment_amount, $payment_mode, $total_amount, $total_pay_amt, $payment_date, $due_date,$email_id,$subject, $customer_name);
}
////////////////////////////**Payment update email notification send end //////////////////

////////////////////////////////**Payment sms notification send start**////////////////////
public function payment_sms_notification_send($train_ticket_id, $payment_amount, $payment_mode)
{
	global $encrypt_decrypt,$secret_key,$currency;
	$sq_train_ticket_info = mysqli_fetch_assoc(mysqlQuery("select customer_id from train_ticket_master where train_ticket_id='$train_ticket_id'"));
	$customer_id = $sq_train_ticket_info['customer_id'];

	$sq_customer_info = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id='$customer_id'"));
	$mobile_no = $encrypt_decrypt->fnDecrypt($sq_customer_info['contact_no'], $secret_key);
	$sq_currency = mysqli_fetch_assoc(mysqlQuery("select * from currency_name_master where id='$currency'"));
	$currency_code = $sq_currency['currency_code'];
	
	$message = "Dear ".$sq_customer_info['first_name']." ".$sq_customer_info['last_name'].", Acknowledge your payment of ".$payment_amount." ".$currency_code.", which we received for train ticket  installment.";
    global $model;
    $model->send_message($mobile_no, $message);
}
//////////////////////////////////**Payment sms notification send end**/////////////////////////////////////
public function whatsapp_send(){
	global $app_contact_no,$session_emp_id,$currency_logo,$encrypt_decrypt,$secret_key;

	$booking_id = $_POST['booking_id'];
	$payment_amount = $_POST['payment_amount'];
	$sq_ticket_info = mysqli_fetch_assoc(mysqlQuery("select * from train_ticket_master where train_ticket_id='$booking_id'"));

	
	$sq_pay = mysqli_fetch_assoc(mysqlQuery("select sum(payment_amount) as sum, sum(credit_charges) as sumc from train_ticket_payment_master where clearance_status!='Cancelled' and train_ticket_id='$booking_id'"));
	$credit_card_amount = $sq_pay['sumc'];
	$total_pay_amt = $sq_pay['sum']+$credit_card_amount;
	$total_amount = $sq_ticket_info['net_total']+$credit_card_amount;
	$outstanding =  $total_amount - ($total_pay_amt);

	$sq_emp_info = mysqli_fetch_assoc(mysqlQuery("select * from emp_master where emp_id= '$session_emp_id'"));
	if($session_emp_id == 0){
		$contact = $app_contact_no;
	}
	else{
		$contact = $sq_emp_info['mobile_no'];
	}
	$sq_customer = mysqli_fetch_assoc(mysqlQuery("select * from customer_master where customer_id=".$sq_ticket_info['customer_id']));
	$contact_no = $encrypt_decrypt->fnDecrypt($sq_customer['contact_no'], $secret_key);

$whatsapp_msg = rawurlencode('Hello Dear '.$sq_customer['first_name'].',
Hope you are doing great. This is to inform you that we have received your payment. We look forward to provide you a great experience.
*Total Amount* : '.$currency_logo.' '.number_format($total_amount,2).'
*Paid Amount* : '.$currency_logo.' '.number_format($total_pay_amt,2).'
*Balance Amount* : '.$currency_logo.' '.number_format($outstanding,2).'
  
Please do not hesitate to call us on '.$contact.' if you have any concern. 
Thank you. ');
   $link = 'https://web.whatsapp.com/send?phone='.$contact_no.'&text='.$whatsapp_msg;
   echo $link;
  }
}
?>