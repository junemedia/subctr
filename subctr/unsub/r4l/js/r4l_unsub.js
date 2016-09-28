function check_fields() {
  var str = '';
  if (!(document.getElementById('action_stay').checked || document.getElementById('action_unsub').checked)) {
    str += "* Please check the box below to confirm your action.\n";
  }

  if (document.getElementById('action_unsub').checked) {
    console.log('check it');
    document.form1.email.style.backgroundColor="";
    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    if(!document.form1.email.value.match(reg)) {
      str += "* Please enter your valid email address.\n";
      document.form1.email.style.backgroundColor="yellow";
    }
  }

  if (str == '') {
    return true;
  } else {
    alert (str);
    return false;
  }
}

function maxLength(el) {
  if (!('maxLength' in el)) {
    var max = el.attributes.maxLength.value;
    el.onkeypress = function () {
      if (this.value.length >= max) return false;
    };
  }
}
