<tr>
    <td><input class="css-checkbox" id="chk_visa<?= $offset ?>1" type="checkbox" checked><label class="css-label" for="chk_visa<?= $offset ?>1"> <label></td>
    <td><input maxlength="15" value="1" type="text" name="username" placeholder="Sr. No." class="form-control" disabled /></td>
    <td><input type="text" id="first_name<?= $offset ?>1" name="first_name<?= $offset ?>1" onchange="fname_validate(this.id)" placeholder="*First Name" title="First Name"/></td>
    <td><input type="text" id="middle_name<?= $offset ?>1" onchange="fname_validate(this.id)" name="middle_name<?= $offset ?>1" placeholder="Middle Name" title="Middle Name"/></td>
    <td><input type="text" id="last_name<?= $offset ?>1" name="last_name<?= $offset ?>1" onchange="fname_validate(this.id)" placeholder="Last Name" title="Last Name"/></td>
    <td><input type="text" id="birth_date<?= $offset ?>1" name="birth_date<?= $offset ?>1" placeholder="Birth Date" title="Birth Date" class="app_datepicker" value="<?= date('d-m-Y',  strtotime(' -1 day')) ?>" onchange="adolescence_reflect(this.id)"/></td>
    <td ><input type="text" id="adolescence<?= $offset ?>1" name="adolescence<?= $offset ?>1" placeholder="Adolescence" title="Adolescence" disabled/></td>
    <td><input type="text" id="passport_id<?= $offset ?>1" name="passport_id<?= $offset ?>1" onchange="validate_passport(this.id)" placeholder="*Passport ID" title="Passport ID" style="text-transform: uppercase;" required/></td>
    <td><input type="text" id="issue_date<?= $offset ?>1" name="issue_date<?= $offset ?>1" class="app_datepicker" placeholder="Issue Date" title="Issue Date" onchange="checkPassportDate(this.id);" value="<?= date('d-m-Y')?>" ></td>
    <td><input type="text" id="expiry_date<?= $offset ?>1" name="expiry_date<?= $offset ?>1" class="app_datepicker" value="<?= date('d-m-Y') ?>"  placeholder="Expire Date" title="Expire Date" onchange="validate_issueDate('issue_date<?= $offset ?>1',this.id)"/ ></td>
    <td ><input type="text" id="nationality<?= $offset ?>1" onchange="validate_city(this.id);" name="nationality<?= $offset ?>1" placeholder="*Nationality" title="Nationality"/></td>
</tr>

<script>
    $('#visa_country_name<?= $offset ?>1').select2();
    function checkPassportDate (id) {
      var date1 = document.getElementById(id).value;
      var dates = date1.split("-");
      dates = new Date(dates[2], dates[1] - 1, dates[0]).getTime();
      if (dates == '') {
        error_msg_alert('Please enter the Date..!!');
        return false;
      }
      else if (!date1.match(/^(0[1-9]|[12][0-9]|3[01])[\- \/.](?:(0[1-9]|1[012])[\- \/.](19|20)[0-9]{2})$/)) {
        error_msg_alert('Date format is wrong');
        return false;
      }

        var today = new Date().getTime()
      date = Date.parse(date);
      if (today < dates) {
            error_msg_alert("Date cannot be future date");
            $('#'+id).css({'border':'1px solid red'});  
            document.getElementById(id).value="";
            $('#'+id).focus();
            g_validate_status = false;
            return false;		
      }
    }

    function validate_issueDate (from, to) {
	var from_date = $('#'+from).val(); 
    var to_date = $('#'+to).val(); 

    var parts = from_date.split('-');
    var date = new Date();
    var new_month = parseInt(parts[1])-1;
    date.setFullYear(parts[2]);
    date.setDate(parts[0]);
    date.setMonth(new_month);

    var parts1 = to_date.split('-');
    var date1 = new Date();
    var new_month1 = parseInt(parts1[1])-1;
    date1.setFullYear(parts1[2]);
    date1.setDate(parts1[0]);
    date1.setMonth(new_month1);

    var one_day=1000*60*60*24;

    var from_date_ms = date.getTime();
    var to_date_ms = date1.getTime();

    if(from_date_ms>to_date_ms ){
      error_msg_alert(" Date should be greater than passport issue date");
      $('#'+to).css({'border':'1px solid red'});  
        document.getElementById(to).value="";
        $('#'+to).focus();
        g_validate_status = false;
      return false;
    } 
  }
</script>