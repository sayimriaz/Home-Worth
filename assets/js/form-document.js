(function () {
  var printBtn = document.getElementById("print-form");
  if (printBtn) {
    printBtn.addEventListener("click", function () {
      window.print();
    });
  }
  var closeBtn = document.getElementById("close-form");
  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      if (window.parent && window.parent !== window) {
        window.parent.postMessage("hw-form-close", window.location.origin);
        return;
      }
      window.close();
    });
  }
  if (document.body && document.body.getAttribute("data-autoprint") === "1") {
    window.addEventListener("load", function () {
      window.print();
    });
  }
})();
