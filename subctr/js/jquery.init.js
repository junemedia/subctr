var jQueryScriptOutputted = false;
  function initJQuery() {
    if (typeof(jQuery) == 'undefined' || jQuery.fn.jquery != "1.3.2") {
      if (!jQueryScriptOutputted) {
          jQueryScriptOutputted = true;
          document.write("<scr" + "ipt type=\"text/javascript\" src=\"/subctr/js/jquery.min.js\"></scr" + "ipt>");
      }
      setTimeout("initJQuery()", 100);
    }
  }
  initJQuery();
